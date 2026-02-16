<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Api\V1\Admin\ContactMessageStatusRequest;
use App\Http\Resources\V1\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends BaseApiController
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = min($perPage, 50);
        $search = trim((string) $request->get('search', ''));
        $type = strtolower((string) $request->get('type', ''));

        $query = ContactMessage::query()
            ->select(['id', 'name', 'email', 'phone', 'whatsapp', 'note', 'status', 'created_at'])
            ->orderByDesc('created_at');

        if ($search !== '') {
            if ($type === 'phone') {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('phone', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%");
                });
            } elseif ($type === 'name') {
                $query->where('name', 'like', "%{$search}%");
            } elseif ($type === 'email') {
                $query->where('email', 'like', "%{$search}%");
            } elseif ($type === 'msg') {
                $query->where('note', 'like', "%{$search}%");
            } else {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('phone', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%");
                });
            }
        }

        $paginator = $query->paginate($perPage);

        $items = ContactMessageResource::collection($paginator->getCollection())->resolve();

        return $this->successResponse(
            $this->paginationPayload($paginator, $items),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function show(ContactMessage $contactMessage)
    {
        return $this->successResponse(
            (new ContactMessageResource($contactMessage))->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function updateStatus(ContactMessageStatusRequest $request, ContactMessage $contactMessage)
    {
        $contactMessage->update(['status' => $request->status]);

        return $this->successResponse(
            (new ContactMessageResource($contactMessage))->resolve(),
            'تم تحديث البيانات بنجاح',
            'Updated successfully'
        );
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return $this->successResponse(
            null,
            'تم حذف العنصر بنجاح',
            'Deleted successfully'
        );
    }
}
