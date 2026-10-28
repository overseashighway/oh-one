# OH ONE Architecture — Draft v0.1

## Pattern
Modular monolith, PHP/Laravel backend, MySQL persistence. phpMyAdmin is a database administration UI, not the app framework.

## First entities
Offices, users, roles, permissions, contacts, leads, students, universities, applications, tasks, approvals, partners, referrals, invoices, payments, commissions, documents, audit_logs.

## Core relationships
- A student can have many university applications.
- Each application belongs to a student and a university.
- Staff access is constrained by role and permitted office(s).
- Commissions, OH service fees and partner referral payouts are separate ledgers/records.

## Security baseline
Server-side authorization, audit trail, encrypted transport, restricted document storage, backups, least-privilege credentials, and no sensitive client data in public GitHub.

## Implementation order
Environment check → Laravel scaffold → authentication → office roles → leads/students → applications → tasks/approvals → network → finance → automation.

This is an initial planning document, not a deployed system.
