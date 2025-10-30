# Chatbot Session Memory - Implementation Guide

## Overview

The chatbot now **remembers conversation history** by storing sessions and messages in the database. This allows for context-aware conversations where the AI can reference previous messages.

**Key Features:**
- ✅ Remembers last **5 message pairs** (10 total messages: 5 user + 5 assistant)
- ✅ Authenticated users: sessions linked to `user_id`
- ✅ Guest users: sessions identified by `session_key` (UUID stored in localStorage/cookie)
- ✅ Automatic session creation on first message
- ✅ Conversation history included in Gemini API prompts for context

---

## Database Schema

### `chat_sessions` Table
```sql
CREATE TABLE chat_sessions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,           -- Nullable for guest users
    session_key VARCHAR(255) NULL UNIQUE,   -- UUID for guest identification
    meta JSON NULL,                         -- Additional metadata (optional)
    last_activity TIMESTAMP NULL,           -- Last message timestamp
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### `chat_messages` Table
```sql
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    chat_session_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED NULL,
    role ENUM('user', 'assistant'),         -- Message sender
    message TEXT,                           -- Message content
    meta JSON NULL,                         -- Optional metadata
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (chat_session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
);
```

---

## API Changes

### Request (unchanged)
```http
POST /api/v1/chatbot
Content-Type: application/json
Accept-Language: vi
```

#### For Authenticated Users
```json
{
  "message": "Xin chào, tôi muốn tư vấn về dịch vụ"
}
```
No `session_key` needed — session is linked to `user_id` automatically.

#### For Guest Users
```json
{
  "message": "Chi nhánh của bạn ở đâu?",
  "session_key": "a7b3c9e2-4f5d-8c3a-1b2e-9d8c7e6f5a4b"
}
```
Or send via header:
```http
X-Chat-Session: a7b3c9e2-4f5d-8c3a-1b2e-9d8c7e6f5a4b
```

### Response (new field added)
```json
{
  "success": true,
  "message": "Tạo phản hồi thành công",
  "data": {
    "message": "Chúng tôi có 3 chi nhánh tại...",
    "user_id": null,
    "locale": "vi",
    "session_key": "a7b3c9e2-4f5d-8c3a-1b2e-9d8c7e6f5a4b"  // ← NEW
  }
}
```

**Important:** For guests, the server may return a `session_key` if a new session was created. Frontend should save this key.

---

## Frontend Implementation

### React Example (with localStorage)

```jsx
import React, { useState, useEffect } from 'react';

