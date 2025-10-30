# Hướng Dẫn Thêm Ngôn Ngữ Mới Cho Chatbot

## Tổng Quan

Hệ thống chatbot được thiết kế để hỗ trợ đa ngôn ngữ một cách dễ dàng. Hiện tại hỗ trợ:
- ✅ Tiếng Việt (vi)
- ✅ Tiếng Anh (en)
- 🔧 Dễ dàng mở rộng cho các ngôn ngữ khác

## Cấu Trúc Đa Ngôn Ngữ

### 1. File Ngôn Ngữ
Tất cả các văn bản được lưu trong: `resources/lang/{locale}/chatbot.php`

### 2. Dữ Liệu Database
Các model hỗ trợ JSON đa ngôn ngữ:
- `Branch`: name, address, description
- `Service`: name, description
- `ServiceCategory`: name

## Cách Thêm Ngôn Ngữ Mới

### Bước 1: Tạo File Ngôn Ngữ

```bash
# Ví dụ: Thêm tiếng Nhật (ja)
cp resources/lang/ja/chatbot.php.example resources/lang/ja/chatbot.php
```

### Bước 2: Dịch Nội Dung

Mở file `resources/lang/ja/chatbot.php` và dịch tất cả các giá trị:

```php
<?php

return [
    // ... các message khác

    // Tên ngày trong tuần
    'days' => [
        'monday' => '月曜日',
        'tuesday' => '火曜日',
        'wednesday' => '水曜日',
        'thursday' => '木曜日',
        'friday' => '金曜日',
        'saturday' => '土曜日',
        'sunday' => '日曜日',
    ],

    // Nhãn context
    'context' => [
        'business_info' => '企業情報',
        'business_name' => '企業名',
        'description' => '説明',
        'specialties' => '専門',
        'phone' => '電話',
        'branches' => '支店',
        'address' => '住所',
        'working_hours' => '営業時間',
        'services' => 'サービス',
        'price' => '価格',
        'duration' => '所要時間',
        'minutes' => '分',
        'category' => 'カテゴリー',
    ],
];
```

### Bước 3: Thêm Vào Config Chatbot

Mở file `config/chatbot.php` và thêm ngôn ngữ mới:

```php
'business' => [
    'name' => [
        'vi' => 'Phòng Khám Thẩm Mỹ ABC',
        'en' => 'ABC Beauty Clinic',
        'ja' => 'ABC美容クリニック', // ← Thêm vào đây
    ],
    'description' => [
        'vi' => '...',
        'en' => '...',
        'ja' => '...', // ← Thêm vào đây
    ],
    // ... tương tự cho các trường khác
],

'system_instructions' => [
    'vi' => '...',
    'en' => '...',
    'ja' => '...', // ← Thêm system instruction tiếng Nhật
],
```

### Bước 4: Cập Nhật Database

Thêm bản dịch vào dữ liệu database cho các model:

```php
// Migration hoặc Seeder
Branch::create([
    'name' => [
        'vi' => 'Chi Nhánh Quận 1',
        'en' => 'District 1 Branch',
        'ja' => '1区支店',
    ],
    'address' => [
        'vi' => '123 Nguyễn Huệ, Q1, TP.HCM',
        'en' => '123 Nguyen Hue, Dist 1, HCMC',
        'ja' => 'ホーチミン市1区グエンフエ通り123番地',
    ],
    // ...
]);
```

### Bước 5: Test

```bash
# Gọi API với header Accept-Language
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -H "Accept-Language: ja" \
  -d '{"message":"こんにちは"}'
```

## Ngôn Ngữ Được Đề Xuất Thêm

### Châu Á
- 🇯🇵 **ja** - Tiếng Nhật (Japanese)
- 🇰🇷 **ko** - Tiếng Hàn (Korean)
- 🇨🇳 **zh** - Tiếng Trung (Chinese Simplified)
- 🇹🇼 **zh-TW** - Tiếng Trung Phồn Thể (Chinese Traditional)
- 🇹🇭 **th** - Tiếng Thái (Thai)
- 🇮🇩 **id** - Tiếng Indonesia (Indonesian)

### Châu Âu
- 🇫🇷 **fr** - Tiếng Pháp (French)
- 🇩🇪 **de** - Tiếng Đức (German)
- 🇪🇸 **es** - Tiếng Tây Ban Nha (Spanish)
- 🇮🇹 **it** - Tiếng Ý (Italian)
- 🇷🇺 **ru** - Tiếng Nga (Russian)

## Checklist Khi Thêm Ngôn Ngữ Mới

- [ ] Tạo file `resources/lang/{locale}/chatbot.php`
- [ ] Dịch tất cả keys trong `days` array
- [ ] Dịch tất cả keys trong `context` array
- [ ] Dịch tất cả message keys
- [ ] Thêm vào `config/chatbot.php`:
  - [ ] business.name.{locale}
  - [ ] business.description.{locale}
  - [ ] business.specialties.{locale}
  - [ ] system_instructions.{locale}
- [ ] Update database seeds với bản dịch mới
- [ ] Test API với Accept-Language header
- [ ] Kiểm tra Gemini AI có hiểu ngôn ngữ đó không

## Cấu Trúc Code Hỗ Trợ Đa Ngôn Ngữ

### ChatbotService.php

```php
// ✅ ĐÚNG: Sử dụng translation helper
$label = __('chatbot.context.business_name', [], $locale);

// ❌ SAI: Hard-code ngôn ngữ
$label = $locale === 'vi' ? 'Tên doanh nghiệp' : 'Business name';
```

### Lấy Locale từ Request

```php
// Trong Controller
$locale = app()->getLocale(); // Từ Accept-Language header

// Hoặc explicit
$locale = $request->header('Accept-Language', 'vi');
```

## Fallback Language

Hệ thống sử dụng fallback hierarchy:
1. Ngôn ngữ được yêu cầu (ví dụ: `ja`)
2. Tiếng Việt (mặc định): `vi`

```php
// Trong code
$value = $data[$locale] ?? $data['vi'] ?? 'N/A';
```

## Best Practices

1. **Luôn dịch đầy đủ** - Không bỏ sót keys nào
2. **Kiểm tra encoding** - Đảm bảo UTF-8
3. **Test với AI** - Gemini có thể không hiểu một số ngôn ngữ hiếm
4. **Nhất quán** - Dùng cùng style dịch trong toàn bộ hệ thống
5. **Document** - Ghi chú nếu có từ khó dịch

## Ví Dụ Hoàn Chỉnh

Xem file template: `resources/lang/ja/chatbot.php.example`

## Troubleshooting

### Lỗi: Translation key not found
- Kiểm tra file `resources/lang/{locale}/chatbot.php` có tồn tại không
- Đảm bảo tất cả keys đã được định nghĩa
- Chạy `php artisan config:clear`

### Lỗi: Hiển thị sai ký tự
- Kiểm tra file encoding là UTF-8
- Kiểm tra database collation: `utf8mb4_unicode_ci`

### AI không hiểu ngôn ngữ
- Cập nhật `system_instructions.{locale}` rõ ràng hơn
- Gemini hỗ trợ tốt các ngôn ngữ phổ biến

## Liên Hệ

Nếu cần hỗ trợ thêm ngôn ngữ mới, vui lòng tạo issue hoặc liên hệ team development.
