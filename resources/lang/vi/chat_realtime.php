<?php

return [
    // Session management
    'session_created' => 'Tạo phiên chat thành công',
    'no_history' => 'Không tìm thấy lịch sử chat',
    'history_retrieved' => 'Lấy lịch sử chat thành công',
    'sessions_retrieved' => 'Lấy danh sách phiên chat thành công',
    
    // Messages
    'message_sent' => 'Gửi tin nhắn thành công',
    'messages_retrieved' => 'Lấy tin nhắn thành công',
    'message_required' => 'Tin nhắn là bắt buộc',
    'message_string' => 'Tin nhắn phải là chuỗi ký tự',
    'message_max' => 'Tin nhắn không được quá 1000 ký tự',
    
    // Staff management
    'no_staff_available' => 'Hiện tại không có nhân viên nào sẵn sàng',
    'staff_joined' => ':name đã tham gia cuộc trò chuyện',
    'transferred_to_staff' => 'Đã chuyển sang nhân viên hỗ trợ',
    'transfer_success' => 'Chuyển sang hỗ trợ nhân viên thành công',
    'not_assigned' => 'Bạn không được phân công cho phiên chat này',
    
    // Validation messages
    'session_id_required' => 'Session ID là bắt buộc',
    'session_id_string' => 'Session ID phải là chuỗi ký tự',
    'session_id_max' => 'Session ID không được quá 100 ký tự',
    'guest_name_string' => 'Tên khách phải là chuỗi ký tự',
    'guest_name_max' => 'Tên khách không được quá 255 ký tự',
    'guest_email_email' => 'Email khách phải là địa chỉ email hợp lệ',
    'guest_email_max' => 'Email khách không được quá 255 ký tự',
    'guest_phone_string' => 'Số điện thoại khách phải là chuỗi ký tự',
    'guest_phone_max' => 'Số điện thoại khách không được quá 20 ký tự',
    'status_string' => 'Trạng thái phải là chuỗi ký tự',
    'status_in' => 'Trạng thái phải là một trong: active, closed, transferred',
    'assigned_to_integer' => 'Người được phân công phải là số nguyên',
    'assigned_to_exists' => 'Nhân viên được chọn không tồn tại',
    'metadata_array' => 'Metadata phải là mảng',
];
