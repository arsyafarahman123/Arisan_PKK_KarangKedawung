<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\Payment;
use App\Models\User;
use App\Services\ArisanService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        // Update late penalties
        ArisanService::checkAllOverduePenalties();

        $groupId = $request->get('group_id');
        $roundId = $request->get('round_id');
        $status = $request->get('status');
        $search = $request->get('q');

        $groups = ArisanGroup::orderBy('name')->get();
        $rounds = $groupId ? ArisanRound::where('group_id', $groupId)->orderBy('round_number')->get() : collect();

        $query = Payment::with(['user', 'group', 'round', 'verifier']);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }
        if ($roundId) {
            $query->where('round_id', $roundId);
        }
        if ($status && in_array($status, ['unpaid', 'pending_verification', 'paid', 'late'])) {
            $query->where('payment_status', $status);
        }
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest('created_at')->paginate(15);

        $pendingCount = Payment::where('payment_status', 'pending_verification')->count();
        $totalPaidSum = Payment::where('payment_status', 'paid')->sum('total_amount');
        $totalUnpaidSum = Payment::whereIn('payment_status', ['unpaid', 'late'])->sum('total_amount');

        return view('payments.index', compact(
            'payments',
            'groups',
            'rounds',
            'groupId',
            'roundId',
            'status',
            'search',
            'pendingCount',
            'totalPaidSum',
            'totalUnpaidSum'
        ));
    }

    public function memberPayments(Request $request)
    {
        $user = Auth::user();
        ArisanService::checkAllOverduePenalties();

        $myBills = Payment::where('user_id', $user->id)
            ->with(['group', 'round'])
            ->orderBy('due_date', 'desc')
            ->paginate(10);

        return view('payments.member_payments', compact('myBills', 'user'));
    }

    public function uploadProof(Request $request, Payment $payment)
    {
        // Authorization: must be the user themselves or admin
        if (Auth::id() !== $payment->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'proof_image' => 'required|image|max:3072',
            'user_notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:transfer,cash,qris',
        ]);

        $path = $request->file('proof_image')->store('payment_proofs', 'public');

        $payment->update([
            'proof_image' => $path,
            'user_notes' => $validated['user_notes'] ?? null,
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'pending_verification',
            'paid_at' => now(),
            'rejection_reason' => null,
        ]);

        ActivityLog::log(
            'upload_bukti_bayar',
            "Ibu {$payment->user->name} mengunggah bukti bayar putaran ke-{$payment->round->round_number} kelompok {$payment->group->name}",
            $payment->user_id
        );

        return back()->with('success', 'Alhamdulillah! Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi Bendahara.');
    }

    public function verify(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'nullable|required_if:action,reject|string|max:500',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validated['action'] === 'approve') {
            $payment->update([
                'payment_status' => 'paid',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'admin_notes' => $validated['admin_notes'] ?? null,
                'rejection_reason' => null,
            ]);

            // Send WhatsApp confirmation
            WhatsAppService::notifyPaymentVerified($payment);

            ActivityLog::log(
                'verifikasi_iuran_lunas',
                "Menyetujui pembayaran iuran Ibu {$payment->user->name} (Rp " . number_format($payment->total_amount, 0, ',', '.') . ")"
            );

            return back()->with('success', "Pembayaran Ibu {$payment->user->name} berhasil diverifikasi LUNAS!");
        } else {
            $payment->update([
                'payment_status' => 'unpaid',
                'rejection_reason' => $validated['rejection_reason'],
                'admin_notes' => $validated['admin_notes'] ?? null,
                'verified_at' => null,
                'verified_by' => null,
            ]);

            ActivityLog::log(
                'tolak_bukti_bayar',
                "Menolak bukti pembayaran Ibu {$payment->user->name}. Alasan: {$validated['rejection_reason']}"
            );

            return back()->with('info', "Bukti pembayaran ditolak dengan alasan yang telah dicatat.");
        }
    }

    public function quickCashPayment(Request $request, Payment $payment)
    {
        $payment->update([
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'paid_at' => now(),
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'admin_notes' => 'Pembayaran tunai diterima langsung pada pertemuan PKK.',
        ]);

        // Send WhatsApp confirmation
        WhatsAppService::notifyPaymentVerified($payment);

        ActivityLog::log(
            'bayar_tunai_langsung',
            "Mencatat iuran tunai langsung Ibu {$payment->user->name} (Rp " . number_format($payment->total_amount, 0, ',', '.') . ")"
        );

        return back()->with('success', "Pembayaran tunai Ibu {$payment->user->name} telah dicatat LUNAS!");
    }
}
