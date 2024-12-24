CREATE TABLE urls (
    id SERIAL PRIMARY KEY,
    code VARCHAR(6) UNIQUE NOT NULL,
    url TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    expires_at TIMESTAMP DEFAULT NOW() + INTERVAL '31 days',
    click_count INT DEFAULT 0
);