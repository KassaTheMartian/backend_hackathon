# 📋 Danh sách Seeder cho API Testing

## ✅ Các Seeder hiện có

### 1. **DatabaseSeeder.php** - Main Seeder
- **Vị trí**: `database/seeders/DatabaseSeeder.php`
- **Chức năng**: Chạy tất cả các seeder theo thứ tự
- **Dữ liệu tạo**:
  - ServiceCategory (categories)
  - Service (services)  
  - Booking (bookings)
  - Demo (20 records)

### 2. **ServiceCategorySeeder.php**
- **Vị trí**: `database/seeders/ServiceCategorySeeder.php`
- **Dữ liệu tạo**: 4 danh mục dịch vụ
  - Chăm sóc da mặt / Facial Care
  - Điều trị mụn / Acne Treatment
  - Làm trắng da / Skin Whitening
  - Chống lão hóa / Anti-aging
- **Multilingual**: vi, en, ja, zh

### 3. **ServiceSeeder.php**
- **Vị trí**: `database/seeders/ServiceSeeder.php`
- **Dữ liệu tạo**: 4 dịch vụ mẫu
  - Điều trị mụn chuyên sâu (500,000 VND - 450,000 VND)
  - Chăm sóc da cơ bản (300,000 VND)
  - Làm trắng da bằng laser (800,000 VND - 720,000 VND)
  - Điều trị chống lão hóa (1,200,000 VND)
- **Multilingual**: vi, en, ja, zh

### 4. **BookingSeeder.php**
- **Vị trí**: `database/seeders/BookingSeeder.php`
- **Dữ liệu tạo**:
  - 10 Users
  - 3 Branches
  - 5 Staffs
  - 50 Bookings (tổng cộng)
    - 10 pending
    - 15 confirmed
    - 20 completed
    - 5 cancelled

## 🏭 Các Factory hiện có

### 1. **BranchFactory.php**
- **Chức năng**: Tạo dữ liệu chi nhánh với fake data
- **Dữ liệu**: Name, Address, Phone, Email, Location, Opening hours, Images, Amenities
- **States**: `inactive()`

### 2. **ServiceFactory.php**
- **Chức năng**: Tạo dịch vụ ngẫu nhiên
- **Dữ liệu**: Price (200k-2M), Duration (30-180min), Images, Gallery, Meta data
- **States**: `featured()`, `inactive()`

### 3. **BookingFactory.php**
- **Chức năng**: Tạo đặt lịch với trạng thái khác nhau
- **Dữ liệu**: Booking code, Dates, Prices, Payment info
- **States**: `pending()`, `confirmed()`, `completed()`, `cancelled()`

### 4. **StaffFactory.php**
- **Chức năng**: Tạo nhân viên với thông tin chi tiết
- **Dữ liệu**: Position, Specialization, Bio, Rating, Experience, Certifications
- **States**: `senior()`, `inactive()`

### 5. **UserFactory.php**
- **Chức năng**: Tạo user mẫu
- **Dữ liệu**: Name, Email, Password (mặc định: 'password')
- **States**: `unverified()`

### 6. **DemoFactory.php**
- **Chức năng**: Tạo dữ liệu demo
- **Dữ liệu**: Title, Description, User ID

## ✅ Các Seeder mới được tạo

### 5. **UserSeeder.php** - ✨ Đã tạo
- **Vị trí**: `database/seeders/UserSeeder.php`
- **Dữ liệu tạo**:
  - 1 Admin User (admin@example.com / password: password)
  - 1 Test User (test@example.com / password: password)
  - 2 Manager Users (manager1@example.com, manager2@example.com)
  - 10 Regular Users (from factory)

### 6. **BranchSeeder.php** - ✨ Đã tạo
- **Vị trí**: `database/seeders/BranchSeeder.php`
- **Dữ liệu tạo**:
  - 3 chi nhánh cụ thể tại:
    - Quận 1 (TP.HCM)
    - Quận 2 - Thảo Điền (TP.HCM)
    - Quận 7 (TP.HCM)
  - 2 chi nhánh từ factory

### 7. **StaffSeeder.php** - ✨ Đã tạo
- **Vị trí**: `database/seeders/StaffSeeder.php`
- **Dữ liệu tạo**:
  - 5 staff cụ thể (Senior Therapist, Junior Therapist, Manager, Receptionist)
  - 5 staff từ factory

## 🚀 Cách chạy Seeder

### Chạy tất cả seeders:
```bash
php artisan db:seed
# hoặc
php artisan migrate:fresh --seed
```

### Chạy từng seeder riêng lẻ:
```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ServiceCategorySeeder
php artisan db:seed --class=ServiceSeeder
php artisan db:seed --class=BranchSeeder
php artisan db:seed --class=StaffSeeder
php artisan db:seed --class=BookingSeeder
```

### Thứ tự chạy seeders (tự động trong DatabaseSeeder):
1. UserSeeder - Users và Admin
2. ServiceCategorySeeder - Danh mục dịch vụ
3. ServiceSeeder - Dịch vụ
4. BranchSeeder - Chi nhánh
5. StaffSeeder - Nhân viên
6. BookingSeeder - Đặt lịch

## 📊 Dữ liệu test API

Sau khi chạy seeder, bạn sẽ có:
- ✅ **Users**: 14 users (1 admin, 1 test user, 2 managers, 10 regular users)
- ✅ **Categories**: 4 service categories
- ✅ **Services**: 4 services cụ thể
- ✅ **Branches**: 5 branches (3 cụ thể + 2 từ factory)
- ✅ **Staff**: 10 staff members (5 cụ thể + 5 từ factory)
- ✅ **Bookings**: 50 bookings với nhiều trạng thái (pending, confirmed, completed, cancelled)
- ✅ **Demo**: 20 demo records

## 💡 Thông tin đăng nhập test

### Admin Users:
- **Email**: admin@example.com
- **Password**: password
- **Role**: Admin

### Manager Users:
- **Email**: manager1@example.com / manager2@example.com
- **Password**: password
- **Role**: Admin

### Test User:
- **Email**: test@example.com
- **Password**: password
- **Role**: User

## 📝 Ghi chú

- **Password mặc định**: Tất cả users được tạo với password = `'password'`
- **Factory syntax**: Có thể dùng factory trong seeder như `Branch::factory()->count(5)->create()`
- **Multilingual**: Service và ServiceCategory hỗ trợ nhiều ngôn ngữ (vi, en, ja, zh)

