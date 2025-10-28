Chatbot Functional Specification (Aligned with Current Source)

Overview
The chatbot provides three user-facing capabilities through `ChatbotController` and `ChatbotService`:
- QA: Answer questions about the website, booking, branches, and payments.
- Booking Suggestions: Understand user intent and suggest services to book.
- Human Escalation: Route the conversation to a human staff member.

The API is designed to work for both guest users and authenticated users (Sanctum), per existing routes in `routes/api.php`.

Endpoints
- GET `/api/v1/chatbot/sessions` (auth) → list user sessions
- POST `/api/v1/chatbot/sessions` (auth) → create a chat session
- GET `/api/v1/chatbot/sessions/{id}` (auth) → session details
- GET `/api/v1/chatbot/sessions/{id}/messages` (auth) → messages for a session
- POST `/api/v1/chatbot/sessions/{id}/messages` (auth) → send message to bot
  - Body: `{ "message": string, "mode"?: "booking" | "faq" | "human" }`
- DELETE `/api/v1/chatbot/sessions/{id}` (auth) → delete session
- DELETE `/api/v1/chatbot/sessions/{id}/messages` (auth) → clear messages

Note: The current controller requires Sanctum for all chatbot operations. If guest chat is needed, relax route middleware accordingly.

Modes and Behavior
The service supports explicit `mode` or auto mode inference from the message.
- booking: Suggest services (products) relevant for booking.
- faq: Answer common booking/how-to questions.
- human: Acknowledge escalation to human support.
- default: Generic assistant reply.

Controller wiring: `ChatbotController@sendMessage` forwards `mode` to `ChatbotService::processBotResponse($sessionId, $message, $mode)`.

Output Schemas (Fixed for Frontend)
All responses return standard API envelope. The message payload structure (already used by controller):
{
  "id": number,
  "content": string,
  "type": "bot" | "user",
  "created_at": timestamp,
  "updated_at": timestamp
}

Booking Suggestions Payload
To keep the frontend simple and aligned with current code, the bot includes a machine-readable line in `content`:
- A line starting with `SUGGEST:{...}` containing JSON.
- JSON shape:
{
  "services": [
    { "service_id": number, "name": string }
  ]
}
Example `content` field (multiline):
Gợi ý dịch vụ phù hợp. Vui lòng chọn để đặt lịch.
SUGGEST:{"services":[{"service_id":1,"name":"Chăm sóc da cơ bản"},{"service_id":2,"name":"Điều trị mụn chuyên sâu"}]}

Frontend reads the `SUGGEST:` line, parses JSON, and renders suggestion cards. Selecting a service proceeds to the existing booking flow (guest or logged-in user).

FAQ Answers
Plain text content explaining how to perform actions, e.g., how to book, cancel, or pay. No special marker line required.

Human Escalation
Plain text acknowledgment: e.g., `Đã chuyển sang nhân viên hỗ trợ. Vui lòng đợi trong giây lát...`.
Further staff assignment and messaging can be implemented via `ChatSession.assigned_to` and an internal staff tool.

Gemini Integration (gmini API key)
To replace simple stubs with AI while keeping fixed outputs, use Gemini as follows:

- Config: add to `.env` and `config/services.php` (key name example)
GEMINI_API_KEY=your_key_here
`config/services.php`:
'gemini' => [
    'key' => env('GEMINI_API_KEY'),
],

- Booking prompt (returns fixed schema):
You are a booking assistant for a beauty clinic. Given the user message, return ONLY a JSON object with this exact shape:
{"services":[{"service_id":number,"name":string}, ...]}
Rules:
- Suggest 1-5 services most relevant to the message.
- service_id must match existing IDs in our database.
- Names must match our catalog.
User message: "{{USER_MESSAGE}}"

Use the model completion to produce JSON. If Gemini returns text, extract the JSON portion. Then embed into the `content` field with the `SUGGEST:` marker line, as shown above.

- FAQ prompt:
You are a support assistant for a beauty clinic website. Answer briefly in Vietnamese about how to perform actions: booking, cancel, reschedule, payment. Keep under 2 sentences.
User message: "{{USER_MESSAGE}}"

Return plain text. No special markers.

- Human escalation:
No AI call; return the standard escalation acknowledgment.

Data Sources for Suggestions
To map `service_id` and `name` reliably:
- Fetch top-N services from `Service` model filtered by basic keyword matching on `name`/`description` or category, then present in the JSON. Optionally, pre-index simple keyword → service_id lists.
- If using Gemini, post-process model suggestions by cross-checking against `Service` records before returning in `SUGGEST:` payload.

Optional Async Pattern (If Frontend Has Short Timeout)
- Add `?async=1` to POST messages. Return 202 Accepted with a placeholder, queue actual AI call, and let frontend poll `GET /chatbot/sessions/{id}/messages` for the bot message.
- This avoids frontend timeouts under slow AI responses.

Error Handling and Limits
- Rate-limit `sendMessage` per user/session.
- Truncate/validate user message length.
- On AI failure, return a graceful fallback: generic text plus no `SUGGEST:` line.

Future Enhancements
- Add `since_id` to history endpoint for efficient polling.
- Add staff UI to take over when `mode=human` and manage `assigned_to` on `ChatSession`.
- Use embeddings to improve suggestion accuracy.

Alignment With Current Code
- `ChatbotService::processBotResponse` already supports `mode` and produces the `SUGGEST:` marker for booking suggestions.
- Controller forwards `mode` from request.
- This spec formalizes the outputs for the frontend and defines how to plug Gemini in while keeping a fixed response schema.

