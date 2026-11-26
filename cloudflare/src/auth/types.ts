export interface D1PreparedStatement {
  bind(...values: unknown[]): D1PreparedStatement;
  first<T = Record<string, unknown>>(columnName?: string): Promise<T | null>;
  run<T = Record<string, unknown>>(): Promise<{
    success?: boolean;
    error?: string;
    results?: T[];
    meta?: { changes?: number; rows_read?: number; rows_written?: number };
  }>;
}

export interface AuthSessionDatabase {
  prepare(query: string): D1PreparedStatement;
}

export interface AuthEnv {
  SUPABASE_URL?: string;
  SUPABASE_ANON_KEY?: string;
  SUPABASE_SECRET_KEY?: string;
  AUTH_DB?: AuthSessionDatabase;
}

export interface SupabaseTokenResponse {
  access_token: string;
  refresh_token: string;
  expires_in: number;
  token_type?: string;
  user?: {
    id?: string;
    email?: string | null;
    app_metadata?: Record<string, unknown>;
  };
}

export interface LegacyAppUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  status: string;
  user_type: string;
  email_verified_at: string | null;
}

export interface StoredAuthSession {
  supabaseUserId: string;
  legacyUserId: number;
  accessToken: string;
  refreshToken: string;
  accessTokenExpiresAt: number;
  absoluteExpiresAt: number;
  createdAt: number;
  remember: boolean;
  workspace: "admin" | "owner" | "parent";
}

export const SESSION_COOKIE = "findursch_session";
