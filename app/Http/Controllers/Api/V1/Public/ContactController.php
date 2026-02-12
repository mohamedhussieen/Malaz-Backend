<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Api\V1\Public\ContactStoreRequest;
use App\Services\ContactMessageService;

class ContactController extends BaseApiController
{
    public function __construct(private readonly ContactMessageService $contactMessageService)
    {
    }

    public function store(ContactStoreRequest $request)
    {
        $contact = $this->contactMessageService->create($request->validated());

        return $this->successResponse(
            ['id' => $contact->id],
            'تم إرسال رسالتك بنجاح',
            'Your message has been sent',
            201
        );
    }
}
