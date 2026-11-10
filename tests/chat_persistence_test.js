const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync(require('node:path').join(__dirname, '../AI/chat.js'), 'utf8');
const values = new Map();
const sessionStorage = {
  getItem: (key) => values.get(key) ?? null,
  setItem: (key, value) => values.set(key, value),
  removeItem: (key) => values.delete(key),
};

function loadPage() {
  class Element {
    constructor() { this.children = []; this.handlers = {}; this.value = ''; this.hidden = false; }
    addEventListener(name, handler) { this.handlers[name] = handler; }
    setAttribute(name, value) { this[name] = value; }
    removeAttribute(name) { delete this[name]; }
    append(...children) { this.children.push(...children); }
    focus() {}
    querySelector(selector) {
      if (selector === 'p') return this.children.find((child) => child.tag === 'p');
      if (selector === '[data-room-ai-toggle]') return toggle;
      if (selector === 'button[type="submit"]') return sendButton;
      return null;
    }
    closest() { return root; }
  }
  const root = new Element();
  root.dataset = { roomChatKey: 'room-chat-test-session', roomChatRoomUrl: 'room_index.php' };
  const panel = new Element();
  const launcher = new Element();
  const messages = new Element();
  messages.scrollHeight = 100;
  messages.children.push(new Element()); // Intro message supplied by PHP.
  const input = new Element();
  const sendButton = new Element();
  const toggle = new Element();
  const logout = new Element();
  const form = new Element();
  form.action = 'AI/room_ai_search.php';
  form.elements = { namedItem: () => input };
  const document = {
    querySelector(selector) {
      return {
        '[data-room-ai-panel]': panel,
        '[data-room-ai-launcher]': launcher,
        '[data-room-ai-form]': form,
        '[data-room-ai-messages]': messages,
        '[data-room-chat-logout]': logout,
      }[selector] ?? null;
    },
    createElement(tag) { const element = new Element(); element.tag = tag; return element; },
    createTextNode(text) { return { textContent: text }; },
    addEventListener() {},
  };
  const context = {
    document, sessionStorage, console,
    window: { matchMedia: () => ({ matches: false }), fetch: () => {} },
    FormData: class { set() {} },
    fetch: async () => ({ ok: true, json: async () => ({ message: 'Saved answer', rooms: [] }) }),
  };
  vm.runInNewContext(source, context);
  return { form, input, panel, messages, logout };
}

(async () => {
  const first = loadPage();
  first.input.value = 'Can I reserve a room?';
  await first.form.handlers.submit({ preventDefault() {} });
  const second = loadPage();
  assert.equal(second.messages.children.length, 3, 'intro and both messages should survive navigation');
  assert.equal(second.messages.children[1].children[0].children[0].textContent, 'Can I reserve a room?');
  assert.equal(second.messages.children[2].children[1].children[1].textContent, 'Saved answer');
  assert.equal(second.panel.hidden, false, 'open state should survive navigation');
  second.logout.handlers.click();
  assert.equal(sessionStorage.getItem('room-chat-test-session'), null, 'logout should clear saved chat');
  console.log('chat_persistence_test: OK');
})().catch((error) => { console.error(error); process.exitCode = 1; });
