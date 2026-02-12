<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Api\V1\Admin\AdminLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function login(AdminLoginRequest $request)
    {
        $user = User::query()->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                ['email' => ['Invalid credentials']],
                'بيانات الدخول غير صحيحة',
                'Invalid credentials',
                422
            );
        }

        $token = $user->createToken('admin')->plainTextToken;

        return $this->successResponse(
            [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
            'تم تسجيل الدخول بنجاح',
            'Logged in successfully'
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->successResponse(
            null,
            'تم تسجيل الخروج بنجاح',
            'Logged out successfully'
        );
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return $this->successResponse(
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}
