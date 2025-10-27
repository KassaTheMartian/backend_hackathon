# 🌍 Hướng dẫn Đa ngôn ngữ (Localization)

## ✅ Đã implement đa ngôn ngữ trong Backend

### 1. Database Schema
Tất cả các trường có thể dịch đều được lưu dưới dạng JSON:
```json
{
  "vi": "Tiếng Việt",
  "en": "English",
  "ja": "日本語",
  "zh": "中文"
}
```

**Tables hỗ trợ đa ngôn ngữ:**
- ✅ `service_categories` - `name`, `description`
- ✅ `services` - `name`, `description`, `short_description`, `meta_title`, `meta_description`, `meta_keywords`
- ✅ `branches` - `name`, `address`, `description`, `amenities`
- ✅ `posts` - `title`, `slug`, `content`, `excerpt`, `meta_title`, `meta_description`, `meta_keywords`
- ✅ `promotions` - `name`, `description`
- ✅ `post_categories` - `name`, `description`
- ✅ `post_tags` - `name`

**User Table:**
- ✅ `language_preference` - Lưu ngôn ngữ yêu thích của user (en, vi, ja, zh)

### 2. Trait: HasLocalization

Đã tạo trait `app/Traits/HasLocalization.php` với 2 methods chính:

#### `getLocale(Request $request): string`
Detect ngôn ngữ theo thứ tự ưu tiên:

1. **User's language preference** (nếu đã login)
   ```php
   $request->user()->language_preference // 'en', 'vi', 'ja', 'zh'
   ```

2. **Query parameter** `?locale=en`
   ```php
   $request->get('locale') // 'en', 'vi', 'ja', 'zh'
   ```

3. **Accept-Language header**
   ```http
   Accept-Language: en-US,en;q=0.9,vi;q=0.8
   ```

4. **Default: Vietnamese**
   ```php
   return 'vi';
   ```

#### `getLocalizedValue(?array $jsonField, string $locale): string`
Get giá trị theo ngôn ngữ với fallback:
1. Try locale được yêu cầu (e.g., 'en')
2. Fallback to 'vi' (Vietnamese)
3. Fallback to 'en' (English)
4. Fallback to bất kỳ giá trị nào có sẵn

### 3. API Resources

Tất cả Resources đã được update để hỗ trợ đa ngôn ngữ:

#### ✅ ServiceResource
```php
use App\Traits\HasLocalization;

public function toArray(Request $request): array
{
    $locale = $this->getLocale($request);
    
    return [
        'name' => $this->getLocalizedValue($this->name, $locale),
        'description' => $this->getLocalizedValue($this->description, $locale),
        // ...
    ];
}
```

#### ✅ BranchResource
```php
use App\Traits\HasLocalization;

public function toArray(Request $request): array
{
    $locale = $this->getLocale($request);
    
    return [
        'name' => $this->getLocalizedValue($this->name, $locale),
        'address' => $this->getLocalizedValue($this->address, $locale),
        // ...
    ];
}
```

#### ✅ PostResource
```php
use App\Traits\HasLocalization;

public function toArray(Request $request): array
{
    $locale = $this->getLocale($request);
    
    return [
        'title' => $this->getLocalizedValue($this->title, $locale),
        'content' => $this->getLocalizedValue($this->content, $locale),
        // ...
    ];
}
```

## 📋 Cách sử dụng từ Frontend

### Cách 1: Sử dụng query parameter (Dễ nhất)
```javascript
// Get services in English
fetch('http://api.example.com/api/v1/services?locale=en')

// Get services in Vietnamese
fetch('http://api.example.com/api/v1/services?locale=vi')

// Get services in Japanese
fetch('http://api.example.com/api/v1/services?locale=ja')
```

### Cách 2: Sử dụng Accept-Language header (REST API best practice)
```javascript
fetch('http://api.example.com/api/v1/services', {
  headers: {
    'Accept-Language': 'en'
  }
})

// Hoặc với token
fetch('http://api.example.com/api/v1/services', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Accept-Language': 'en'
  }
})
```

### Cách 3: User đã set language preference (Ưu tiên nhất)
```javascript
// User đã login và có language_preference = 'en'
fetch('http://api.example.com/api/v1/services', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN'
  }
})
// API tự động trả về English
```

### Cách 4: Update user language preference
```javascript
// Update user's language preference
fetch('http://api.example.com/api/v1/profile', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    language_preference: 'en'
  })
})

// Từ giờ tất cả API requests sẽ trả về English
```

## 🎯 Ví dụ API Response

