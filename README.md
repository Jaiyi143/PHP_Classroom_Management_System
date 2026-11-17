# Room Management System

## Room assistant setup

The dashboard includes a floating room chat widget for signed-in users. It answers saved questions and searches the current room status, building, and capacity; it does not handle future availability, equipment, or reservations.

The same chat widget appears on every signed-in page. Its messages, draft, and open or minimized state stay available while you navigate in the same browser tab. Chat history is cleared on logout or when the tab's session ends.

The chat code is in `AI/`: `room_ai.php` handles local room search and filtering, `room_ai_search.php` handles form submissions, and `chat.js` displays answers in the widget.

To add a saved question and answer, open `AI/faq.php` and add a new `'Question?' => 'Answer.'` entry to the array returned by `roomAiFaqs()`. Saved answers are checked before room search. Matching ignores case, extra spaces, and punctuation.

The chat also replies to short greetings such as “hi,” “hello,” “hellow,” and “good morning.”

No external service or API key is needed. For room search, the bot recognizes words such as “vacant” or “occupied,” a building name, and requests such as “for 30 students.” Room facts and matching results come from MySQL. Other questions receive a prompt explaining what the bot can answer.

## CSS development

Install the build tools with `npm ci`, then run `npm run build:css` when `Style/input.css` changes. Generated `node_modules/` files are not tracked in Git.

