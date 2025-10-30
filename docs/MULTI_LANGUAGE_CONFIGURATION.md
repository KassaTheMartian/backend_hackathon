# Multi-Language Configuration Guide

## Overview

The application now supports dynamic multi-language configuration for Posts and other translatable content. Languages are configured centrally and automatically applied throughout the system.

---

## Current Configuration

### File: `config/localization.php`

```php
return [
    // Application supported locales
    'supported' => ['en', 'vi', 'ja', 'zh'],

    // Default locale for translatable inputs
    'default' => 'en',

    // Fallback order when reading localized values
    'fallbacks' => ['en', 'vi'],
];
```

### Supported Languages
- 🇬🇧 **English** (`en`) - Default
- 🇻🇳 **Vietnamese** (`vi`)
- 🇯🇵 **Japanese** (`ja`)
- 🇨🇳 **Chinese** (`zh`)

---

## How to Add a New Language

### Step 1: Update Configuration

Edit `config/localization.php`:

```php
return [
    'supported' => [
        'en',  // English
        'vi',  // Vietnamese
        'ja',  // Japanese
        'zh',  // Chinese
        'ko',  // Korean (NEW)
        'th',  // Thai (NEW)
        'id',  // Indonesian (NEW)
    ],
    
    'default' => 'en',
    
    'fallbacks' => ['en', 'vi'], // Can add more fallbacks
];
```

### Step 2: Add Translation Files

Create new language files in `resources/lang/`:

```bash
resources/lang/
├── en/
│   ├── messages.php
│   ├── validation.php
│   └── ...
├── vi/
│   ├── messages.php
│   └── ...
├── ko/          # NEW Korean
│   ├── messages.php
│   └── ...
└── th/          # NEW Thai
    ├── messages.php
    └── ...
```

### Step 3: Database Schema (Already Supports JSON)

Posts table uses JSON fields for translatable content:

```sql
-- Posts table schema
CREATE TABLE posts (
    id BIGINT PRIMARY KEY,
    title JSON,        -- {"en": "Title", "vi": "Tiêu đề", "ko": "제목"}
    slug JSON,         -- {"en": "title-slug", "vi": "tieu-de", "ko": "je-mok"}
    content JSON,      -- {"en": "Content...", "vi": "Nội dung...", "ko": "내용..."}
    excerpt JSON,      -- {"en": "Excerpt", "vi": "Trích dẫn", "ko": "발췌"}
    ...
);
```

**No migration needed!** JSON fields automatically support any number of languages.

### Step 4: API Request Format

When creating/updating posts, include all supported languages:

```json
{
    "title": {
        "en": "My Post Title",
        "vi": "Tiêu đề bài viết",
        "ko": "내 게시물 제목"
    },
    "slug": {
        "en": "my-post-title",
        "vi": "tieu-de-bai-viet",
        "ko": "nae-gesimul-jemog"
    },
    "content": {
        "en": "Post content in English...",
        "vi": "Nội dung bài viết tiếng Việt...",
        "ko": "한국어 게시물 내용..."
    },
    "category_id": 1,
    "status": "published"
}
```

### Step 5: Clear Configuration Cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

## How It Works

### PostRepository - Dynamic Language Support

The `PostRepository` now dynamically reads supported languages from config:

```php
protected function getSupportedLocales(): array
{
    return config('localization.supported', ['en', 'vi']);
}
```

### Search Functionality

Search automatically queries **all supported languages**:

```php
public function getWithFilters(array $filters = []): LengthAwarePaginator
{
    // ...
    if (isset($filters['search'])) {
        $supportedLocales = $this->getSupportedLocales();
        
        $query->where(function ($q) use ($filters, $supportedLocales) {
            foreach ($supportedLocales as $locale) {
                $q->orWhereRaw("JSON_EXTRACT(title, '$.{$locale}') LIKE ?", ['%' . $filters['search'] . '%'])
                  ->orWhereRaw("JSON_EXTRACT(content, '$.{$locale}') LIKE ?", ['%' . $filters['search'] . '%']);
            }
        });
    }
    // ...
}
```

**Example:** Search for "beauty" will match:
- English: "Beauty tips"
- Vietnamese: "Mẹo làm đẹp" (beauty tips)
- Korean: "미용 팁" (beauty tips)

### Slug Lookup

Find post by slug in **any supported language**:

```php
public function getBySlug(string $slug): ?Post
{
    $supportedLocales = $this->getSupportedLocales();
    
    return $this->query()
        ->where(function ($query) use ($slug, $supportedLocales) {
            foreach ($supportedLocales as $locale) {
                $query->orWhereRaw('JSON_EXTRACT(slug, "$.{$locale}") = ?', [$slug]);
            }
        })
        ->first();
}
```

**Example:**
- `/posts/beauty-tips` → Finds post (English slug)
- `/posts/meo-lam-dep` → Finds same post (Vietnamese slug)
- `/posts/미용-팁` → Finds same post (Korean slug)

---

## API Usage Examples

### Get Posts with Locale Header

```http
GET /api/posts
Accept-Language: ko

Response:
{
    "data": [
        {
            "id": 1,
            "title": "내 게시물 제목",  // Korean title shown
            "content": "한국어 게시물 내용...",
            "slug": "nae-gesimul-jemog",
            ...
        }
    ]
}
```

