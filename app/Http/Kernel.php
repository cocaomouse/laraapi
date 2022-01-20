<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        // 修正代理服务器后的服务器参数
        \App\Http\Middleware\TrustProxies::class,
        // 解决 cors 跨域问题
        \Fruitcake\Cors\HandleCors::class,
        // 检测应用是否进入『维护模式』
        // 见：https://learnku.com/docs/laravel/8.x/configuration#maintenance-mode
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        // 检测表单请求的数据是否过大
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        // 对所有提交的请求数据进行 PHP 函数 `trim()` 处理
        \App\Http\Middleware\TrimStrings::class,
        // 将提交请求参数中空子串转换为 null
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,

            // 处理路由绑定
            // 见：https://learnku.com/docs/laravel/8.x/routing#route-model-binding
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            //\App\Http\Middleware\EnsureEmailIsVerified::class,  //未认证用户跳转到邮件认证提醒页面中间件

            // 记录用户最后活跃时间
            \App\Http\Middleware\RecordLastActivedTime::class
        ],

        // API 中间件组，应用于 routes/api.php 路由文件，
        // 在 RouteServiceProvider 中设定
        'api' => [
            \App\Http\Middleware\AcceptHeader::class,

            // 使用别名来调用中间件
            // 请见：https://learnku.com/docs/laravel/8.x/middleware#为路由分配中间件
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     *
     * 中间件别名设置，允许你使用别名调用中间件，例如上面的 api 中间件组调用
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        // 接口语言设置
        'change-locale' => \App\Http\Middleware\ChangeLocale::class,
    ];
}
