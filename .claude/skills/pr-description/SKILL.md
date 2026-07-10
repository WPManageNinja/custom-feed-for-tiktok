---
name: pr-description
description: Generate a pull request description based on the current branch's changes, using the project's PR template format.
user-invocable: true
argument-hint: [base-branch]
allowed-tools: Bash(git *), Bash(gh *), Read, Write, Glob, Grep
---

# PR Description Generator

Generate a pull request description for the current branch using the project's `.github/pull_request_template.md` format.

## Instructions

When invoked, follow these steps **in order**:

### Step 1: Determine the base branch

- If the user passed an argument, use that as the base branch.
- Otherwise default to `development`.

### Step 2: Check for user input

If the user provided any additional context, instructions, or notes alongside the command invocation, **respect and prioritize them**. User input takes precedence over auto-generated content. For example:

- If the user says "focus on the caching changes only" → scope the description accordingly.
- If the user provides a summary → use it as the basis for "What does this PR do and why?"
- If the user says "skip the SCSS fix" → omit it.
- If the user gives specific test steps → use those instead of generating your own.

Store the user's input and weave it into the final output naturally.

### Step 3: Read the PR template

Read the project's PR template file to get the latest format:

```
Read file: .github/pull_request_template.md
```

Use the **exact sections and structure** from this file as the output format. The template may have been updated — always follow what the file says, not a cached copy.

### Step 4: Gather context from git

Run these commands to understand the full scope of changes:

```bash
# Current branch name
git rev-parse --abbrev-ref HEAD

# All commits on this branch since it diverged from base
git log <base>...HEAD --pretty=format:"%h %s" --reverse

# Full diff stat
git diff <base>...HEAD --stat

# Full diff for analysis (use --no-color)
git diff <base>...HEAD --no-color
```

Read all the output carefully. Understand **every** commit and **every** changed file.

### Step 5: Check memory for additional context

Search your auto-memory directory (if available) for notes related to this branch's work. Use keywords from the branch name, changed file names, or feature area to search.

Look for:
- Architectural decisions or trade-offs documented during development
- Bug root causes that were debugged (these add valuable "why" context)
- Implementation choices that aren't obvious from the diff alone
- Anything the user explicitly asked to remember

If memory contains context that is **directly relevant to this PR's changes** and adds information **not already visible in the diff** (e.g., root cause analysis, rejected approaches, non-obvious design decisions), present it to the user:

> "I found these notes in memory that may be relevant to this PR. Should I include any of them?"
> - [brief summary of each relevant note]

**Do NOT** ask about notes that simply describe the same changes the diff already shows. Only surface genuinely additive context (the "why behind the why").

### Step 6: Present summary for review

Before writing the final file, present a **brief summary** to the user for review:

1. **PR title** (one line)
2. **Summary** (1–3 sentences of what and why)
3. **Categories** (which checklist items will be checked)
4. **Key changes** (bullet list of the main changes, grouped by area)

Then ask: **"Does this look right? Any changes before I generate the full description?"**

Wait for the user's response. If they provide revisions, incorporate them. If they approve, proceed to Step 7.

### Step 7: Generate the PR description and write to file

Fill in every section from the PR template (read in Step 3) with real content:

- Replace HTML comments with actual descriptions.
- For checklist items, check `[x]` the ones that apply, delete the rest.
- Add detailed bullet lists of specific changes, grouped logically by area.
- Fill in concrete test steps — name the exact settings page, platform, or widget type.

#### Bug fix PRs — Problem + Root cause trace

When the PR is a bug fix, prepend a **`## Problem`** block and a **`### Root cause trace`** table **before** the standard template sections. Use this format:

```markdown
## Problem

[1–3 sentences describing the symptom. Mention the specific platform, hook, service, or Vue component involved. Bold the key wrong behavior.]

### Root cause trace

| Step | What happens | Problem? |
|------|-------------|---------|
| [component / entry point] | [what it does — use inline code for function names, hook names, REST routes] | ✅ Correct / ❌ Bug / [outcome description] |
| … | … | … |

[1–2 sentences closing the trace: why the existing code or missing guard wasn't enough to prevent this.]
```

