<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanOrder;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $plans = Plan::active()->public()->orderBy('price')->get();
        $current = $user->currentPlan();
        $sub = $user->subscription;
        $usage = $user->usage();
        $orders = $user->planOrders()->with('plan')->latest('id')->limit(10)->get();

        return view('billing.index', compact('plans', 'current', 'sub', 'usage', 'orders'));
    }

    public function createOrder(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'plan' => ['required', 'string'],
            'months' => ['nullable', 'integer', 'in:1,3,6,12'],
        ]);
        $plan = Plan::where('slug', $data['plan'])->active()->public()->first();
        if (! $plan || $plan->slug === 'trial') {
            return $this->respondError($request, 'plan', 'Gói không hợp lệ.');
        }

        $months = (int) ($data['months'] ?? 1);
        $amount = (int) $plan->price * $months;
        $code = 'HC-'.strtoupper($plan->slug).'-'.$user->id.'-'.time();

        // Nếu có đơn pending khác → huỷ để tránh loạn
        PlanOrder::where('user_id', $user->id)->where('status', 'pending')
            ->update(['status' => 'cancelled', 'note' => 'Tự huỷ khi tạo đơn mới']);

        $order = PlanOrder::create([
            'code' => $code,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $amount,
            'months' => $months,
            'status' => 'pending',
        ]);

        return response()->json([
            'ok' => true,
            'order' => [
                'code' => $order->code,
                'plan_name' => $plan->name,
                'amount' => $amount,
                'months' => $months,
            ],
            'qr' => $this->buildQrPayload($amount, $code),
        ]);
    }

    public function notifyPaid(Request $request, string $code)
    {
        $user = $request->user();
        $order = PlanOrder::where('code', $code)->where('user_id', $user->id)->firstOrFail();
        if ($order->status !== 'pending') {
            return $this->respondError($request, 'order', 'Đơn không ở trạng thái chờ.');
        }
        $order->update(['notified_at' => now()]);

        return $this->respondOk($request, 'Đã ghi nhận. Admin sẽ duyệt trong 24h.', route('billing.index'));
    }

    public function cancelOrder(Request $request, string $code)
    {
        $user = $request->user();
        $order = PlanOrder::where('code', $code)->where('user_id', $user->id)->firstOrFail();
        if ($order->status !== 'pending') {
            return $this->respondError($request, 'order', 'Đơn không thể huỷ ở trạng thái hiện tại.');
        }
        $order->update(['status' => 'cancelled', 'note' => 'User tự huỷ']);

        return $this->respondOk($request, 'Đã huỷ đơn.', route('billing.index'));
    }

    /** Build payload QR + info hiển thị trong modal. */
    private function buildQrPayload(int $amount, string $code): array
    {
        $bank = config('services.bank');
        $qrUrl = 'https://img.vietqr.io/image/'
            .rawurlencode($bank['code']).'-'.rawurlencode($bank['account'])
            .'-compact.png?amount='.$amount
            .'&addInfo='.rawurlencode($code)
            .'&accountName='.rawurlencode($bank['name']);

        return [
            'url' => $qrUrl,
            'bank_code' => $bank['code'],
            'account' => $bank['account'],
            'name' => $bank['name'],
        ];
    }
}
