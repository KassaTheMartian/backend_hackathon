# Chatbot Frontend Integration Guide

## API Response Structure

### Endpoint
```
POST /api/v1/chatbot
```

### Request
```json
{
  "message": "Tôi muốn biết về dịch vụ chăm sóc da"
}
```

### Guest session handling (session_key)

Frontend should generate a UUID-like session key for guest users and persist it in localStorage or a cookie. Include this key in every chatbot request so the backend can link messages to the same session and return history.

Example (generate & store):

```javascript
// generate once per new guest visit
const sessionKey = localStorage.getItem('chat_session_key') || crypto.randomUUID();
localStorage.setItem('chat_session_key', sessionKey);

// send with request
fetch('/api/v1/chatbot', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-Chat-Session': sessionKey, // or include in body as session_key
    'Accept-Language': 'vi'
  },
  body: JSON.stringify({ message: 'Xin chào' })
});
```

If the server creates a new session for the guest, it will return `data.session_key` in the response — update localStorage with that key.


### Response
```json
{
  "success": true,
  "message": "Response generated successfully",
  "data": {
    "message": "Chúng tôi cung cấp nhiều dịch vụ chăm sóc da chuyên nghiệp...",
    "user_id": 1,
    "locale": "vi",
    "structured_data": {
      "branches": [
        {
          "id": 1,
          "name": "Chi nhánh Quận 1",
          "address": "123 Nguyễn Huệ, Quận 1, TP.HCM",
          "phone": "0901234567",
          "email": "q1@clinic.com",
          "latitude": 10.7769,
          "longitude": 106.7009,
          "opening_hours": {
            "monday": ["09:00", "20:00"],
            "tuesday": ["09:00", "20:00"],
            "wednesday": ["09:00", "20:00"],
            "thursday": ["09:00", "20:00"],
            "friday": ["09:00", "20:00"],
            "saturday": ["08:00", "21:00"],
            "sunday": ["08:00", "21:00"]
          }
        }
      ],
      "services": [
        {
          "id": 1,
          "name": "Chăm sóc da mặt cơ bản",
          "description": "Làm sạch sâu và dưỡng ẩm cho da mặt",
          "price": 500000,
          "duration": 60,
          "image": "https://example.com/service.jpg",
          "category": {
            "id": 1,
            "name": "Chăm sóc da"
          }
        }
      ],
      "suggestions": [
        "Đặt lịch ngay",
        "Xem bảng giá"
      ]
    }
  }
}
```

## Structured Data Logic

### When Data is Included

1. **Branches**: Included when user asks about:
   - Location keywords: "chi nhánh", "địa chỉ", "branch", "location", "ở đâu", "where"
   - Or when no specific intent is detected

2. **Services**: Included when user asks about:
   - Service keywords: "dịch vụ", "service", "giá", "price", "làm đẹp", "treatment"
   - Or when no specific intent is detected

3. **Suggestions**: Quick actions based on detected intent
   - Service intent: "Đặt lịch ngay", "Xem bảng giá"
   - Branch intent: "Xem bản đồ", "Liên hệ chi nhánh"

## Frontend Implementation Examples

### React Component

```jsx
import React from 'react';

const ChatMessage = ({ data }) => {
  const { message, structured_data } = data;

  return (
    <div className="chat-message">
      {/* AI Response Text */}
      <div className="message-text">
        {message}
      </div>

      {/* Branch Cards */}
      {structured_data?.branches?.length > 0 && (
        <div className="branch-cards">
          {structured_data.branches.map(branch => (
            <BranchCard key={branch.id} branch={branch} />
          ))}
        </div>
      )}

      {/* Service Cards */}
      {structured_data?.services?.length > 0 && (
        <div className="service-cards">
          {structured_data.services.map(service => (
            <ServiceCard key={service.id} service={service} />
          ))}
        </div>
      )}

      {/* Quick Action Buttons */}
      {structured_data?.suggestions?.length > 0 && (
        <div className="suggestions">
          {structured_data.suggestions.map((suggestion, idx) => (
            <button key={idx} className="suggestion-btn">
              {suggestion}
            </button>
          ))}
        </div>
      )}
    </div>
  );
};

const BranchCard = ({ branch }) => (
  <div className="branch-card">
    <h4>{branch.name}</h4>
    <p>{branch.address}</p>
    <p>📞 {branch.phone}</p>
    {branch.latitude && branch.longitude && (
      <a 
        href={`https://maps.google.com/?q=${branch.latitude},${branch.longitude}`}
        target="_blank"
        rel="noopener noreferrer"
      >
        🗺️ Xem bản đồ
      </a>
    )}
    <WorkingHours hours={branch.opening_hours} />
  </div>
);

