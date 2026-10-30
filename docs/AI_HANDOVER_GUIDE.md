# OH ONE — AI Account Handover Guide
Updated: 2026-10-31

## Purpose
Make OH ONE maintainable from a different personal ChatGPT account, Claude, or by a human developer. GitHub is the canonical source for code and documentation. No AI chat history or memory should be required to operate the software.

## Project and hosting
- GitHub: https://github.com/overseashighway/oh-one (main)
- CRM: https://crm.overseashighwaystudy.com
- cPanel Git clone: /home/overnzbk/repositories/oh-one
- Web document root: /home/overnzbk/repositories/oh-one/public
- Deploy: cPanel Git Version Control > Manage > Update from Remote
- PHP + MySQL; database name: overnzbk_oh_one
- Server-only config.php stores credentials and is not to be committed.
- Core files: public/index.php, leads.php, workflow.php, staff.php, login.php, password.php; app/db.php, app/security.php.
- SQL migrations: database/sql/phase1_schema.sql, phase2_auth.sql, phase3_workflow.sql.

## Implemented and tested in controlled pilot
- Founder/staff login; founder adds offices, staff and leads.
- Founder assigns/reassigns leads; staff sees assigned leads only in tested UI.
- Staff can log follow-ups, update task statuses; founder sees workload.
- Lead list search/filter is client-side for displayed 100 records.
- Dashboard includes open tasks and due follow-up records.
- Founder-only staff.php access checked: staff received Forbidden.
- Founder setup URL returned 404 (not proof file deleted).
- Database SQL backup exported manually; restore untested.
- Password change behavior inconsistent in testing; password reset deferred.

## Trial and roadmap
See docs/TEAM_TRIAL_AND_ROADMAP.md. Target: 5–6 staff across Malaysia and Bangladesh. Prioritize access verification and safe onboarding before using real student data.
Planned: technical development tracker, founder strategic goals and KPI, expanded automation and role permissions.

## Mandatory onboarding for new AI account or developer
1. Connect authorized GitHub access to this repository (read initially, write only if needed).
2. Read README, this guide, roadmap and existing source files.
3. Review latest commits, SQL schema and deployment workflow.
4. Ask owner for current priorities and confirm live deployment state; do not infer live status from GitHub alone.
5. Use a staging environment and backup/restore plan before schema or security changes.
6. Request only least-privilege access. Never paste credentials, API keys, password hashes or student records into chat or repository.
7. Keep architecture, feature status, deployment steps and decisions in GitHub docs, not only AI conversations.
8. Validate syntax and authorization before deployment; document verification evidence.

## Transfer and ownership
- New ChatGPT or Claude account does not inherit prior chat history, memory, connectors, or access rights.
- Repository can be cloned/transferred under the owner's GitHub access. Hosting and database remain independently controlled.
- Export relevant conversations if needed, sanitize personal and secret data, then create a concise project brief rather than relying on raw chat history.
- Prefer dedicated private AI accounts and individual GitHub access; revoke old sessions/tokens when switching.
- Confirm ownership of domain, GitHub organization/repository, hosting and backups.

## Security gaps / cautions
Not production-hardened. Verify server-side POST authorization, office isolation, setup token removal, active user/session behavior, rate limiting, audit logging, backups and restoration. Never commit SQL backups containing real data. Do not expose student documents in pilot.