const ChatBot = () => {
  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState('');
  const [sessionKey, setSessionKey] = useState(null);

  // Load or generate session key on mount
  useEffect(() => {
    const storedKey = localStorage.getItem('chat_session_key');
    if (storedKey) {
      setSessionKey(storedKey);
    } else {
      // Generate new UUID for guest
      const newKey = crypto.randomUUID();
      localStorage.setItem('chat_session_key', newKey);
      setSessionKey(newKey);
    }
  }, []);

  const sendMessage = async () => {
    if (!input.trim()) return;

    const userMessage = { role: 'user', message: input };
    setMessages(prev => [...prev, userMessage]);

    try {
      const response = await fetch('http://localhost:8000/api/v1/chatbot', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept-Language': 'vi',
          ...(sessionKey && { 'X-Chat-Session': sessionKey }),
        },
        body: JSON.stringify({
          message: input,
          session_key: sessionKey, // optional: can send in body or header
        }),
      });

      const data = await response.json();

      // Update session key if server created a new one
      if (data.data.session_key && data.data.session_key !== sessionKey) {
        localStorage.setItem('chat_session_key', data.data.session_key);
        setSessionKey(data.data.session_key);
      }

      const assistantMessage = { role: 'assistant', message: data.data.message };
      setMessages(prev => [...prev, assistantMessage]);
      setInput('');

    } catch (error) {
      console.error('Chat error:', error);
    }
  };

  return (
    <div className="chatbot">
      <div className="messages">
        {messages.map((msg, idx) => (
          <div key={idx} className={`message ${msg.role}`}>
            {msg.message}
          </div>
        ))}
      </div>
      <input
        value={input}
        onChange={(e) => setInput(e.target.value)}
        onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
      />
      <button onClick={sendMessage}>Send</button>
    </div>
  );
};
```

### Vue Example

```vue
<template>
  <div class="chatbot">
    <div class="messages">
      <div
        v-for="(msg, idx) in messages"
        :key="idx"
        :class="['message', msg.role]"
      >
        {{ msg.message }}
      </div>
    </div>
    <input
      v-model="input"
      @keypress.enter="sendMessage"
      placeholder="Type your message..."
    />
    <button @click="sendMessage">Send</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      messages: [],
      input: '',
      sessionKey: null,
    };
  },
  mounted() {
    this.sessionKey = localStorage.getItem('chat_session_key') || this.generateUUID();
    if (!localStorage.getItem('chat_session_key')) {
      localStorage.setItem('chat_session_key', this.sessionKey);
    }
  },
  methods: {
    generateUUID() {
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
      });
    },
    async sendMessage() {
      if (!this.input.trim()) return;

      this.messages.push({ role: 'user', message: this.input });

      try {
        const response = await fetch('http://localhost:8000/api/v1/chatbot', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept-Language': 'vi',
            'X-Chat-Session': this.sessionKey,
          },
          body: JSON.stringify({ message: this.input }),
        });

        const data = await response.json();

        // Update session key if changed
        if (data.data.session_key && data.data.session_key !== this.sessionKey) {
          this.sessionKey = data.data.session_key;
          localStorage.setItem('chat_session_key', this.sessionKey);
        }

        this.messages.push({ role: 'assistant', message: data.data.message });
        this.input = '';

      } catch (error) {
        console.error('Chat error:', error);
      }
    },
  },
};
</script>
```

### Vanilla JavaScript

```javascript
let sessionKey = localStorage.getItem('chat_session_key') || generateUUID();
if (!localStorage.getItem('chat_session_key')) {
  localStorage.setItem('chat_session_key', sessionKey);
}

function generateUUID() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = Math.random() * 16 | 0;
    const v = c === 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

async function sendChatMessage(message) {
  const response = await fetch('http://localhost:8000/api/v1/chatbot', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept-Language': 'vi',
      'X-Chat-Session': sessionKey,
    },
    body: JSON.stringify({ message }),
  });

  const data = await response.json();

  // Update session key if server created a new one
  if (data.data.session_key && data.data.session_key !== sessionKey) {
    sessionKey = data.data.session_key;
    localStorage.setItem('chat_session_key', sessionKey);
  }

  return data.data.message;
}
```

---

## Backend Flow

### 1. Session Resolution
When a message arrives:
- **Authenticated user:** Find or create session by `user_id`
- **Guest with session_key:** Find existing session by `session_key`
- **Guest without session_key:** Create new session with generated UUID

### 2. Load Conversation History
- Fetch last **10 messages** (5 pairs) from `chat_messages` table
- Order chronologically (oldest first)
- Include in prompt sent to Gemini API

### 3. Gemini API Prompt Structure
```
[System Instruction]

Conversation history:
User: Xin chào
Assistant: Chào bạn! Tôi có thể giúp gì cho bạn?
User: Chi nhánh ở đâu?
Assistant: Chúng tôi có 3 chi nhánh tại...

[Business Context: branches, services, etc.]

User: [new message]
```

### 4. Save Messages
After receiving Gemini response:
- Save **user message** to DB (`role='user'`)
- Save **assistant response** to DB (`role='assistant'`)
- Update `chat_sessions.last_activity`

---

## Testing

### Test Case 1: Guest User First Message
```bash
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -H "Accept-Language: vi" \
  -d '{"message":"Xin chào"}'
