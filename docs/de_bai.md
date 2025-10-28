
---

# Yêu Cầu, Phạm Vi Công Việc Và Chức Năng Hệ Thống

## I. Yêu Cầu Đề Bài Và Phạm Vi Công Việc

### 1. Đề Bài
Áp dụng công nghệ AI để phát triển một ứng dụng web sử dụng **ReactJS** và **NodeJS** cho website **Phòng Khám Chăm Sóc Sắc Đẹp**.

### 2. Phạm Vi Công Việc (Scope of Work)

#### Gợi ý Tech Stack (có thể sử dụng server local):
- **Frontend**: React.js (hoặc SSR), TailwindCSS hoặc công nghệ tương tự
- **Backend**: Node.js với Express hoặc công nghệ tương tự laravel
- **Database**: MySQL cho dữ liệu quan hệ, Redis cho caching hoặc giải pháp khác
- **Payment**: Stripe API hoặc công nghệ thanh toán khác
- **Chatbot**: Open AI

#### Sử dụng AI hỗ trợ tạo ra sản phẩm:
- Các đội được phép sử dụng bất kỳ công cụ AI nào để hỗ trợ quá trình phát triển sản phẩm.

### 3. Deliverables

#### Vòng Loại:
- **Source code dự án**
- **Tài liệu thiết kế** (ưu tiên file Markdown, đặt trong thư mục `docs`):
  - Kiến trúc tổng thể (High-level architecture)
  - Cơ sở dữ liệu (Database)
  - API
  - UI/UX
  - Testing
  - Triển khai (Deployment)
- **File `setup.md`**:
  - Hướng dẫn thiết lập để chạy project trên local
  - Bao gồm setup data, key, v.v.
  - Đảm bảo Ban tổ chức có thể chạy project trên local

#### Vòng Chung Kết:
- **Slide thuyết trình**:
  - Nội dung thuyết trình cần nêu rõ cách đội sử dụng AI để giải quyết bài toán
  - Đưa ra các chỉ số đo đạc khi sử dụng AI

#### Cách thức nộp bài:
- **Đội trưởng gửi email** tới: `datnv13@fpt.com`
  - **CC**: Thành viên BGK (`duonghv@fpt.com`, `hieutt39@fpt.com`) và 3 thành viên trong nhóm
  - **Subject**: `EES_AI_Hackathon [Tên đội] [Tên vòng]`
    - **Tên đội**: Tên đội đã đăng ký với BTC (nếu chưa có tên, gửi tên trong subject)
    - **Tên vòng**: Vòng loại hoặc Vòng chung kết

#### Deadline:
- **Vòng loại**: 23:59, ngày **31/10/2025**
- **Vòng chung kết**: 23:59, ngày **04/11/2025**

### 4. Tiêu Chí Chấm Điểm
> **Lưu ý**: Đội nộp bài muộn sẽ bị **trừ 10% số điểm**.

#### 4.1 Vòng Loại
| **Tiêu chí**               | **Trọng số (%)** | **Giải thích**                                                                 |
|----------------------------|------------------|--------------------------------------------------------------------------------|
| Hoàn thành các chức năng   | 50               | Mỗi chức năng trong 10 chức năng = 5 điểm. Đánh giá mức độ hoàn thành chức năng |
| Chất lượng code            | 20               | Tuân thủ coding convention, best practice theo ngôn ngữ sử dụng                |
| Chất lượng tài liệu        | 30               | Tài liệu mô tả đầy đủ, rõ ràng, chính xác giải pháp                            |

> **Lưu ý**: BTC sẽ ưu tiên sử dụng AI để chấm thi do số lượng bài nhiều và thời gian ngắn.

#### 4.2 Vòng Chung Kết (Dự kiến)
| **Tiêu chí**                           | **Trọng số (%)** | **Giải thích**                                                                 |
|----------------------------------------|------------------|--------------------------------------------------------------------------------|
| Hiểu đúng vấn đề & xác định hướng AI   | 30               | Nắm rõ bài toán, xác định pain point, chọn AI approach hợp lý, có căn cứ       |
| Giải pháp sáng tạo & khả thi           | 30               | Đề xuất giải pháp chính xác, tạo giá trị rõ ràng, có thể triển khai thực tế    |
| Trình bày thuyết phục & logic          | 20               | Slide mạch lạc, storytelling rõ ràng, lập luận logic, thể hiện hiểu biết AI    |
| Teamwork & phản biện                   | 20               | Phối hợp ăn ý, phân vai rõ, trả lời phản biện tự tin, nắm vững nội dung        |

