<?php
$_SESSION['room_ai_csrf'] ??= bin2hex(random_bytes(32));
$chatStorageKey = 'room-chat-' . hash('sha256', session_id());
?>
<div class="room-chat" id="room-ai" data-room-chat-key="<?= e($chatStorageKey) ?>" data-room-chat-room-url="<?= e($base) ?>room_index.php">
    <section class="room-chat-panel" id="room-chat-panel" aria-labelledby="room-chat-heading" data-room-ai-panel>
        <header class="room-chat-header">
            <h2 id="room-chat-heading">Now chatting</h2>
            <button class="room-chat-close" type="button" aria-label="Minimize chat" aria-controls="room-chat-panel" data-room-ai-toggle>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 9 7 7 7-7"/></svg>
            </button>
        </header>
        <div class="room-chat-messages" role="log" aria-label="Room assistant conversation" aria-live="polite" data-room-ai-messages>
            <div class="room-chat-message room-chat-message--assistant">
                <span class="room-chat-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 5h14v11H9l-4 3V5Z"/><path d="M9 10h.01M12 10h.01M15 10h.01"/></svg></span>
                <div><span class="room-chat-sender">Room Assistant</span><p>Hi! Ask me about room availability, building, or capacity.</p></div>
            </div>
        </div>
        <form class="room-chat-form" method="post" action="<?= e($base) ?>AI/room_ai_search.php" data-room-ai-form>
            <input type="hidden" name="csrf" value="<?= e($_SESSION['room_ai_csrf']) ?>">
            <label class="sr-only" for="room-ai-question">Message the room assistant</label>
            <input id="room-ai-question" name="question" type="text" maxlength="500" required autocomplete="off" placeholder="Type here and hit enter to chat">
            <button type="submit" aria-label="Send message"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 12 18-9-5 18-4-7-9-2Z"/><path d="m12 14 9-11"/></svg></button>
        </form>
    </section>
    <button class="room-chat-launcher" type="button" aria-controls="room-chat-panel" aria-expanded="true" data-room-ai-launcher>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v11H9l-5 3V5Z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>
        <span>Room Chat</span>
    </button>
</div>
