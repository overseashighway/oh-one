# OH Website Control Center — Master Registry

Updated: 2026-11-18. Source: GitHub repository listing for `overseashighway`.

This list contains GitHub repositories, **not verified live domains/subdomains**. Do not assume a repository is deployed or operational. This is the master reference for building a unified, access-controlled website administration console.

| Project | GitHub repository | Live subdomain | Monitoring status |
|---|---|---|---|
| transfer-program | https://github.com/overseashighway/transfer-program | To verify | Not connected |
| Tech-Campus | https://github.com/overseashighway/Tech-Campus | To verify | Not connected |
| iamc | https://github.com/overseashighway/iamc | To verify | Not connected |
| crp | https://github.com/overseashighway/crp | To verify | Not connected |
| kids | https://github.com/overseashighway/kids | To verify | Not connected |
| b2b- | https://github.com/overseashighway/b2b- | To verify | Not connected |
| uniten | https://github.com/overseashighway/uniten | To verify | Not connected |
| HealthEd | https://github.com/overseashighway/HealthEd | To verify | Not connected |
| madrasha | https://github.com/overseashighway/madrasha | To verify | Not connected |
| ausnz | https://github.com/overseashighway/ausnz | To verify | Not connected |
| mypublicuni | https://github.com/overseashighway/mypublicuni | To verify | Not connected |
| Safe-Journey | https://github.com/overseashighway/Safe-Journey | To verify | Not connected |
| Register | https://github.com/overseashighway/Register | To verify | Not connected |
| skills | https://github.com/overseashighway/skills | To verify | Not connected |
| oh-one | https://github.com/overseashighway/oh-one | To verify | Not connected |
| turkeygateway | https://github.com/overseashighway/turkeygateway | To verify | Not connected |
| MM2H | https://github.com/overseashighway/MM2H | To verify | Not connected |
| study | https://github.com/overseashighway/study | To verify | Not connected |
| Saudi | https://github.com/overseashighway/Saudi | To verify | Not connected |
| hospitality | https://github.com/overseashighway/hospitality | To verify | Not connected |

## Priority modules
1. Website catalog: verified subdomain, deployment path, git branch, cPanel mapping, owner, last checked, service health.
2. Central user authentication: MFA, role-based authorization, audit logs, no public admin credentials.
3. Content and admissions: program catalog, fees, deadlines, images, Apply Now/WhatsApp links, staging and approval before publishing.
4. Central leads CRM and per-website attribution, privacy and consent handling.
5. Uptime monitoring with explicit checks/alerts, and cPanel/GitHub deployment integration.
6. Backups, deployment safety, rollback and permissions verification.

## Project notes
- `oh-one` README currently describes internal CRM architecture (Laravel/MySQL) as planning/bootstrap only, not a deployed application.
- `kids` README describes a static website with WhatsApp enquiry drafts, not stored enquiries.
- Previous HealthEd cPanel issue: directory permissions require 0750 after deploy; confirm `.cpanel.yml` per repository where applicable, and verify actual paths before editing.
- Sites and GitHub repos should remain separate; control center provides a single interface and permission layer.
- Consolidating ChatGPT project conversations is not an operation this registry performs.
