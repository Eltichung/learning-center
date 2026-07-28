<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Trả về JSON cho request AJAX, hoặc redirect HTML như trước.
     *  - $redirect: URL điều hướng sau khi thành công (truyền null = back).
     */
    protected function respondOk(Request $request, string $message, ?string $redirect = null): mixed
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => $message,
                'redirect' => $redirect,
            ]);
        }
        if ($redirect) {
            return redirect()->to($redirect)->with('ok', $message);
        }
        return back()->with('ok', $message);
    }

    /**
     * Trả lỗi guard (không phải validate):
     *  - AJAX → JSON 422 với errors[$field] = [$message]
     *  - HTML → back()->withErrors([...])
     */
    protected function respondError(Request $request, string $field, string $message, ?string $redirect = null): mixed
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'errors' => [$field => [$message]],
            ], 422);
        }
        $back = $redirect ? redirect()->to($redirect) : back();
        return $back->withErrors([$field => $message]);
    }
}