---

## II. Yêu Cầu Chức Năng: Website Phòng Khám Chăm Sóc Sắc Đẹp

### 1. Giới Thiệu
Website **Phòng Khám Chăm Sóc Sắc Đẹp** là nền tảng trực tuyến hỗ trợ khách hàng:
- Tìm hiểu thông tin về dịch vụ và chi nhánh
- Dễ dàng đặt lịch hẹn
- Giao diện hiện đại, thân thiện, hỗ trợ đa ngôn ngữ và tương thích thiết bị di động

### 2. Chức Năng Chi Tiết

#### 2.1 Trang Chủ (Homepage)
- Banner lớn giới thiệu thương hiệu
- Gian dịch vụ nổi bật
- Hình ảnh phòng khám và cơ sở vật chất
- Nút đặt lịch nhanh

#### 2.2 Menu Dịch Vụ (Services Menu)
- Hiển thị dạng lưới với hình ảnh và mô tả
- Phân loại dịch vụ rõ ràng
- Trang chi tiết riêng cho từng dịch vụ

#### 2.3 Đặt Lịch Khám (Booking System)
- **Form đặt lịch**:
  - Chọn dịch vụ, chi nhánh, ngày giờ, thông tin liên hệ
- Xác nhận qua email hoặc SMS
- Hỗ trợ 2 loại người dùng:
  - **Khách vãng lai**: Không cần tài khoản, chỉ điền thông tin liên hệ
  - **Thành viên**: Đăng nhập để đặt lịch, xem lịch sử, nhận ưu đãi

#### 2.4 Đăng Ký & Đăng Nhập Thành Viên
- **Form đăng ký**: Tên, email, mật khẩu, xác thực OTP
- **Trang đăng nhập**
- **Trang quản lý cá nhân**:
  - Lịch sử đặt lịch
  - Hồ sơ cá nhân
  - Ưu đãi

#### 2.5 Giới Thiệu Chi Nhánh
- **Danh sách chi nhánh**: Tên, địa chỉ, hình ảnh
- **Trang chi tiết**:
  - Bản đồ
  - Giờ làm việc
  - Số điện thoại

#### 2.6 Liên Hệ (Contact Page)
- **Form liên hệ**: Tên, email, nội dung
- **Thông tin liên hệ**: Số điện thoại, email, địa chỉ
- Tích hợp **Google Maps**

#### 2.7 Đa Ngôn Ngữ (Multilingual Support)
- Nút chuyển đổi ngôn ngữ (ví dụ: 🇻🇳 🇯🇵 🇬🇧 🇨🇳)
- Nội dung được dịch đầy đủ
- Tùy chọn tự động chuyển ngôn ngữ theo trình duyệt

#### 2.8 Blog / Tin Tức
- **Danh sách bài viết**: Tiêu đề, hình ảnh, mô tả ngắn
- **Trang chi tiết bài viết**
- **Phân loại bài viết**: Chăm sóc da, công nghệ mới, mẹo sức khỏe

#### 2.9 Đánh Giá & Phản Hồi
- **Form gửi đánh giá**: Tên, nội dung, điểm số
- Hiển thị đánh giá công khai (tùy chọn)
- Quản trị viên có thể duyệt hoặc phản hồi đánh giá

#### 2.10 Hỗ Trợ Trực Tuyến
- **Live chat** với nhân viên
- **Chatbot** tự động trả lời câu hỏi thường gặp
- Tích hợp hệ thống booking

### 3. Hỗ Trợ Giao Diện Mobile
- Giao diện **responsive**, tương thích với điện thoại và máy tính bảng
- Menu điều hướng đơn giản, dễ sử dụng trên thiết bị nhỏ
- Tối ưu tốc độ tải trang và trải nghiệm người dùng di động

---
