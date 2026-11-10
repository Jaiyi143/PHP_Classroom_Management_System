# Room AI search design

## Intent

Let signed-in students and teachers ask for a suitable campus room in ordinary language, such as “Find a vacant room for 30 students in the Engineering Building.” Show matches from the current `rooms` table, with a short explanation and links to each room.

## Approaches

- **Current: PHP-only question matching.** Saved questions are answered from `AI/faq.php`; room requests are parsed for status, building, and capacity before querying MySQL. It needs no external service or key, but recognizes a narrower range of wording.
- **Previously considered: external question interpretation.** This option was removed because the owner does not want to use an API key.

## Scope

- Add an “Ask about rooms” form to the signed-in dashboard.
- Interpret requests for vacancy, minimum capacity, and building in PHP. Use bound query parameters for room searches.
- Query the existing `rooms` table on each request. Show matched room cards or links, and state when no rooms match or the request needs clarification.
- Keep questions and room records on the local server.
- Limit request length, require an authenticated session, and display a useful error if room search is unavailable.
- Document how to edit saved answers and verify room searches.

## Limits

The database stores only each room’s current Vacant/Occupied status. The assistant cannot answer future availability, reserve rooms, or infer equipment. Those questions should receive an explicit limitation instead of a guess.

## Implementation decisions left to the builder

Fit the widget into the existing dashboard styles. Verify saved answers, capacity, building, vacancy, no matches, and unsupported scheduling requests.
