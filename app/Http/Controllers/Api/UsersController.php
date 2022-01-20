<?php

namespace App\Http\Controllers\Api;

use App\Handlers\ImageUploadHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
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
        $attributes = $request->only(['name', 'email', 'introduction','registration_id']);

        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }

        $user->update($attributes);

        return (new UserResource($user))->showSensitiveFields();
    }

    public function activedIndex(User $user)
    {
        /*UserResource::wrap('data');
        return UserResource::collection($user->getActiveUsers());*/
        $usersInfo = $user->getActiveUsers();

        if (!$usersInfo) {
            return response()->json([
                'message' => '无'
            ]);
        }
        foreach ($usersInfo as $key => $val) {
            $activedUser[$key]['id'] = $val->id;
            $activedUser[$key]['name'] = $val->name;
            $activedUser[$key]['phone'] = $val->phone ? true : false;
            $activedUser[$key]['email'] = $val->email ? true : false;
            $activedUser[$key]['avatar'] = $val->avatar;
            $activedUser[$key]['wechat'] = ($val->weixin_unionid || $val->weixin_openid) ? true : false;
        }
        return array_merge($activedUser);
    }
}
