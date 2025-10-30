<?php

return [
    // Session management
    'session_created' => 'Chat session created successfully',
    'no_history' => 'No chat history found',
    'history_retrieved' => 'Chat history retrieved successfully',
    'sessions_retrieved' => 'Chat sessions retrieved successfully',
    
    // Messages
    'message_sent' => 'Message sent successfully',
    'messages_retrieved' => 'Messages retrieved successfully',
    'message_required' => 'Message is required',
    'message_string' => 'Message must be a string',
    'message_max' => 'Message may not be greater than 1000 characters',
    
    // Staff management
    'no_staff_available' => 'No staff available at the moment',
    'staff_joined' => ':name has joined the conversation',
    'transferred_to_staff' => 'Transferred to staff member',
    'transfer_success' => 'Successfully transferred to human support',
    'not_assigned' => 'You are not assigned to this chat session',
    
    // Validation messages
    'session_id_required' => 'Session ID is required',
    'session_id_string' => 'Session ID must be a string',
    'session_id_max' => 'Session ID may not be greater than 100 characters',
    'guest_name_string' => 'Guest name must be a string',
    'guest_name_max' => 'Guest name may not be greater than 255 characters',
    'guest_email_email' => 'Guest email must be a valid email address',
    'guest_email_max' => 'Guest email may not be greater than 255 characters',
    'guest_phone_string' => 'Guest phone must be a string',
    'guest_phone_max' => 'Guest phone may not be greater than 20 characters',
    'status_string' => 'Status must be a string',
    'status_in' => 'Status must be one of: active, closed, transferred',
    'assigned_to_integer' => 'Assigned to must be an integer',
    'assigned_to_exists' => 'Selected staff member does not exist',
    'metadata_array' => 'Metadata must be an array',
];
