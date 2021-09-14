<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\Api\UserRequest;
use Illuminate\Auth\AuthenticationException;

class UsersController extends Controller
{
    public function store(UserRequest $request)
    {
        $verificationData = \Cache::get($request->verification_key);
        if (!$verificationData) {
            abort(403,'验证码已失效');
        }

        if (!hash_equals($request->verification_code,$verificationData['code'])) {
            //\Cache::forget($request->verification_key);
            throw new AuthenticationException('验证错误');
        }

        $user = User::create([
            'name' => $request->name,
            'password' => $request->password,
            'phone' => $verificationData['phone'],
        ]);

        // 清除验证码缓存
        \Cache::forget($request->verification_key);

        return new UserResource($user);
    }
}