Guidelines for the table:
- **Step** — the triggering component: a feed service class, page builder widget, WordPress hook, or cron job (e.g., `TiktokFeed::refreshAccessToken`, `custom_feed_for_tiktok/tiktok_feeds_limit`, `ElementorWidget::render`)
- **What happens** — the execution chain; use backtick code spans for function names, class names, REST routes, and hook names
- **Problem?** — use `✅ Correct`, `❌ Bug`, or a short outcome phrase when neither applies (e.g., `Cache expires but stale data returned`)
- Include a row for each distinct actor/trigger that contributes to the bug, including the final user-visible consequence
- Keep rows tight — one idea per row

#### Reviewer sections — two distinct audiences

**`## Anything the reviewer should know?` — always for the bot reviewer**

This section is ALWAYS present and ALWAYS written for an automated code review bot, not a human. It should contain information that helps the bot focus its analysis and avoid false positives. Include:

- Architectural decisions or constraints driving the approach (e.g., "PHP 7.4 — no null-safe operator, used ternary instead")
- Invariants the diff relies on, including ones from the WP Social Ninja core dependency (e.g., "`RemoteAuth` only exists in core ≥ 4.3.0, guarded with `class_exists()`", "cache is always keyed by `open_id`")
- Non-obvious trade-offs or deliberate design choices
- Areas that might look wrong but are correct (e.g., "the hook fires after output buffering intentionally — see `boot/app.php`")
- Edge cases the bot should scrutinize or verify
- Files or patterns the bot can safely ignore (e.g., generated autoload files, compiled JS/CSS)

Do NOT include: setup instructions, how-to-test steps, screenshots, or human workflow context. Those belong in the human sections below.

**`## How to test` — human reviewer, include only when meaningful**

Keep this section when the PR introduces a user-visible behavior change, new feature, UI change, or bug fix that requires verification. Write concrete, specific steps:
- Which admin page or settings panel to open
- Which platform or connection to configure
- Which shortcode, widget, or Gutenberg block to use
- What input to enter and what outcome to expect

Omit this section entirely for refactors, config-only changes, or changes where the diff is self-evidently correct.

**`## Human reviewer notes` — optional, add only when needed**

Add this section (after `## How to test`) when there is context a human reviewer needs that a bot wouldn't act on:

- Environment setup required before testing (e.g., a live Google OAuth token, a specific platform API key, a WooCommerce product configured)
- Known limitations or out-of-scope items the reviewer should not flag
- Follow-up PRs this depends on or unlocks
- Anything the reviewer should manually verify that isn't captured in the test steps

Omit entirely if there's nothing substantive to add.

---

Write the final PR description to `dev-works/pr-description.md` (a findable, in-project scratch location — not `/tmp`, which is ephemeral and hidden on macOS).

**Store the exact PR title at the very top of the file** as an HTML comment, so the title is preserved verbatim for branch creation and `gh pr create`, while staying invisible when the body is pasted into GitHub:

```markdown
<!-- PR TITLE: <the exact one-line title from Step 6> -->

## What does this PR do and why?
...
```

Use the **exact** title the user approved in Step 6 — do not paraphrase or re-word it. The text after the comment is the PR body, ready to copy-paste into GitHub as-is.

Then present the path as a clickable link and output: `PR description saved to [dev-works/pr-description.md](dev-works/pr-description.md)`

> **Note:** `dev-works/pr-description.md` is intentionally gitignored (see `.gitignore`). It is a local scratch artifact — never commit it, and never `git add -f` it. Its contents are meant to be copied into the GitHub PR body.

### Step 8: Create the working branch

First check the current branch with `git rev-parse --abbrev-ref HEAD`, then decide:

