# php jer jer

## Room assistant setup

The dashboard includes a floating room chat widget for signed-in users. It answers saved questions and searches the current room status, building, and capacity; it does not handle future availability, equipment, or reservations.

The chat code is in `AI/`: `room_ai.php` handles local room search and filtering, `room_ai_search.php` handles form submissions, and `chat.js` displays answers in the widget.

To add a saved question and answer, open `AI/faq.php` and add a new `'Question?' => 'Answer.'` entry to the array returned by `roomAiFaqs()`. Saved answers are checked before room search. Matching ignores case, extra spaces, and punctuation.

No external service or API key is needed. For room search, the bot recognizes words such as “vacant” or “occupied,” a building name, and requests such as “for 30 students.” Room facts and matching results come from MySQL. Other questions receive a prompt explaining what the bot can answer.

