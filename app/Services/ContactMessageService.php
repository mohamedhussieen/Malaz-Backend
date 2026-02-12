<?php

namespace App\Services;

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;

class ContactMessageService
{
    public function create(array $data): ContactMessage
    {
        $data['status'] = ContactMessageStatus::New->value;

        return ContactMessage::create($data);
    }
}
