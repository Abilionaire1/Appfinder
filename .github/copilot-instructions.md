# AppFinder Copilot Instructions

## Project context

- AppFinder is an Indonesian-language directory for discovering legal, free, and open-source software.
- The application uses native PHP with PDO, MySQL, server-rendered pages, and Tailwind CSS via CDN.
- Preserve the current stack and page structure. Do not introduce a frontend framework or replace backend code as part of UI work.

## UI and design

- Use `/ui-ux-pro-max` for UI/UX design, implementation, and review tasks. For implementation guidance, search the `html-tailwind` stack and relevant UX domains.
- Follow the established clean, modern, curated engineering aesthetic. Keep layouts clear, polished, and functional; avoid neon-heavy palettes, loud gradients, and excessive glassmorphism.
- Treat skill search results as recommendations. Check them against AppFinder's product, existing UI, and this project's design direction before applying them.
- Keep the interface responsive, keyboard-accessible, and consistent with the Indonesian copy and existing visual language.

## Change safety

- Keep changes scoped to the requested page or component; do not turn a focused task into a site-wide redesign.
- For a major visual direction change, use a dedicated feature branch and a focused Git commit so it can be reverted independently.
- Never include pre-existing or unrelated working-tree changes when staging or committing.