```

**Expected:**
- Server creates new session
- Returns `session_key` in response
- Saves 2 messages (user + assistant)

### Test Case 2: Guest User Second Message (with session_key)
```bash
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -H "Accept-Language: vi" \
  -H "X-Chat-Session: a7b3c9e2-4f5d-8c3a-1b2e-9d8c7e6f5a4b" \
  -d '{"message":"Chi nhánh của bạn ở đâu?"}'
```

**Expected:**
- Server finds existing session by `session_key`
- Loads previous conversation history
- AI response references earlier greeting
- Saves new pair of messages

### Test Case 3: Authenticated User
```bash
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -H "Accept-Language: en" \
  -d '{"message":"Tell me about your services"}'
```

**Expected:**
- Session linked to authenticated `user_id`
- No `session_key` needed
- Conversation history persists across requests

---

## Database Queries (for Reference)

### Find session for authenticated user
```php
$session = ChatSession::firstOrCreate(
    ['user_id' => $userId],
    ['session_key' => Str::uuid()]
);
```

### Find session for guest by session_key
```php
$session = ChatSession::where('session_key', $sessionKey)->first();
```

### Fetch last 10 messages (5 pairs)
```php
$messages = $session->messages()->take(10)->get()->reverse()->values();
```

### Save new messages
```php
ChatMessage::create([
    'chat_session_id' => $session->id,
    'user_id' => $userId,
    'role' => 'user',
    'message' => $userMessage,
]);

ChatMessage::create([
    'chat_session_id' => $session->id,
    'user_id' => $userId,
    'role' => 'assistant',
    'message' => $assistantResponse,
]);
```

---

## Best Practices

### 1. Session Key Management
- **Generate once per visitor** (not per conversation)
- **Persist in localStorage** for web apps
- **Use secure cookies** for better security (httpOnly, sameSite)
- **Expire old sessions** (e.g., after 30 days of inactivity)

### 2. Performance
- Limit history to 5 pairs (10 messages) to avoid large prompts
- Index `session_key` column (already done in migration)
- Consider archiving old sessions periodically

### 3. Security
- Guest sessions are **not authenticated** — don't store sensitive data
- Rate limit chat API to prevent abuse
- Validate `session_key` format (UUID) before querying

### 4. User Experience
- Show loading indicator while waiting for response
- Display conversation history on page load (fetch from DB via new endpoint if needed)
- Allow users to clear/reset conversation

---

## Future Enhancements

### Optional: Fetch Conversation History Endpoint
```http
GET /api/v1/chatbot/history
X-Chat-Session: <session_key>
```

Returns full conversation history for display on page load.

### Optional: Clear Conversation
```http
DELETE /api/v1/chatbot/session
X-Chat-Session: <session_key>
```

Deletes session and all messages (or marks inactive).

### Optional: Session Metadata
Store additional context in `chat_sessions.meta`:
```json
{
  "user_agent": "Mozilla/5.0...",
  "referrer": "google.com",
  "language_preference": "vi"
}
```

---

## Troubleshooting

### Issue: AI doesn't remember previous messages
- **Check:** Is `session_key` being sent correctly?
- **Check:** Are messages being saved to DB? (inspect `chat_messages` table)
- **Check:** Is conversation history being included in prompt? (check logs)

### Issue: Multiple sessions created for same guest
- **Check:** Is `session_key` persisting in localStorage/cookie?
- **Check:** Is frontend sending same key on every request?

### Issue: Conversation history too long
- Current limit: 5 pairs (10 messages)
- Adjust in `ChatbotService::chat()` if needed: `->take(10)`

---

## Summary

✅ **Conversation memory** now works for both authenticated and guest users  
✅ **Last 5 message pairs** included in AI prompts for context  
✅ **session_key** stored in localStorage identifies guest sessions  
✅ **Database tables** `chat_sessions` and `chat_messages` persist all conversations  
✅ **Frontend integration** requires minimal changes (generate & send session_key)

The chatbot is now **context-aware** and can have multi-turn conversations! 🎉
