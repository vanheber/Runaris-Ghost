# Closing the Loop — execute, reconcile, issues

The advisor's job doesn't end at the plan. This file covers the three follow-through flows: dispatching an executor and reviewing its work (`execute`), keeping the plan backlog alive (`reconcile`), and publishing plans where work gets picked up (`--issues`).

The founding rule survives unchanged: **the advisor never edits source code.** In `execute`, a *separate executor subagent* edits code in an isolated git worktree; the advisor dispatches, reviews, and renders a verdict — like a tech lead who doesn't push commits to your branch.

---

## `execute <plan>` — dispatch and review

### Preconditions (check all before dispatching)

- The repo is a git repository (worktree isolation requires it). If not: stop and say so.
- The plan file exists and its dependencies show DONE in `plans/README.md`. If not: stop, name the missing dependency.
- Run the plan's drift check yourself. If in-scope files changed since `Planned at`, reconcile the plan first — don't hand a stale plan to an executor.

### Dispatch

Spawn **one** `general-purpose` subagent with `isolation: "worktree"`. Executor model: default `sonnet`; use what the user named if they named one.

The subagent prompt must contain the full plan file text inlined (the worktree contains only committed files — if `plans/` is uncommitted, the executor can't read it).

### Review

Review like a tech lead reviewing a PR against the spec — never fix anything yourself:

1. **Re-run every done criterion** in the worktree. Don't trust the executor's report — verify.
2. **Scope compliance**: `git -C <worktree> diff --stat` against the plan's in-scope list. Any file outside scope fails review.
3. **Read the full diff.** Judge against "Why this matters" and repo conventions.
4. **Audit the new tests.** Executors game criteria — read what they actually assert.

### Verdict

| Verdict | When | Action |
|---|---|---|
| **APPROVE** | Criteria pass, scope clean, quality holds | Update index to DONE. Present to user: diff summary, worktree path. **Merging is the user's decision.** |
| **REVISE** | Fixable gaps | Send feedback to executor. Max 2 rounds, then BLOCK. |
| **BLOCK** | STOP condition, scope violation, revisions exhausted | Mark BLOCKED in index. Refine or rewrite the plan. |

---

## `reconcile` — keep `plans/` alive

Process what happened since last session. Read `plans/README.md` and every plan file, then:

| Status | Action |
|---|---|
| **DONE** | Spot-check done criteria on current HEAD. Mark verified. |
| **BLOCKED** | Read reason. Investigate obstacle. Rewrite plan or mark REJECTED. |
| **IN PROGRESS (stale)** | Flag to user; executor probably died mid-run. |
| **TODO** | Run drift check. If drifted: verify finding still exists, refresh "Current state" and SHA. If fixed: mark REJECTED. |

---

## `--issues` — publish plans as GitHub issues

1. Preflight: `gh auth status` succeeds, repo has GitHub remote.
2. Visibility check: if repo is **public**, warn before publishing security findings.
3. Per plan: `gh issue create --title "<plan title>" --body-file <plan file>`. Label: `improve`.
4. Record each issue URL in the plan's Status block and index.
