<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Str;

class CaptchasController extends Controller
{
    public function store(CaptchaRequest $request,CaptchaBuilder $captchaBuilder)
    {
        $key = 'captcha_'.Str::random(15);
        $captchaCode = $captchaBuilder->build();
        $expiredAt = now()->addMinute(3);
        $phone = $request->phone;

        \Cache::put($key,['phone' => $phone,'code' => $captchaCode->getPhrase()],$expiredAt);

        return response()->json([
            'captcha_key' => $key,
            'captcha_image_content' => $captchaCode->inline(),
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(201);
    }
}
