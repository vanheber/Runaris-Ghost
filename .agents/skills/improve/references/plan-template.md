# Handoff Plan Template

Every plan is written for an executor model that has **zero context**: it has not seen the advisor session, the audit, the other plans, or any prior conversation. Assume it is competent at following explicit instructions and weak at filling gaps.

Three properties make a plan executable by a weaker model:

1. **Self-contained context** — everything needed is in the file: paths, code excerpts, conventions, commands.
2. **Verification gates** — every step ends with a command and its expected result.
3. **Hard boundaries and escape hatches** — explicit out-of-scope list, STOP conditions.

File naming: `plans/NNN-short-slug.md`, numbered in recommended execution order.

## Template

```markdown
# Plan NNN: <Imperative title>

> **Executor instructions**: Follow this plan step by step. Run every
> verification command and confirm the expected result. If any STOP
> condition occurs, stop and report — do not improvise.
>
> **Drift check**: `git diff --stat <SHA>..HEAD -- <in-scope paths>`

## Status

- **Priority**: P1 | P2 | P3
- **Effort**: S | M | L
- **Risk**: LOW | MED | HIGH
- **Depends on**: plans/NNN-*.md (or "none")
- **Category**: bug | security | perf | tests | tech-debt | migration | dx | docs | direction
- **Planned at**: commit `<short SHA>`, <YYYY-MM-DD>

## Why this matters

2–5 sentences. The problem, its concrete cost, and what improves when this lands.

## Current state

- Relevant files with their roles
- Code excerpts as they exist today (with `file:line` markers)
- Repo conventions that apply, with exemplar file
- Domain vocabulary from CONTEXT.md the executor should use

## Commands

| Purpose   | Command                  | Expected on success |
|-----------|--------------------------|---------------------|
| PHP check | `php -l <file>`          | No syntax errors    |
| Tests     | `phpunit`                | all pass            |

(Exact commands from this repo — verified during recon, not guessed.)

## Scope

**In scope**: specific files to modify (exact paths).
**Out of scope**: files that look related but must not be touched.

## Steps

### Step 1: <title>

What to do, precisely. Reference exact files/symbols.

**Verify**: `<command>` → expected output

### Step 2: ...

## Test plan

- New tests to write, in which file, covering which cases.
- Which existing test to use as pattern.
- Verification: `<test command>` → all pass.

## Done criteria

Machine-checkable. ALL must hold.

## STOP conditions

Stop and report if:

- Code at "Current state" locations doesn't match excerpts
- Verification fails twice after reasonable fix
- Fix requires touching out-of-scope file
- Key assumption is false

## Maintenance notes

For the human/agent who owns this code after the change lands.
```
