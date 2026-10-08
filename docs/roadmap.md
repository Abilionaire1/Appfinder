# AppFinder Roadmap

The roadmap prioritizes trustworthy catalog workflows and feature completeness before a larger visual redesign. Work is delivered in small, independently reversible Git commits.

## Current baseline

- The catalog, search/filter page, software details, accounts, bookmarks, and a Windows app-bundle prototype exist.
- The catalog has 108 local entries. Recently added software without verified public rating/download data is shown without fabricated numbers.
- Admin catalog management supports administrator-only software and category workflows.
- Bookmark writes use authenticated, CSRF-protected POST requests.
- Each completed step is checked before starting the next. Existing user working-tree changes are kept out of feature commits.

## Delivery order

| # | Work item | Status | Outcome |
|---|---|---|---|
| 1 | Secure bookmark mutations | Complete | Authenticate, validate the target app, require POST and a session-bound CSRF token, and report request failures explicitly. Browser checks cover missing tokens, invalid IDs, add/remove, and the dashboard empty state. |
| 2 | Admin catalog management | Complete | Add/edit/delete software and categories with server-side role checks, CSRF validation, slug and URL validation, duplicate checks, and protection against deleting categories in use. Browser checks cover non-admin denial, CSRF rejection, CRUD flows, and category deletion protection. |
| 3 | Verify platform download links | Planned | Ensure each platform action opens the correct official destination and never falls back to an unrelated store/search page. |
| 4 | Improve catalog discovery | Planned | Correct and preserve search, category, license, and sort state; add a scalable results flow and useful empty states. |
| 5 | Correct software bundle commands | Planned | Use verified package-manager IDs instead of display names and make copy/install states reliable. |
| 6 | Add community reviews | Planned | Add validated user feedback without inventing ratings; define duplicate-submission and moderation behavior. |
| 7 | Polish responsive workflows | Planned | Review accessibility, responsive behavior, focus, motion, and interface consistency after core flows stabilize. |

## Execution notes

- Steps 1 and 2 are sequenced because admin writes reuse the CSRF and mutation protections established for bookmarks.
- Keep each work item scoped to its feature. Avoid a site-wide redesign while core workflows are still being completed.
- For each step, run the narrowest useful syntax, database, and browser checks before recording its own commit.
- Roll back a completed step with `git revert <commit>` rather than resetting the branch.
