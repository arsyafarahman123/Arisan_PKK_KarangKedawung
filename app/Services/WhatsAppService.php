<?php

namespace App\Services;

use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class WhatsAppService
{
    public static function createWaLink(string $phone, string $message): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }

        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
    }

    public static function notifyPaymentReminder(Payment $payment, string $reminderType = 'reminder_h3'): NotificationLog
    {
        $user = $payment->user;
        $group = $payment->group;
        $round = $payment->round;
        $formattedAmount = 'Rp ' . number_format($payment->total_amount, 0, ',', '.');
        $dueDate = Carbon::parse($payment->due_date)->translatedFormat('l, d F Y');

        $intro = match ($reminderType) {
            'reminder_h1' => "*PENGINGAT IURAN ARISAN (H-1 JATUH TEMPO)*",
            'reminder_h0' => "*PENGINGAT IURAN ARISAN (HARI INI JATUH TEMPO)*",
            default => "*PENGINGAT IURAN ARISAN*",
        };

        $msg = "Assalamu'alaikum Wr. Wb.\n"
             . "Kepada Yth. Ibu *{$user->name}*,\n\n"
             . "{$intro}\n"
             . "Kelompok: *{$group->name}*\n"
             . "Putaran: *Ke-{$round->round_number}*\n"
             . "Nominal Iuran: *{$formattedAmount}*\n"
             . "Batas Pembayaran: *{$dueDate}*\n\n";

        if ($group->bank_account_no) {
            $msg .= "Pembayaran dapat ditransfer melalui:\n"
                  . "• Bank: *{$group->bank_name}*\n"
                  . "• No. Rekening: *{$group->bank_account_no}*\n"
                  . "• Atas Nama: *{$group->bank_account_name}*\n\n";
        }

        $msg .= "Mohon mengunggah bukti transfer melalui sistem Arisan PKK KarangKedawung untuk verifikasi oleh bendahara.\n\n"
              . "Terima kasih atas perhatian dan kerja samanya.\n\n"
              . "Wassalamu'alaikum Wr. Wb.\n"
              . "— _Pengurus PKK KarangKedawung_";

        $waLink = self::createWaLink($user->phone, $msg);

        return NotificationLog::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'round_id' => $round->id,
            'type' => $reminderType,
            'title' => 'Pengingat Iuran Putaran ke-' . $round->round_number,
            'message' => $msg,
            'channel' => 'whatsapp',
            'status' => 'sent',
            'sent_at' => now(),
            'whatsapp_link' => $waLink,
        ]);
    }

    public static function notifyLatePenalty(Payment $payment): NotificationLog
    {
        $user = $payment->user;
        $group = $payment->group;
        $round = $payment->round;
        $pokok = 'Rp ' . number_format($payment->amount, 0, ',', '.');
        $denda = 'Rp ' . number_format($payment->penalty_amount, 0, ',', '.');
        $total = 'Rp ' . number_format($payment->total_amount, 0, ',', '.');

        $msg = "Assalamu'alaikum Wr. Wb.\n"
             . "Kepada Yth. Ibu *{$user->name}*,\n\n"
             . "*PEMBERITAHUAN TUNGGAKAN IURAN & DENDA*\n"
             . "Kelompok: *{$group->name}* (Putaran ke-{$round->round_number})\n\n"
             . "Iuran arisan tercatat telah melewati batas jatuh tempo:\n"
             . "• Pokok Iuran: *{$pokok}*\n"
             . "• Denda Keterlambatan: *{$denda}*\n"
             . "• *Total Tagihan: {$total}*\n\n"
             . "Mohon segera menyelesaikan pembayaran agar dapat diikutsertakan dalam pengocokan arisan. Terima kasih atas kerja samanya.\n\n"
             . "Wassalamu'alaikum Wr. Wb.\n"
             . "— _Pengurus PKK KarangKedawung_";

        $waLink = self::createWaLink($user->phone, $msg);

        return NotificationLog::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'round_id' => $round->id,
            'type' => 'late_penalty',
            'title' => 'Tagihan Telat & Denda Putaran ke-' . $round->round_number,
            'message' => $msg,
            'channel' => 'whatsapp',
            'status' => 'sent',
            'sent_at' => now(),
            'whatsapp_link' => $waLink,
        ]);
    }

    public static function notifyPaymentVerified(Payment $payment): NotificationLog
    {
        $user = $payment->user;
        $group = $payment->group;
        $round = $payment->round;
        $formattedAmount = 'Rp ' . number_format($payment->total_amount, 0, ',', '.');

        $msg = "Assalamu'alaikum Wr. Wb.\n"
             . "Kepada Yth. Ibu *{$user->name}*,\n\n"
             . "*KONFIRMASI PEMBAYARAN IURAN*\n"
             . "Kelompok: *{$group->name}*\n"
             . "Putaran: *Ke-{$round->round_number}*\n"
             . "Jumlah: *{$formattedAmount}* (LUNAS)\n"
             . "Waktu Verifikasi: *" . now()->translatedFormat('d F Y H:i') . " WIB*\n\n"
             . "Pembayaran Anda telah berhasil diverifikasi dan tercatat ke dalam sistem. Terima kasih.\n\n"
             . "Wassalamu'alaikum Wr. Wb.\n"
             . "— _Bendahara PKK KarangKedawung_";

        $waLink = self::createWaLink($user->phone, $msg);

        return NotificationLog::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'round_id' => $round->id,
            'type' => 'payment_verified',
            'title' => 'Pembayaran Putaran ke-' . $round->round_number . ' Diterima',
            'message' => $msg,
            'channel' => 'whatsapp',
            'status' => 'sent',
            'sent_at' => now(),
            'whatsapp_link' => $waLink,
        ]);
    }

    public static function notifyWinnerAnnouncement(ArisanRound $round): array
    {
        $winner = $round->winner;
        $group = $round->group;
        $formattedPrize = 'Rp ' . number_format($round->winning_amount ?? $group->total_pot, 0, ',', '.');
        $drawDate = Carbon::parse($round->draw_date)->translatedFormat('l, d F Y');

        $logs = [];

        // 1. Message for the Winner
        $winnerMsg = "Assalamu'alaikum Wr. Wb.\n"
                   . "Kepada Yth. Ibu *{$winner->name}*,\n\n"
                   . "*PENGUMUMAN PEMENANG ARISAN*\n"
                   . "Selamat, nama Anda telah keluar sebagai pemenang arisan pada:\n"
                   . "• Kelompok: *{$group->name}*\n"
                   . "• Putaran: *Ke-{$round->round_number}*\n"
                   . "• Tanggal Kocokan: *{$drawDate}*\n"
                   . "• Total Uang Arisan: *{$formattedPrize}*\n\n"
                   . "Pengurus akan segera menghubungi Anda terkait prosedur serah terima dan pencairan dana.\n\n"
                   . "Wassalamu'alaikum Wr. Wb.\n"
                   . "— _Pengurus PKK KarangKedawung_";

        $logs[] = NotificationLog::create([
            'user_id' => $winner->id,
            'group_id' => $group->id,
            'round_id' => $round->id,
            'type' => 'winner_announcement',
            'title' => 'Selamat! Anda Menang Arisan Putaran ke-' . $round->round_number,
            'message' => $winnerMsg,
            'channel' => 'whatsapp',
            'status' => 'sent',
            'sent_at' => now(),
            'whatsapp_link' => self::createWaLink($winner->phone, $winnerMsg),
        ]);

        return $logs;
    }

    public static function notifyDisbursement(ArisanRound $round): NotificationLog
    {
        $winner = $round->winner;
        $group = $round->group;
        $formattedPrize = 'Rp ' . number_format($round->winning_amount ?? $group->total_pot, 0, ',', '.');
        $disbursedDate = Carbon::parse($round->disbursed_at ?? now())->translatedFormat('l, d F Y H:i');

        $msg = "Assalamu'alaikum Wr. Wb.\n"
             . "Kepada Yth. Ibu *{$winner->name}*,\n\n"
             . "*BUKTI PENYERAHAN DANA ARISAN*\n"
             . "Kelompok: *{$group->name}* (Putaran ke-{$round->round_number})\n"
             . "Nominal Diserahkan: *{$formattedPrize}*\n"
             . "Waktu Penyerahan: *{$disbursedDate} WIB*\n";

        if ($round->disbursement_notes) {
            $msg .= "Catatan: _{$round->disbursement_notes}_\n\n";
        }

        $msg .= "Dana arisan telah diserahkan dengan sukses. Terima kasih atas partisipasi aktif Anda.\n\n"
              . "Wassalamu'alaikum Wr. Wb.\n"
              . "— _Pengurus PKK KarangKedawung_";

        $waLink = self::createWaLink($winner->phone, $msg);

        return NotificationLog::create([
            'user_id' => $winner->id,
            'group_id' => $group->id,
            'round_id' => $round->id,
            'type' => 'disbursement_info',
            'title' => 'Pencairan Dana Arisan Putaran ke-' . $round->round_number,
            'message' => $msg,
            'channel' => 'whatsapp',
            'status' => 'sent',
            'sent_at' => now(),
            'whatsapp_link' => $waLink,
        ]);
    }
}
