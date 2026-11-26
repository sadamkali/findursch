export interface AuthSessionKV {
  get(key: string): Promise<string | null>;
  put(key: string, value: string, options?: { expirationTtl?: number }): Promise<void>;
  delete(key: string): Promise<void>;
}

export interface AuthEnv {
  SUPABASE_URL?: string;
  SUPABASE_ANON_KEY?: string;
  SUPABASE_SECRET_KEY?: string;
  AUTH_SESSIONS?: AuthSessionKV;
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

export interface AuthIdentity {
  user: LegacyAppUser;
  roles: string[];
  permissions: string[];
  isAdmin: boolean;
  isSchoolOwner: boolean;
  isParent: boolean;
  redirectTo: "/admin" | "/owner" | "/parent";
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
export const SESSION_KEY_PREFIX = "findursch:session:";
export const DEFAULT_SUPABASE_URL = "https://gpsfttpeikatxszswljo.supabase.co";
