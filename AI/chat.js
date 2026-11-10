(() => {
  const panel = document.querySelector('[data-room-ai-panel]');
  const launcher = document.querySelector('[data-room-ai-launcher]');
  const form = document.querySelector('[data-room-ai-form]');
  const messages = document.querySelector('[data-room-ai-messages]');
  const input = form?.elements.namedItem('question');
  const sendButton = form?.querySelector('button[type="submit"]');
  if (!panel || !launcher || !form || !messages || !input || !sendButton) return;

  const setOpen = (open) => {
    panel.hidden = !open;
    launcher.setAttribute('aria-expanded', String(open));
    if (open) input.focus();
    else launcher.focus();
  };

  launcher.addEventListener('click', () => setOpen(panel.hidden));
  panel.querySelector('[data-room-ai-toggle]')?.addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) setOpen(false);
  });
  if (window.matchMedia('(max-width: 600px)').matches) {
    panel.hidden = true;
    launcher.setAttribute('aria-expanded', 'false');
  }

  const addMessage = (text, fromUser = false) => {
    const row = document.createElement('div');
    row.className = `room-chat-message room-chat-message--${fromUser ? 'user' : 'assistant'}`;
    if (!fromUser) {
      const avatar = document.createElement('span');
      avatar.className = 'room-chat-avatar';
      avatar.setAttribute('aria-hidden', 'true');
      avatar.textContent = 'AI';
      row.append(avatar);
    }
    const body = document.createElement('div');
    if (!fromUser) {
      const sender = document.createElement('span');
      sender.className = 'room-chat-sender';
      sender.textContent = 'Room Assistant';
      body.append(sender);
    }
    const bubble = document.createElement('p');
    bubble.textContent = text;
    body.append(bubble);
    row.append(body);
    messages.append(row);
    messages.scrollTop = messages.scrollHeight;
    return body;
  };

  if (!window.fetch) return;
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (sendButton.disabled) return;
    const question = input.value.trim();
    if (!question) return;

    addMessage(question, true);
    input.value = '';
    const reply = addMessage('Searching rooms…');
    const replyText = reply.querySelector('p');
    sendButton.disabled = true;
    messages.setAttribute('aria-busy', 'true');
    try {
      const body = new FormData(form);
      body.set('question', question);
      const response = await fetch(form.action, {
        method: 'POST',
        headers: { Accept: 'application/json' },
        body,
        credentials: 'same-origin',
      });
      const result = await response.json();
      if (typeof result.message !== 'string' || !Array.isArray(result.rooms)) throw new Error('Invalid response');
      replyText.textContent = result.message;
      if (response.ok && result.rooms.length) {
        const list = document.createElement('ul');
        list.className = 'room-chat-room-list';
        result.rooms.forEach((room) => {
          const id = Number(room.room_id);
          if (!Number.isInteger(id) || id < 1) return;
          const item = document.createElement('li');
          const link = document.createElement('a');
          link.href = `room_index.php#room-${id}`;
          link.textContent = String(room.room_name);
          link.addEventListener('click', () => {
            panel.hidden = true;
            launcher.setAttribute('aria-expanded', 'false');
          });
          item.append(link, document.createTextNode(` · ${room.building} · ${room.capacity} seats · ${room.status}`));
          list.append(item);
        });
        reply.append(list);
      }
    } catch (_) {
      replyText.textContent = 'I could not load an answer right now. Please try again.';
    } finally {
      sendButton.disabled = false;
      messages.removeAttribute('aria-busy');
      messages.scrollTop = messages.scrollHeight;
      input.focus();
    }
  });
})();
