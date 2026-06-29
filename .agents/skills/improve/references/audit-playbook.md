# Audit Playbook

What to look for, per category. Each subagent (or direct audit pass) gets the relevant section plus the **Finding format** at the bottom. Adapt depth to repo size.

A finding is only a finding with evidence. "Probably has N+1 queries somewhere" is not a finding; `dashboard.blade.php:142 issues one query per item inside a loop` is.

## 1. Correctness / Bugs

- Error handling: swallowed exceptions, empty catch blocks, missing error states in UI code.
- Null/undefined flows: non-null assertions on values that can be null, unchecked array indexing.
- Boundary conditions: empty-collection handling, integer overflow.
- State machines: status enums with unhandled branches.
- Type escape hatches: `any` / `as` casts — each one is a place the compiler was overruled.
- Resource leaks: unclosed handles, connections, subscriptions; missing `finally`.

## 2. Security

Keep findings framed as defensive maintenance. **Never copy a secret into a finding** — reference `file:line` and credential type only.

**By-design is not a finding:** a tradeoff explicitly recorded in PROTOCOL.md's "Integridade do Santuário Digital" section is settled, not a finding.

- Credential hygiene: hardcoded keys/tokens/passwords, credentials in committed files, credentials logged.
- Data crossing into interpreters: SQL or shell operations from request data, HTML sinks fed by user content, dynamic execution with runtime input, filesystem paths from request data.
- Access control: endpoints without server-side identity checks, authorization enforced only in the client, object access by ID without ownership checks (IDOR).
- Input contracts: API boundaries without schema validation, file upload without type/size/storage constraints, mass assignment.
- Dependency posture: `composer audit` and `npm audit`. Report only critical/high advisories.
- Production configuration: debug/verbose behavior enabled in production, overly broad CORS, missing response-hardening headers.
- Data minimization: PII or sensitive data in logs, stack traces returned to clients, internal error details in API responses.
- **Zero telemetry compliance**: any tracking, external calls beyond Google Gemini API, or non-essential user logging per PROTOCOL.md policy.

## 3. Performance

- N+1 patterns: query per item inside loops.
- Wrong complexity: nested scans over the same collection, repeated find/filter in hot loops.
- Caching gaps: identical expensive computations repeated per request; missing memoization.
- Payload size: over-fetching, missing pagination on unbounded lists.
- Backend: synchronous work that belongs in a queue, connection-per-request patterns.
- Build/CI: slow CI from missing caching, redundant pipeline steps.

## 4. Test Coverage

- Map the critical paths (AI generation, manuscript save, export, image upload) and check which have zero or trivial coverage.
- Modules with high churn (git log) + no tests = top refactor risk.
- Verification infrastructure: is there a one-command way to know the codebase works? `phpunit` exists but may have limited coverage.

## 5. Tech Debt & Architecture

- **Duplication**: same logic re-implemented in multiple places — `Project::where('uuid', ...)->firstOrFail()` pattern, `switchToProject()` calls, CSS inline vs app.css conflicts.
- **Dead code**: unused functions (`applyAISuggestion` was recently removed), unused routes, deps no longer imported.
- **God objects**: `dashboard.blade.php` (~3500 lines) — everything touches it.
- **Inconsistent patterns**: three different ways of doing the same thing in the same repo — pick the winner and consolidate.
- **Layering violations**: UI importing from data layer internals, circular dependencies.

## 6. Dependencies & Migrations

- Major-version lag on core framework/runtime (PHP, Laravel, Bootstrap).
- Deprecated APIs in use with announced removal timelines.
- Abandoned dependencies on critical paths.
- Duplicate dependencies solving the same problem.
- Lockfile drift, version pinning inconsistencies.

## 7. DX & Tooling

- Missing or broken: typecheck script, lint config, formatter, pre-commit hooks.
- Slow feedback loops: dev-server startup times, no watch mode.
- Onboarding friction: README setup steps that are wrong/incomplete, undocumented required env vars.
- Missing `CONTEXT.md` / `AGENTS.md` — for repos where agents execute plans, this is high-leverage.

## 8. Docs

- Public API surface without reference docs.
- Architectural decisions nobody can reconstruct (why X over Y).
- Stale docs that are actively wrong (worse than missing).

## 9. Direction — features & where to take this next

Forward-looking: not what's broken, but what this codebase wants to become.

- **Unfinished intent**: TODO/FIXME clusters, feature flags never rolled out, stubbed modules.
- **Stated-but-undelivered**: README/ROADMAP.md promises with no corresponding code.
- **Surface asymmetries**: export without import, create without bulk-create, entities with CRUD minus one.
- **The adjacent possible**: capabilities the existing architecture makes disproportionately cheap.

## Finding format

```markdown
### [CATEGORY-NN] Short imperative title

- **Evidence**: `path/file.ext:line` — one-sentence description. (Repeat per location.)
- **Impact**: What goes wrong / what's being paid because of this. Concrete.
- **Effort**: S (hours) / M (a day-ish) / L (multi-day).
- **Risk**: What the fix could break; LOW/MED/HIGH plus one line why.
- **Confidence**: HIGH / MED / LOW.
- **Fix sketch**: 1–3 sentences — enough to judge effort.
```