### Search in Multiple Languages

```http
GET /api/posts?search=beauty
Accept-Language: en

# Matches posts with "beauty" in:
# - English title/content
# - Vietnamese title/content
# - Korean title/content
# - All other configured languages
```

### Get Post by Slug (Any Language)

```http
# English slug
GET /api/posts/beauty-tips

# Vietnamese slug (same post)
GET /api/posts/meo-lam-dep

# Korean slug (same post)
GET /api/posts/미용-팁
```

---

## Benefits

### ✅ Easy to Extend
- Add new language: Just update `config/localization.php`
- No code changes needed
- No database migrations required

### ✅ Centralized Configuration
- Single source of truth for supported languages
- Easy to enable/disable languages
- Consistent across the application

### ✅ Automatic Propagation
- PostRepository automatically adapts
- Search includes all languages
- Slug lookup works for all languages

### ✅ Backward Compatible
- Existing English/Vietnamese content works unchanged
- Fallback to default values if translation missing

---

## Migration Guide for Existing Content

### Adding Translations to Existing Posts

If you have existing posts in only English/Vietnamese, you can add new language translations:

```php
// Update existing post
$post = Post::find(1);

$post->update([
    'title' => [
        'en' => $post->title['en'],      // Keep existing
        'vi' => $post->title['vi'],      // Keep existing
        'ko' => '새로운 한국어 제목',        // Add Korean
        'th' => 'ชื่อภาษาไทยใหม่',         // Add Thai
    ],
    // ... same for other fields
]);
```

### Bulk Translation Script

```php
// Generate translations for all posts
use App\Models\Post;
use Illuminate\Support\Facades\Http;

Post::chunk(100, function ($posts) {
    foreach ($posts as $post) {
        // Use translation API (Google Translate, DeepL, etc.)
        $koreanTitle = translateText($post->title['en'], 'ko');
        $thaiTitle = translateText($post->title['en'], 'th');
        
        $post->update([
            'title' => array_merge($post->title, [
                'ko' => $koreanTitle,
                'th' => $thaiTitle,
            ])
        ]);
    }
});
```

---

## Validation Rules

When creating/updating posts, ensure all required languages are provided:

```php
// In PostRequest.php
public function rules(): array
{
    $supportedLocales = config('localization.supported');
    $rules = [];
    
    foreach ($supportedLocales as $locale) {
        $rules["title.{$locale}"] = 'required|string|max:255';
        $rules["slug.{$locale}"] = 'required|string|max:255|unique:posts,slug->' . $locale;
        $rules["content.{$locale}"] = 'required|string';
        $rules["excerpt.{$locale}"] = 'nullable|string|max:500';
    }
    
    return $rules;
}
```

---

## Testing

### Test Search with New Language

```php
/** @test */
public function it_searches_posts_in_korean()
{
    $post = Post::factory()->create([
        'title' => [
            'en' => 'Beauty Tips',
            'vi' => 'Mẹo làm đẹp',
            'ko' => '미용 팁',
        ],
    ]);
    
    $response = $this->get('/api/posts?search=미용');
    
    $response->assertOk()
             ->assertJsonFragment(['id' => $post->id]);
}
```

### Test Slug Lookup with New Language

```php
/** @test */
public function it_finds_post_by_korean_slug()
{
    $post = Post::factory()->create([
        'slug' => [
            'en' => 'beauty-tips',
            'vi' => 'meo-lam-dep',
            'ko' => 'mi-yong-tip',
        ],
    ]);
    
    $response = $this->get('/api/posts/mi-yong-tip');
    
    $response->assertOk()
             ->assertJson(['id' => $post->id]);
}
```

---

## Language Codes Reference

Common ISO 639-1 language codes:

| Code | Language | Native Name |
|------|----------|-------------|
| `en` | English | English |
| `vi` | Vietnamese | Tiếng Việt |
| `ja` | Japanese | 日本語 |
| `zh` | Chinese | 中文 |
| `ko` | Korean | 한국어 |
| `th` | Thai | ไทย |
| `id` | Indonesian | Bahasa Indonesia |
| `ms` | Malay | Bahasa Melayu |
| `tl` | Filipino | Filipino |
| `km` | Khmer | ខ្មែរ |
| `lo` | Lao | ລາວ |
| `my` | Burmese | မြန်မာဘာသာ |

---

## Troubleshooting

### Issue: Search not working for new language

**Solution:** Clear config cache:
```bash
php artisan config:clear
```

### Issue: Validation fails for new language

**Solution:** Update validation rules to include new locale (see Validation Rules section above).

### Issue: Old posts don't have new language fields

**Solution:** This is expected. Use migration script to add translations, or allow nullable fields.

---

## Summary

✅ **Centralized**: All language config in one place
✅ **Scalable**: Add languages without code changes
✅ **Automatic**: Search and slug lookup adapt dynamically
✅ **Flexible**: JSON fields support unlimited languages
✅ **Maintainable**: Easy to enable/disable languages

To add a new language, simply:
1. Add language code to `config/localization.supported`
2. Clear cache
3. Start creating content in the new language! 🚀