- **If already on a feature branch** (anything other than `development`/`master`): a branch already exists — **skip branch creation entirely**. Do nothing in git. The skill's only output for this run is the generated title + description (already written in Step 7). Report: `Already on feature branch <name> — generated title + description only, no branch created.`
- **If currently on a base branch** (`development` or `master`): create a branch from the **exact PR title** so the working changes live on a feature branch ready to push:
  1. Derive the branch name from the PR title: lowercase it, replace every run of non-alphanumeric characters with a single hyphen, trim leading/trailing hyphens, and cap at ~50 characters. Example: `"Add plain-PHP mb_* fallbacks for servers missing mbstring"` → `add-plain-php-mb-fallbacks-for-servers-missing-mbst`.
  2. Create and switch with `git checkout -b <derived-name>`. Uncommitted working changes carry over automatically — do not stash or commit them here.
  3. Report: `Branch ready: <branch-name>`.

Only create/switch the branch in this step — committing happens in Step 9. Do not push.

### Step 9: Commit using the exact title and description

Commit the PR's changes using the **exact** title and description from `dev-works/pr-description.md` — never regenerate, paraphrase, or write a separate commit message. The commit subject and body must come verbatim from the file written in Step 7.

1. **Build the commit message from the file:**
   - **Subject line** = the exact title from the `<!-- PR TITLE: ... -->` comment (the text after `PR TITLE:`), with the `<!-- ... -->` wrapper stripped.
   - **Body** = the entire PR description body — everything in the file *after* the title comment line — copied verbatim (the `## What does this PR do and why?` section through the end, including the checked categories and all sections).
   - Append the standard `Co-Authored-By:` trailer required by the repo.
   - Write this message to a temp file and commit with `git commit -F <tempfile>` (heredocs mangle the markdown). Remove the temp file afterward.
2. **Stage only the PR's files.** Stage the changed/new files that belong to this PR. **Never** stage the always-excluded paths (see Rules): the `.claude/` directory, or `dev-works/pr-description.md` (gitignored). Stage explicitly by path — do not `git add -A`.
3. **Commit.** Run `git commit -F <tempfile>`. Do not push.
4. Report the resulting commit: `Committed <short-sha>: <title>`.

If there are no stageable changes (everything already committed), skip the commit and report that instead — do not create an empty commit.

### Step 10: Proofread (mandatory before output)

Silently fix before outputting:
- Spelling mistakes
- Wrong words (their/there, it's/its, etc.)
- Words joined without a space
- Extra or missing spaces
- Inconsistent capitalisation in headings

### Rules

- **Default base branch is `development`**, not `master`.
- **Always read the template file fresh.** Never rely on a hardcoded copy — the template may change.
- **Always show the summary first** and wait for user approval before writing the file.
- **Be concise but complete.** Don't pad with fluff.
- **Use imperative mood** for change descriptions ("Add X", "Fix Y", not "Added X").
- **Group related changes** under sub-headings if the PR touches multiple areas (e.g., Reviews vs Feeds vs Admin UI).
- **Include file paths** when referencing specific changes (e.g., `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:290`).
- **Don't include** the HTML comments from the template — fill in real content.
- **Output raw markdown** so the user can copy-paste directly into GitHub.
- **Memory context is supplementary.** Only ask about memory notes that add genuinely new information (root cause debugging, rejected alternatives, non-obvious decisions). Never ask "should I mention X?" when X is already clearly described by the diff.
- **Always exclude from the PR description:** `.claude/` directory files (local tooling, not relevant to reviewers).
- **`## Anything the reviewer should know?` is always bot-focused.** Never put human workflow context here. Never omit this section.
- **Store the exact title** as the top `<!-- PR TITLE: ... -->` comment in the file, and derive the branch name from that same title — title, file, and branch must stay consistent.
- **Never commit, stage, force-add, or push `dev-works/pr-description.md`.** It is deliberately gitignored and kept local.
- **Branch creation only runs when on a base branch** (`development`/`master`). On an existing feature branch, report it and leave it untouched — never re-branch off a feature branch.
- **Commit with the exact title and description from the file — never regenerate the commit message.** The commit subject is the `<!-- PR TITLE: ... -->` text; the commit body is the PR description body verbatim. Use `git commit -F <tempfile>`, never an inline regenerated summary.
- **Never push.** The skill creates the branch and the commit only; pushing and opening the PR are left to the user.
