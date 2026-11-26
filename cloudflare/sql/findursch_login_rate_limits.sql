-- FindURSCH login rate limiting, replacing the former temporary Cloudflare D1 counter.
-- The function accepts only a keyed HMAC of the client IP; the raw IP is never stored.

CREATE TABLE IF NOT EXISTS public.findursch_login_rate_limits (
  ip_hash text PRIMARY KEY,
  window_started_at timestamptz NOT NULL,
  attempts integer NOT NULL CHECK (attempts > 0),
  updated_at timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT findursch_login_rate_limits_ip_hash_format
    CHECK (ip_hash ~ '^[0-9a-f]{64}$')
);

CREATE INDEX IF NOT EXISTS findursch_login_rate_limits_updated_at_idx
  ON public.findursch_login_rate_limits (updated_at);

ALTER TABLE public.findursch_login_rate_limits ENABLE ROW LEVEL SECURITY;

REVOKE ALL ON TABLE public.findursch_login_rate_limits
  FROM PUBLIC, anon, authenticated, service_role;

CREATE OR REPLACE FUNCTION public.findursch_check_login_rate_limit(p_ip_hash text)
RETURNS TABLE (allowed boolean, retry_after_seconds integer)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $function$
DECLARE
  v_now timestamptz := clock_timestamp();
  v_attempts integer;
  v_window_started_at timestamptz;
BEGIN
  IF p_ip_hash IS NULL OR p_ip_hash !~ '^[0-9a-f]{64}$' THEN
    RAISE EXCEPTION 'Invalid client address hash' USING ERRCODE = '22023';
  END IF;

  INSERT INTO public.findursch_login_rate_limits AS existing
    (ip_hash, window_started_at, attempts, updated_at)
  VALUES
    (p_ip_hash, v_now, 1, v_now)
  ON CONFLICT (ip_hash) DO UPDATE
  SET
    window_started_at = CASE
      WHEN existing.window_started_at <= v_now - interval '60 seconds'
        THEN EXCLUDED.window_started_at
      ELSE existing.window_started_at
    END,
    attempts = CASE
      WHEN existing.window_started_at <= v_now - interval '60 seconds'
        THEN 1
      ELSE existing.attempts + 1
    END,
    updated_at = v_now
  RETURNING attempts, window_started_at
  INTO v_attempts, v_window_started_at;

  DELETE FROM public.findursch_login_rate_limits
  WHERE updated_at < v_now - interval '1 day';

  RETURN QUERY
  SELECT
    v_attempts <= 10,
    CASE
      WHEN v_attempts <= 10 THEN 0
      ELSE GREATEST(
        1,
        CEIL(EXTRACT(EPOCH FROM (v_window_started_at + interval '60 seconds' - v_now)))::integer
      )
    END;
END;
$function$;

REVOKE ALL ON FUNCTION public.findursch_check_login_rate_limit(text)
  FROM PUBLIC, anon, authenticated;
GRANT EXECUTE ON FUNCTION public.findursch_check_login_rate_limit(text)
  TO service_role;
