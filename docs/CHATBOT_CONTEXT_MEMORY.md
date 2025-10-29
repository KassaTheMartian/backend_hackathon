# Chatbot Context Memory (Conversation History)

## Tổng Quan

Chatbot giờ đã hỗ trợ nhớ context của cuộc hội thoại trước đó. Frontend cần gửi lịch sử trò chuyện lên API để chatbot có thể hiểu ngữ cảnh và trả lời chính xác hơn.

## API Request với History

### Request Format

```json
{
  "message": "Giá bao nhiêu?",
  "history": [
    {
      "role": "user",
      "content": "Tôi muốn biết về dịch vụ chăm sóc da"
    },
    {
      "role": "assistant", 
      "content": "Chúng tôi có các dịch vụ chăm sóc da: Làm sạch da, Trị mụn, Trẻ hóa da..."
    },
    {
      "role": "user",
      "content": "Dịch vụ làm sạch da như thế nào?"
    },
    {
      "role": "assistant",
      "content": "Dịch vụ làm sạch da bao gồm..."
    }
  ]
}
```

### Validation Rules

- `message`: Required, string, 1-1000 characters
- `history`: Optional, array, max 20 messages
- `history[].role`: Required if history exists, must be "user" or "assistant"
- `history[].content`: Required if history exists, max 2000 characters

## Frontend Implementation

### React Example

```jsx
import React, { useState } from 'react';

const Chatbot = () => {
  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);

  const sendMessage = async () => {
    if (!input.trim()) return;

    // Add user message to UI
    const userMessage = { role: 'user', content: input };
    setMessages(prev => [...prev, userMessage]);
    setInput('');
    setLoading(true);

    try {
      // Prepare history (exclude current message, max 20 previous messages)
      const history = messages.slice(-20);

      // Call API
      const response = await fetch('http://localhost:8000/api/v1/chatbot', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept-Language': 'vi'
        },
        body: JSON.stringify({
          message: input,
          history: history
        })
      });

      const data = await response.json();

      if (data.success) {
        // Add assistant response to UI
        const assistantMessage = {
          role: 'assistant',
          content: data.data.message,
          structured_data: data.data.structured_data
        };
        setMessages(prev => [...prev, assistantMessage]);
      }
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="chatbot">
      <div className="messages">
        {messages.map((msg, idx) => (
          <div key={idx} className={`message ${msg.role}`}>
            {msg.content}
            {msg.structured_data && (
              <StructuredData data={msg.structured_data} />
            )}
          </div>
        ))}
        {loading && <div className="loading">...</div>}
      </div>
      
      <div className="input-area">
        <input
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
          placeholder="Nhập tin nhắn..."
        />
        <button onClick={sendMessage}>Gửi</button>
      </div>
    </div>
  );
};

export default Chatbot;
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
        {{ msg.content }}
        <StructuredData 
          v-if="msg.structured_data" 
          :data="msg.structured_data" 
        />
      </div>
      <div v-if="loading" class="loading">...</div>
    </div>

    <div class="input-area">
      <input
        v-model="input"
        @keyup.enter="sendMessage"
        placeholder="Nhập tin nhắn..."
      />
      <button @click="sendMessage">Gửi</button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      messages: [],
      input: '',
      loading: false
    };
  },
  methods: {
    async sendMessage() {
      if (!this.input.trim()) return;

      // Add user message
      this.messages.push({
        role: 'user',
        content: this.input
      });

      const currentInput = this.input;
      this.input = '';
      this.loading = true;

      try {
        // Prepare history (last 20 messages, exclude current)
        const history = this.messages
          .slice(0, -1)  // Exclude the message we just added
          .slice(-20)
          .map(msg => ({
            role: msg.role,
            content: msg.content
          }));

        // Call API
        const response = await fetch('http://localhost:8000/api/v1/chatbot', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept-Language': 'vi'
          },
          body: JSON.stringify({
            message: currentInput,
            history: history
          })
        });

        const data = await response.json();

        if (data.success) {
          // Add assistant response
          this.messages.push({
            role: 'assistant',
            content: data.data.message,
            structured_data: data.data.structured_data
          });
        }
      } catch (error) {
        console.error('Error:', error);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
```

### Vanilla JavaScript

```javascript
class Chatbot {
  constructor(apiUrl) {
    this.apiUrl = apiUrl;
    this.messages = [];
  }

  async sendMessage(message) {
    // Add user message to history
    this.messages.push({
      role: 'user',
      content: message
    });

    // Prepare history (last 20 messages, exclude current)
    const history = this.messages
      .slice(0, -1)
      .slice(-20)
      .map(msg => ({
        role: msg.role,
        content: msg.content
      }));

    try {
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept-Language': 'vi'
        },
        body: JSON.stringify({
          message: message,
          history: history
        })
      });

      const data = await response.json();

      if (data.success) {
        // Add assistant response to history
        this.messages.push({
          role: 'assistant',
          content: data.data.message,
          structured_data: data.data.structured_data
        });

        return data.data;
      } else {
        throw new Error(data.message);
      }
    } catch (error) {
      console.error('Error sending message:', error);
      throw error;
    }
  }

  clearHistory() {
    this.messages = [];
  }

  getHistory() {
    return this.messages;
  }
}

// Usage
const chatbot = new Chatbot('http://localhost:8000/api/v1/chatbot');

// Send first message
await chatbot.sendMessage('Xin chào');
// Response: "Xin chào! Tôi có thể giúp gì cho bạn?"

// Send follow-up (bot remembers context)
await chatbot.sendMessage('Cho tôi biết về dịch vụ chăm sóc da');
// Response: AI knows this is a continuation of the conversation

// Send another follow-up
await chatbot.sendMessage('Giá bao nhiêu?');
// Response: AI knows you're asking about skincare service prices
```

