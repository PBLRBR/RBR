-- Jalankan melalui Supabase SQL Editor hanya bila tabel password_resets belum ada.
CREATE TABLE IF NOT EXISTS password_resets (
    id_reset BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user INTEGER NOT NULL REFERENCES users(id_user) ON DELETE CASCADE,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMPTZ NOT NULL,
    used_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_password_resets_user ON password_resets(id_user);
