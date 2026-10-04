@extends('layouts.app')

@section('title', 'Notifikasi & Pengingat WhatsApp — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-brands fa-whatsapp text-emerald-600 text-3xl"></i>
                <span>Pengingat WhatsApp & Log Notifikasi</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kirim pesan WhatsApp otomatis ke ibu-ibu PKK: pengingat H-3, H-1, hari H, tagihan telat, konfirmasi lunas, dan pengumuman pemenang</p>
        </div>
    </div>

    <!-- Quick Blast Form Card -->
    <div class="bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg">
        <div class="max-w-2xl">
            <span class="inline-block px-3 py-0.5 rounded-full bg-white/20 text-[11px] font-bold backdrop-blur-md mb-2">
                <i class="fa-solid fa-bolt mr-1"></i> WhatsApp Quick Blaster
            </span>
            <h2 class="text-lg font-bold">Kirim Pesan Pengingat Iuran Massal</h2>
            <p class="text-emerald-100 text-xs mt-1 leading-relaxed">
                Pilih putaran arisan yang sedang aktif dan jenis pengingat. Sistem akan menyiapkan pesan WhatsApp santun beremotikon untuk seluruh anggota yang belum lunas.
            </p>

            <form action="{{ route('notifications.send_bulk') }}" method="POST" class="mt-4 flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <select name="round_id" required class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white text-slate-800 text-xs font-bold focus:ring-2 focus:ring-emerald-300 outline-none shadow-sm">
                    <option value="">-- Pilih Putaran Arisan --</option>
                    @foreach($groups as $g)
                        <optgroup label="{{ $g->name }}">
                            @foreach($g->rounds as $r)
                                <option value="{{ $r->id }}">
                                    {{ $g->name }} - Putaran {{ $r->round_number }} (Jatuh Tempo: {{ $r->due_date->translatedFormat('d M Y') }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                <select name="reminder_type" required class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white text-slate-800 text-xs font-bold focus:ring-2 focus:ring-emerald-300 outline-none shadow-sm">
                    <option value="reminder_h3">Pengingat H-3 (Jatuh Tempo)</option>
                    <option value="reminder_h1">Pengingat H-1 (Besok Jatuh Tempo)</option>
                    <option value="reminder_h0">Pengingat Hari H (Hari Ini)</option>
                </select>

                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-black transition shadow-md flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fa-brands fa-whatsapp text-emerald-400 text-sm"></i>
                    <span>Proses Pengingat</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Notification Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto text-xs">
        <a href="{{ route('notifications.index') }}" class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ !$type ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Semua Pesan ({{ $logs->total() }})
        </a>
        <a href="{{ route('notifications.index', ['type' => 'reminder_h3']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $type === 'reminder_h3' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-regular fa-clock text-[11px] {{ $type === 'reminder_h3' ? 'text-white' : 'text-slate-500' }}"></i>
            <span>Pengingat Iuran</span>
        </a>
        <a href="{{ route('notifications.index', ['type' => 'late_penalty']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $type === 'late_penalty' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-triangle-exclamation text-[11px] {{ $type === 'late_penalty' ? 'text-white' : 'text-rose-500' }}"></i>
            <span>Tagihan Telat & Denda</span>
        </a>
        <a href="{{ route('notifications.index', ['type' => 'payment_verified']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $type === 'payment_verified' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-circle-check text-[11px] {{ $type === 'payment_verified' ? 'text-white' : 'text-emerald-500' }}"></i>
            <span>Konfirmasi Pembayaran</span>
        </a>
        <a href="{{ route('notifications.index', ['type' => 'winner_announcement']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $type === 'winner_announcement' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-trophy text-[11px] {{ $type === 'winner_announcement' ? 'text-white' : 'text-amber-500' }}"></i>
            <span>Pengumuman Pemenang</span>
        </a>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Penerima (Ibu)</th>
                        <th class="py-3 px-4">No. WA</th>
                        <th class="py-3 px-4">Jenis Pesan</th>
                        <th class="py-3 px-4">Isi Pesan WhatsApp</th>
                        <th class="py-3 px-4">Waktu Generate</th>
                        <th class="py-3 px-4 text-right">Kirim WA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $log->user->name }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $log->user->phone }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', strtoupper($log->type)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <p class="line-clamp-2 text-[11px] text-slate-600 italic">"{{ $log->message }}"</p>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">{{ $log->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                @if($log->whatsapp_link)
                                    <a href="{{ $log->whatsapp_link }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-sm inline-flex items-center gap-1.5">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                        <span>Buka WA</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 italic text-[10px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">Belum ada riwayat notifikasi yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
