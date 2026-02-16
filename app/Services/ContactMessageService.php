<?php

namespace App\Services;

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;

class ContactMessageService
{
    public function create(array $data): ContactMessage
    {
        if (!array_key_exists('note', $data) && array_key_exists('msg', $data)) {
            $data['note'] = $data['msg'];
        }

        unset($data['msg']);
        $data['status'] = ContactMessageStatus::New->value;

        return ContactMessage::create($data);
    }
}
