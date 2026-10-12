# Security Review Summary

Date: 2026-10-03

## Findings

| # | Severity | File | Lines | Vulnerability | Confidence |
|---|----------|------|-------|---------------|------------|
| 1 | 🔴 CRITICAL | [controller/AuthController.php](controller/AuthController.php) | 56-98 | Public registration accepts hardcoded secrets (`POSPERU-ROOT-99`, `POSPERU-DEV-2026`) and assigns `admin`/`developer` roles without any admin authorization. This is a direct privilege-escalation path for anyone who knows the internal codes. | 9/10 |
| 2 | 🟠 HIGH | [config/conexion.php](config/conexion.php) | 3-9 | Database credentials are hardcoded in source code (`postgres` / `admin123`), exposing the app's database to anyone with repository access and making secret rotation difficult. | 9/10 |

## Impact

- Attackers can create privileged accounts by exploiting the registration flow.
- Anyone with source access can obtain database credentials and read or modify application data.
- These issues are likely to be chained together for full account takeover and data exposure.

## Recommended remediation

1. Remove all hardcoded role-authorization keys from application source and move authorization into an admin-only workflow.
2. Replace credential hardcoding with environment variables or a secret manager.
3. Validate that the database credentials are not committed to the repo and add them to `.gitignore`/deployment config.
4. Consider adding server-side authorization checks and CSRF protections for all state-changing endpoints.
