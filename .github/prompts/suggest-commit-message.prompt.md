---
mode: ask
description: Suggest a clean Git commit message for the current changes
tools:
  - changes
  - codebase
---

Review the current repository changes and suggest commit messages.

Requirements:
- Use Conventional Commit style
- Prefer one best title first
- Then give 3 short alternatives
- Keep the main subject concise and clear
- Mention the real scope of the changes
- If helpful, add a short body with bullet points

Output format:
1. Best commit title
2. Alternative titles
3. Optional body