const ServiceCard = ({ service }) => (
  <div className="service-card">
    {service.image && <img src={service.image} alt={service.name} />}
    <h4>{service.name}</h4>
    <p>{service.description}</p>
    <div className="service-info">
      <span className="price">
        {new Intl.NumberFormat('vi-VN', { 
          style: 'currency', 
          currency: 'VND' 
        }).format(service.price)}
      </span>
      <span className="duration">⏱️ {service.duration} phút</span>
    </div>
    {service.category && (
      <span className="category">{service.category.name}</span>
    )}
    <button className="book-btn">Đặt lịch</button>
  </div>
);

const WorkingHours = ({ hours }) => {
  if (!hours) return null;

  const daysMap = {
    monday: 'T2',
    tuesday: 'T3',
    wednesday: 'T4',
    thursday: 'T5',
    friday: 'T6',
    saturday: 'T7',
    sunday: 'CN'
  };

  return (
    <div className="working-hours">
      <strong>Giờ làm việc:</strong>
      <ul>
        {Object.entries(hours).map(([day, time]) => (
          <li key={day}>
            {daysMap[day]}: {time[0]} - {time[1]}
          </li>
        ))}
      </ul>
    </div>
  );
};

export default ChatMessage;
```

### Vue Component

```vue
<template>
  <div class="chat-message">
    <!-- AI Response Text -->
    <div class="message-text">{{ data.message }}</div>

    <!-- Branch Cards -->
    <div v-if="hasBranches" class="branch-cards">
      <BranchCard 
        v-for="branch in data.structured_data.branches" 
        :key="branch.id"
        :branch="branch"
      />
    </div>

    <!-- Service Cards -->
    <div v-if="hasServices" class="service-cards">
      <ServiceCard 
        v-for="service in data.structured_data.services" 
        :key="service.id"
        :service="service"
      />
    </div>

    <!-- Suggestions -->
    <div v-if="hasSuggestions" class="suggestions">
      <button 
        v-for="(suggestion, idx) in data.structured_data.suggestions"
        :key="idx"
        class="suggestion-btn"
        @click="handleSuggestion(suggestion)"
      >
        {{ suggestion }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChatMessage',
  props: {
    data: {
      type: Object,
      required: true
    }
  },
  computed: {
    hasBranches() {
      return this.data.structured_data?.branches?.length > 0;
    },
    hasServices() {
      return this.data.structured_data?.services?.length > 0;
    },
    hasSuggestions() {
      return this.data.structured_data?.suggestions?.length > 0;
    }
  },
  methods: {
    handleSuggestion(suggestion) {
      this.$emit('suggestion-click', suggestion);
    }
  }
};
</script>
```

### Vanilla JavaScript

```javascript
function renderChatMessage(data) {
  const container = document.createElement('div');
  container.className = 'chat-message';

  // AI Response Text
  const messageText = document.createElement('div');
  messageText.className = 'message-text';
  messageText.textContent = data.message;
  container.appendChild(messageText);

  // Branch Cards
  if (data.structured_data?.branches?.length > 0) {
    const branchCards = document.createElement('div');
    branchCards.className = 'branch-cards';
    
    data.structured_data.branches.forEach(branch => {
      branchCards.appendChild(createBranchCard(branch));
    });
    
    container.appendChild(branchCards);
  }

  // Service Cards
  if (data.structured_data?.services?.length > 0) {
    const serviceCards = document.createElement('div');
    serviceCards.className = 'service-cards';
    
    data.structured_data.services.forEach(service => {
      serviceCards.appendChild(createServiceCard(service));
    });
    
    container.appendChild(serviceCards);
  }

  // Suggestions
  if (data.structured_data?.suggestions?.length > 0) {
    const suggestions = document.createElement('div');
    suggestions.className = 'suggestions';
    
    data.structured_data.suggestions.forEach(suggestion => {
      const btn = document.createElement('button');
      btn.className = 'suggestion-btn';
      btn.textContent = suggestion;
      btn.onclick = () => handleSuggestionClick(suggestion);
      suggestions.appendChild(btn);
    });
    
    container.appendChild(suggestions);
  }

  return container;
}

