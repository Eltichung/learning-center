<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tin tưởng proxy của Render (HTTPS terminate ở proxy) để Laravel nhận đúng scheme
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route('teacher.login'));
        $middleware->redirectUsersTo(fn () => auth()->user()?->role === 'super_admin'
            ? route('admin.dashboard') : route('teacher.dashboard'));

        // Alias middleware kiểm tra subscription còn hạn (áp cho route ghi của GV)
        $middleware->alias([
            'active.sub' => \App\Http\Middleware\EnsureActiveSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render JSON cho mọi request AJAX (wantsJson hoặc X-Requested-With),
        // không chỉ riêng /api — để validation/csrf/auth errors trả 422/401/419 JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
