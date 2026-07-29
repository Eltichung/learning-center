<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chặn các hành động ghi (tạo/sửa/xoá) khi subscription của GV đã hết hạn.
 * - Super admin bỏ qua kiểm tra.
 * - GET request luôn cho phép (đọc dữ liệu vẫn OK).
 */
class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Không đăng nhập → để middleware auth xử lý
        if (! $user) {
            return $next($request);
        }

        // Super admin bỏ qua
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Cho phép mọi GET (xem dữ liệu vẫn OK khi hết hạn)
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        if ($user->subscriptionActive()) {
            return $next($request);
        }

        $msg = 'Gói đã hết hạn. Nâng cấp để tiếp tục thao tác.';
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => $msg,
                'redirect' => route('billing.index'),
            ], 402);
        }

        return redirect()->route('billing.index')->withErrors(['plan' => $msg]);
    }
}