### Request (Vietnamese user)
```bash
GET /api/v1/services/1
Authorization: Bearer TOKEN
Accept-Language: vi
```

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "category": {
      "id": 1,
      "name": "Massage trị liệu",
      "slug": "massage-therapy"
    },
    "name": "Massage body toàn thân",
    "description": "Dịch vụ massage body toàn thân...",
    "price": 500000,
    "duration": 60
  }
}
```

### Request (English user)
```bash
GET /api/v1/services/1?locale=en
Authorization: Bearer TOKEN
```

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "category": {
      "id": 1,
      "name": "Massage Therapy",
      "slug": "massage-therapy"
    },
    "name": "Full Body Massage",
    "description": "Full body massage service...",
    "price": 500000,
    "duration": 60
  }
}
```

## 🔧 Setup Database

### Insert data với đa ngôn ngữ
```php
Service::create([
    'category_id' => 1,
    'name' => [
        'vi' => 'Massage body toàn thân',
        'en' => 'Full Body Massage',
        'ja' => '全身マッサージ',
        'zh' => '全身按摩'
    ],
    'slug' => 'full-body-massage',
    'description' => [
        'vi' => 'Dịch vụ massage chuyên nghiệp...',
        'en' => 'Professional massage service...',
        // ...
    ],
    'price' => 500000,
    'duration' => 60,
    // ...
]);
```

### Update language preference
```php
// Via ProfileController
PUT /api/v1/profile
{
  "language_preference": "en"
}

// Hoặc trong code
$user->update(['language_preference' => 'en']);
```

## 📊 Supported Languages

- ✅ `vi` - Tiếng Việt (Vietnamese)
- ✅ `en` - English
- ✅ `ja` - 日本語 (Japanese)
- ✅ `zh` - 中文 (Chinese)

**Default:** `vi` (Vietnamese)

## 🚀 Testing

### Test với Postman/Insomnia

1. **Test với query parameter:**
   ```
   GET http://localhost:8000/api/v1/services?locale=en
   ```

2. **Test với Accept-Language header:**
   ```
   GET http://localhost:8000/api/v1/services
   Header: Accept-Language: en
   ```

3. **Test với authenticated user:**
   ```
   GET http://localhost:8000/api/v1/services
   Header: Authorization: Bearer YOUR_TOKEN
   ```
   (Tự động detect từ user->language_preference)

### Test với cURL
```bash
# Vietnamese (default)
curl http://localhost:8000/api/v1/services

# English
curl "http://localhost:8000/api/v1/services?locale=en"

# Japanese
curl "http://localhost:8000/api/v1/services?locale=ja"

# With Accept-Language header
curl -H "Accept-Language: en" http://localhost:8000/api/v1/services

# With authenticated user
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/v1/services
```

## 🎨 Best Practices

### 1. Always provide fallback values
```json
{
  "name": {
    "vi": "Dịch vụ",
    "en": "Service",
    "ja": "",
    "zh": ""
  }
}
```

### 2. Keep translations consistent
- Use same terminology across app
- Document translations
- Review with native speakers

### 3. Handle missing translations gracefully
```php
// getLocalizedValue() automatically falls back
// 1. Try requested locale
// 2. Fallback to 'vi'
// 3. Fallback to 'en'
// 4. Return empty string if nothing available
```

### 4. Cache translations (future optimization)
```php
// Cache localized responses
Cache::remember("service_{$id}_en", 3600, function() {
    return ServiceResource::make($service);
});
```

## 📝 Migration Example

```php
// Create service with multilingual data
Schema::create('services', function (Blueprint $table) {
    $table->json('name')->comment('{"vi": "", "en": "", "ja": "", "zh": ""}');
    $table->json('description')->nullable();
    // ...
});

// Insert data
DB::table('services')->insert([
    'name' => json_encode([
        'vi' => 'Massage body toàn thân',
        'en' => 'Full Body Massage',
        'ja' => '全身マッサージ',
        'zh' => '全身按摩'
    ]),
    // ...
]);
```

## 🔗 Related Files

- `app/Traits/HasLocalization.php` - Main localization logic
- `app/Http/Resources/Service/ServiceResource.php` - Service localization
- `app/Http/Resources/Branch/BranchResource.php` - Branch localization
- `app/Http/Resources/Post/PostResource.php` - Post localization
- `app/Models/User.php` - User language preference
- `app/Services/ProfileService.php` - Update language preference

---

**Status**: ✅ Fully Implemented
**Last Updated**: January 2025

