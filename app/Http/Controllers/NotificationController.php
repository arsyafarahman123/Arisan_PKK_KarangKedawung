<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $channel = $request->get('channel');
        $type = $request->get('type');

        $query = NotificationLog::with(['user', 'group', 'round']);

        if ($channel) {
            $query->where('channel', $channel);
        }
        if ($type) {
            $query->where('type', $type);
        }

        $logs = $query->latest()->paginate(15);
        $groups = ArisanGroup::where('status', 'active')->with('rounds')->get();

        return view('notifications.index', compact('logs', 'groups', 'channel', 'type'));
    }

    public function sendBulkReminder(Request $request)
    {
        $validated = $request->validate([
            'round_id' => 'required|exists:arisan_rounds,id',
            'reminder_type' => 'required|in:reminder_h3,reminder_h1,reminder_h0,draw_reminder',
        ]);

        $round = ArisanRound::with(['group', 'payments.user'])->findOrFail($validated['round_id']);
        $unpaidPayments = $round->payments->whereIn('payment_status', ['unpaid', 'late', 'pending_verification']);

        $count = 0;
        foreach ($unpaidPayments as $payment) {
            WhatsAppService::notifyPaymentReminder($payment, $validated['reminder_type']);
            $count++;
        }

        ActivityLog::log(
            'kirim_pengingat_wa',
            "Mengirimkan {$count} pengingat WhatsApp ({$validated['reminder_type']}) untuk putaran ke-{$round->round_number} kelompok {$round->group->name}"
        );

        return back()->with('success', "Berhasil menyiapkan {$count} pesan pengingat WhatsApp! Klik tombol 'Kirim WA' pada daftar untuk membuka WhatsApp langsung.");
    }

    public function sendLateNotice(Payment $payment)
    {
        $log = WhatsAppService::notifyLatePenalty($payment);

        return redirect($log->whatsapp_link);
    }

    public function sendPaymentReminder(Payment $payment, string $type = 'reminder_h3')
    {
        $log = WhatsAppService::notifyPaymentReminder($payment, $type);

        return redirect($log->whatsapp_link);
    }
}
