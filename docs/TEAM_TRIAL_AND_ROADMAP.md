# OH ONE — Controlled Pilot and Product Roadmap
Updated: 2026-10-31

## Immediate objective: 5–6 real staff in controlled trial
- Founder creates individual accounts and offices, issues unique temporary passwords through a private channel.
- Staff can see only assigned leads and tasks; no student document uploads during pilot.
- Start with minimal/non-sensitive lead records; obtain appropriate consent and follow applicable privacy obligations.
- Founder monitors daily lead assignment, follow-ups, tasks and blockers.
- Before live onboarding: verify office separation, authorization on POST actions, backup restoration, and remove setup credentials/tokens. Test password-change behavior; do not rely on an unverified reset workflow.
- Trial acceptance: each staff member signs in, sees only permitted leads, logs a follow-up, completes a task; founder confirms results.

## Product modules
### Operations
Lead management, assignments, follow-ups, tasks, office dashboards and workload.

### Development & documentation portal (planned)
Founder/technical-team roles; roadmap features with status (planned/in progress/testing/released), owner, priority, acceptance criteria, blockers, release notes, bug reports and evidence links. No access to student PII by default. A feature is only 'released' after deployment and verification.

### Strategy & goals (planned)
Founder/admin define company vision, annual and quarterly objectives, measurable KPIs, targets, baselines, owners and deadlines. Link monthly milestones and operational tasks to goals. Track actual vs target with dated progress updates and evidence; avoid conflating task completion with business outcomes.

## Delivery sequence
P0: security checks, staff onboarding, pilot runbook, feedback log.
P1: usable daily staff queue and founder monitoring, basic validation, office segregation.
P2: roadmap/documentation portal and issue tracker with scoped technical role.
P3: goals/KPI module, progress dashboard and linked execution milestones.
P4: notifications, automation, granular permissions and integrations.

## Known limitations
Not production-hardened. Staff password reset deferred. Browser-side lead search covers only the 100 visible records. Current dashboard follow-up counter counts records, not unique leads. Backup export downloaded but restore not tested. Existing tests are limited and not a security audit.

## Finance and staff task expansion (requested 2026-10-31)
Build incrementally, without delaying the Malaysia + Bangladesh 5–6-person pilot.

### Phase 1: simple income/expense ledger
Record date, office, category, description, amount, currency (MYR/BDT and others), payment status, creator, and optional related student/service. Keep income and expenses separate and calculate monthly office-wise totals. Do not combine different currencies without an explicit dated exchange rate. Limit finance access by role.

### Phase 2: controlled expense workflows
Expense submission by staff; approval/rejection by founder/admin; receipt attachments with access controls; payment method, reference, and audit history. Distinguish submitted, approved, paid and rejected. Prevent deletion of approved transactions without reversal/audit.

### Phase 3: management accounting
Recurring rent, salaries, utilities, marketing; monthly budgets and variance, service revenue and commission receivables, cashflow reporting and export. Financial dashboard should not claim accounting/tax compliance without review.

### Staff tasks and strategic alignment
Tasks need office, assignee, deadline, priority, status, outcome and optional link to lead, expense or strategic goal. Founder goals are measurable targets and should not be reduced to checklists. Allow founder to monitor completion and overdue work across offices; staff can see only their own permitted work.

### Guardrails
Finance records are sensitive. Use server-side authorization, audit trails, backups, and secure attachment handling before real financial data. Never commit bank details or financial records to GitHub.
