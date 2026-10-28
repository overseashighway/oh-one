# OH ONE — Master Project Handover

Updated: 2026-10-29

## Owner and goal
Overseas Highway (M) Sdn. Bhd. Internal multi-office CRM and business operations system. Build for internal use first; possible SaaS later.

## Decisions
- GitHub: overseas highway organization/account repository overseas highway/oh-one (actual: overseas highway without spaces, see repo URL).
- Repository is public by owner's explicit choice. Never commit client records, passports, API keys, credentials, .env, or database exports.
- PHP + Laravel preferred, MySQL + phpMyAdmin, potential cPanel hosting pending checks.
- User will develop with AI guidance, step by step.

## Core domain workflow
Lead → Counselling → Student → University Applications (multiple per student) → Offer → Visa → Enrolment → Commission lifecycle.

## Modules
Identity & Access; Party/Contact; Case/Workflow; Task/Approval; Documents; Communication; Money/Commission; Audit/Events.

## Workspaces
Growth; Clients & Cases; Network; Money; People & Knowledge; Command.

## Current progress
- Repository confirmed accessible and empty before bootstrap.
- This documentation is the first implementation deliverable.
- No Laravel runtime, MySQL schema, login, or production deployment exists yet.

## Next action
Confirm development environment: local machine or cPanel, PHP version, Composer availability, SSH access, MySQL version. Then scaffold Laravel and commit application files with secrets excluded.

## Account portability
Source and decisions live in GitHub; production database and backups remain on owner-controlled infrastructure. New ChatGPT account can continue using this handover and repository.