function createBranchCard(branch) {
  const card = document.createElement('div');
  card.className = 'branch-card';
  card.innerHTML = `
    <h4>${branch.name}</h4>
    <p>${branch.address}</p>
    <p>📞 ${branch.phone}</p>
    ${branch.latitude && branch.longitude ? 
      `<a href="https://maps.google.com/?q=${branch.latitude},${branch.longitude}" target="_blank">
        🗺️ Xem bản đồ
      </a>` : ''
    }
  `;
  return card;
}

function createServiceCard(service) {
  const card = document.createElement('div');
  card.className = 'service-card';
  
  const price = new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(service.price);
  
  card.innerHTML = `
    ${service.image ? `<img src="${service.image}" alt="${service.name}">` : ''}
    <h4>${service.name}</h4>
    <p>${service.description || ''}</p>
    <div class="service-info">
      <span class="price">${price}</span>
      <span class="duration">⏱️ ${service.duration} phút</span>
    </div>
    ${service.category ? `<span class="category">${service.category.name}</span>` : ''}
    <button class="book-btn" onclick="bookService(${service.id})">Đặt lịch</button>
  `;
  
  return card;
}
```

## Styling Examples

### CSS

```css
.chat-message {
  margin-bottom: 1rem;
}

.message-text {
  background: #f0f0f0;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

/* Branch Cards */
.branch-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.branch-card {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.branch-card h4 {
  margin-top: 0;
  color: #333;
}

/* Service Cards */
.service-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.service-card {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.service-card img {
  width: 100%;
  height: 150px;
  object-fit: cover;
}

.service-card h4 {
  padding: 1rem 1rem 0.5rem;
  margin: 0;
}

.service-card p {
  padding: 0 1rem;
  color: #666;
  font-size: 0.9rem;
}

.service-info {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 1rem;
  align-items: center;
}

.price {
  font-weight: bold;
  color: #e91e63;
  font-size: 1.1rem;
}

.duration {
  color: #666;
  font-size: 0.9rem;
}

.book-btn {
  width: 100%;
  padding: 0.75rem;
  background: #2196f3;
  color: white;
  border: none;
  cursor: pointer;
  font-weight: 500;
}

.book-btn:hover {
  background: #1976d2;
}

/* Suggestions */
.suggestions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.suggestion-btn {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #2196f3;
  color: #2196f3;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.2s;
}

.suggestion-btn:hover {
  background: #2196f3;
  color: white;
}
```

## Best Practices

1. **Check Data Availability**: Always check if `structured_data` exists before rendering
2. **Handle Empty States**: Show appropriate messages when no data is returned
3. **Responsive Design**: Cards should adapt to mobile screens
4. **Loading States**: Show skeleton loaders while waiting for API response
5. **Error Handling**: Display user-friendly error messages
6. **Accessibility**: Add proper ARIA labels and keyboard navigation
7. **Performance**: Use virtual scrolling for long lists of services/branches

## Example Use Cases

### Use Case 1: User Asks About Services
**Input**: "Tôi muốn xem dịch vụ chăm sóc da"

**Response**:
- `message`: AI-generated text about skincare services
- `structured_data.services`: Array of skincare services
- `structured_data.branches`: Empty (not relevant)
- `structured_data.suggestions`: ["Đặt lịch ngay", "Xem bảng giá"]

### Use Case 2: User Asks About Locations
**Input**: "Chi nhánh ở đâu?"

**Response**:
- `message`: AI-generated text about branch locations
- `structured_data.branches`: Array of all active branches
- `structured_data.services`: Empty (not relevant)
- `structured_data.suggestions`: ["Xem bản đồ", "Liên hệ chi nhánh"]

### Use Case 3: General Question
**Input**: "Xin chào"

**Response**:
- `message`: AI greeting and introduction
- `structured_data.branches`: Empty
- `structured_data.services`: Empty
- `structured_data.suggestions`: Empty

## Testing

```javascript
// Test API call
fetch('http://localhost:8000/api/v1/chatbot', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept-Language': 'vi'
  },
  body: JSON.stringify({
    message: 'Tôi muốn biết về dịch vụ'
  })
})
.then(res => res.json())
.then(data => {
  console.log('Response:', data);
  // Render the message
  const messageElement = renderChatMessage(data.data);
  chatContainer.appendChild(messageElement);
});
```
