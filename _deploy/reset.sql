-- PostgreSQL Database & Role Initialization for E-Pay
DO $$
BEGIN
  IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'epay') THEN
    CREATE ROLE epay WITH LOGIN PASSWORD 'ChangeMe_StrongPassword!';
  END IF;
END
$$;

-- Run in psql as superuser:
-- CREATE DATABASE epay OWNER epay ENCODING 'UTF8';
-- GRANT ALL PRIVILEGES ON DATABASE epay TO epay;
