<?php

namespace App\Http\Controllers\Api;

use App\Handlers\ImageUploadHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\Api\UserRequest;
use Illuminate\Auth\AuthenticationException;
use App\Models\Image;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function store(UserRequest $request)
    {
        $verificationData = \Cache::get($request->verification_key);
        if (!$verificationData) {
            abort(403, '验证码已失效');
        }

        if (!hash_equals($request->verification_code, $verificationData['code'])) {
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

        return (new UserResource($user))->showSensitiveFields();
    }

    public function show(User $user, Request $request)
    {
        return new UserResource($user);
    }

    public function me(Request $request)
    {
        return (new UserResource($request->user()))->showSensitiveFields();
    }

    public function update(UserRequest $request, ImageUploadHandler $uploader)
    {
        $user = $request->user();

        $attributes = $request->only(['name', 'email', 'introduction']);

        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }

        $user->update($attributes);

        return (new UserResource($user))->showSensitiveFields();
    }
}