## LocalStorage Persistence

### Save Conversation

```javascript
const saveChatHistory = (messages) => {
  localStorage.setItem('chatHistory', JSON.stringify(messages));
};

const loadChatHistory = () => {
  const saved = localStorage.getItem('chatHistory');
  return saved ? JSON.parse(saved) : [];
};

// In your component
const [messages, setMessages] = useState(() => loadChatHistory());

// Save whenever messages change
useEffect(() => {
  saveChatHistory(messages);
}, [messages]);
```

## Context Window Management

### Limit to Last 20 Messages

```javascript
const getRecentHistory = (messages, limit = 20) => {
  // Get last N messages, excluding the current one
  return messages
    .slice(-limit - 1, -1)
    .map(msg => ({
      role: msg.role,
      content: msg.content
    }));
};
```

### Clear Old Conversations

```javascript
const clearOldConversations = () => {
  setMessages([]);
  localStorage.removeItem('chatHistory');
};
```

## Example Conversations

### Conversation 1: Follow-up Questions

```
User: "Xin chào"
Assistant: "Xin chào! Tôi có thể giúp gì cho bạn?"

User: "Tôi muốn biết về dịch vụ làm đẹp"
Assistant: "Chúng tôi có các dịch vụ: Chăm sóc da, Spa body, Triệt lông..."

User: "Dịch vụ chăm sóc da có những gì?"
Assistant: "Dịch vụ chăm sóc da bao gồm..."

User: "Giá bao nhiêu?"
Assistant: "Giá dịch vụ chăm sóc da từ 500.000đ - 2.000.000đ..."
// Bot nhớ đang nói về "dịch vụ chăm sóc da"
```

### Conversation 2: Context References

```
User: "Chi nhánh ở đâu?"
Assistant: "Chúng tôi có 3 chi nhánh: Quận 1, Quận 3, Quận 7..."

User: "Chi nhánh Quận 1 mở cửa lúc mấy giờ?"
Assistant: "Chi nhánh Quận 1 mở cửa: T2-T6: 9h-20h, T7-CN: 8h-21h"
// Bot nhớ user đang hỏi về "Chi nhánh Quận 1"

User: "Số điện thoại là gì?"
Assistant: "Số điện thoại chi nhánh Quận 1: 0901234567"
// Bot biết "số điện thoại" đang refer to "Chi nhánh Quận 1"
```

## Best Practices

### 1. Message Truncation
```javascript
// Limit message length to prevent API errors
const truncateMessage = (content, maxLength = 2000) => {
  return content.length > maxLength 
    ? content.substring(0, maxLength) 
    : content;
};
```

### 2. History Cleanup
```javascript
// Remove structured_data from history (only send text)
const cleanHistory = (messages) => {
  return messages.map(msg => ({
    role: msg.role,
    content: msg.content
  }));
};
```

### 3. Error Handling
```javascript
try {
  const response = await sendMessage(message, history);
} catch (error) {
  if (error.response?.status === 422) {
    // Validation error - maybe history too large
    console.log('Clearing history and retrying...');
    const response = await sendMessage(message, []); // Retry without history
  }
}
```

### 4. Loading States
```javascript
const [isTyping, setIsTyping] = useState(false);

const sendMessage = async () => {
  setIsTyping(true);
  try {
    // ... API call
  } finally {
    setIsTyping(false);
  }
};
```

## Testing

### Test Context Memory

```bash
# First message
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -d '{"message":"Tôi muốn biết về dịch vụ chăm sóc da"}'

# Follow-up (with history)
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Giá bao nhiêu?",
    "history": [
      {"role":"user","content":"Tôi muốn biết về dịch vụ chăm sóc da"},
      {"role":"assistant","content":"Chúng tôi có các dịch vụ chăm sóc da..."}
    ]
  }'
```

## Troubleshooting

### History không hoạt động
- Kiểm tra format của history array
- Đảm bảo role là "user" hoặc "assistant"
- Không vượt quá 20 messages

### Response không liên quan
- History quá dài → Giảm số lượng messages
- Content bị truncate → Tăng maxLength validation

### Performance issues
- Quá nhiều history → Limit to recent 10-15 messages
- Message quá dài → Truncate old messages

## Migration Guide

Nếu đang có chatbot không có history, update như sau:

```javascript
// Before
fetch('/api/v1/chatbot', {
  body: JSON.stringify({ message: input })
});

// After
fetch('/api/v1/chatbot', {
  body: JSON.stringify({ 
    message: input,
    history: messages.slice(-20).map(m => ({ role: m.role, content: m.content }))
  })
});
```
