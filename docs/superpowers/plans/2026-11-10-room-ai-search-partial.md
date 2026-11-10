# Room AI Search Partial Setup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the room question UI with saved answers and local room search.

**Architecture:** A dashboard chat form posts to a PHP endpoint. The endpoint validates the session and question, checks saved answers, then parses room criteria locally. PHP queries `rooms` and renders matching room data.

**Tech Stack:** PHP, PDO/MySQL, existing CSS and JavaScript.

**Spec:** `docs/superpowers/specs/2026-11-10-room-ai-search-design.md`

## Global Constraints

- No external API or key is required.
- Use the current `rooms` table as the source of room facts.
- Do not claim future availability or support reservations.

## Review Focus

- Unknown question: explain what the bot can answer.
- Empty or oversized input: reject before searching.
- Unauthenticated request: redirect to login.
- Unrecognized room criteria: avoid returning every room by accident.
- No matching rooms: show a clear empty result.

---

### Task 1: Room question endpoint

**Files:** Create `AI/room_ai_search.php`; create `AI/room_ai.php`; create `tests/room_ai_test.php`.

**Interfaces:** `roomAiParseCriteria(string $question, array $buildings): array`; `roomAiFind(PDO $pdo, array $criteria): array`.

- [x] Write focused tests for local criteria parsing; run them red.
- [x] Implement request validation, local criteria parsing, and PDO filtering.
- [x] Run tests green and PHP syntax checks.

### Task 2: Dashboard form and setup notes

**Files:** Modify `room_index.php`, `Style/input.css`, and `README.md`; rebuild `Style/styles.css`.

- [x] Add the form and results layout using existing dashboard styling.
- [x] Document saved answers and local room search.
- [x] Rebuild CSS and verify PHP syntax, tests, and changed-file diff.
