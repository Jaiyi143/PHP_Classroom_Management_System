(() => {
  const panel = document.querySelector('[data-room-ai-panel]');
  const launcher = document.querySelector('[data-room-ai-launcher]');
  const form = document.querySelector('[data-room-ai-form]');
  const messages = document.querySelector('[data-room-ai-messages]');
  const input = form?.elements.namedItem('question');
  const sendButton = form?.querySelector('button[type="submit"]');
  const chatRoot = panel?.closest('[data-room-chat-key]');
  if (!panel || !launcher || !form || !messages || !input || !sendButton || !chatRoot) return;

  const storageKey = chatRoot.dataset.roomChatKey;
  let savedState = {};
  try {
    savedState = JSON.parse(sessionStorage.getItem(storageKey) || '{}');
  } catch (_) {
    savedState = {};
  }
  if (!savedState || typeof savedState !== 'object') savedState = {};
  const history = Array.isArray(savedState.history)
    ? savedState.history.filter((item) => item && ['user', 'assistant'].includes(item.role) && typeof item.text === 'string').slice(-40)
    : [];

  const persist = () => {
    try {
      sessionStorage.setItem(storageKey, JSON.stringify({
        open: !panel.hidden,
        draft: input.value.slice(0, 500),
        history: history.slice(-40),
      }));
    } catch (_) {
      // Chat remains usable when browser storage is disabled.
    }
  };

  const setOpen = (open) => {
    panel.hidden = !open;
    launcher.setAttribute('aria-expanded', String(open));
    if (open) input.focus();
    else launcher.focus();
    persist();
  };

  launcher.addEventListener('click', () => setOpen(panel.hidden));
  panel.querySelector('[data-room-ai-toggle]')?.addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) setOpen(false);
  });
  input.addEventListener('input', persist);

  const appendRooms = (body, rooms) => {
    if (!Array.isArray(rooms) || !rooms.length) return;
    const list = document.createElement('ul');
    list.className = 'room-chat-room-list';
    rooms.slice(0, 30).forEach((room) => {
      const id = Number(room.room_id);
      if (!Number.isInteger(id) || id < 1) return;
      const item = document.createElement('li');
      const link = document.createElement('a');
      link.href = `${chatRoot.dataset.roomChatRoomUrl}#room-${id}`;
      link.textContent = String(room.room_name);
      link.addEventListener('click', () => setOpen(false));
      item.append(link, document.createTextNode(` · ${room.building} · ${room.capacity} seats · ${room.status}`));
      list.append(item);
    });
    body.append(list);
  };

  const addMessage = (text, fromUser = false, rooms = []) => {
    const row = document.createElement('div');
    row.className = `room-chat-message room-chat-message--${fromUser ? 'user' : 'assistant'}`;
    if (!fromUser) {
      const avatar = document.createElement('span');
      avatar.className = 'room-chat-avatar';
      avatar.setAttribute('aria-hidden', 'true');
      const logo = document.createElement('img');
      logo.src = chatRoot.dataset.roomChatLogoUrl;
      logo.alt = '';
      avatar.append(logo);
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
    if (!fromUser) appendRooms(body, rooms);
    row.append(body);
    messages.append(row);
    messages.scrollTop = messages.scrollHeight;
    return body;
  };

  history.forEach((item) => addMessage(item.text.slice(0, 2000), item.role === 'user', item.rooms));
  if (typeof savedState.draft === 'string') input.value = savedState.draft.slice(0, 500);
  const open = typeof savedState.open === 'boolean'
    ? savedState.open
    : !window.matchMedia('(max-width: 600px)').matches;
  panel.hidden = !open;
  launcher.setAttribute('aria-expanded', String(open));

  if (!window.fetch) return;
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (sendButton.disabled) return;
    const question = input.value.trim();
    if (!question) return;

    addMessage(question, true);
    history.push({ role: 'user', text: question });
    input.value = '';
    persist();
    const reply = addMessage('');
    const replyText = reply.querySelector('p');
    replyText.classList.add('room-chat-typing');
    replyText.setAttribute('aria-label', 'Room Assistant is typing');
    replyText.innerHTML = '<span></span><span></span><span></span>';
    const startedTyping = performance.now();
    sendButton.disabled = true;
    messages.setAttribute('aria-busy', 'true');
    let answer;
    let rooms = [];
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
      answer = result.message;
      rooms = response.ok ? result.rooms : [];
    } catch (_) {
      answer = 'I could not load an answer right now. Please try again.';
    } finally {
      const remaining = Math.max(0, 600 - (performance.now() - startedTyping));
      if (remaining) await new Promise((resolve) => setTimeout(resolve, remaining));
      replyText.classList.remove('room-chat-typing');
      replyText.removeAttribute('aria-label');
      replyText.textContent = answer;
      appendRooms(reply, rooms);
      history.push({ role: 'assistant', text: answer, rooms });
      persist();
      sendButton.disabled = false;
      messages.removeAttribute('aria-busy');
      messages.scrollTop = messages.scrollHeight;
      input.focus();
    }
  });
})();
