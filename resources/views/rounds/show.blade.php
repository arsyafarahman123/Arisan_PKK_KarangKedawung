@extends('layouts.app')

@section('title', 'Putaran Ke-' . $round->round_number . ' — ' . $group->name)

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('groups.show', $group) }}" class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Kelompok {{ $group->name }}</span>
            </a>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Putaran Ke-{{ $round->round_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $round->status === 'completed' ? 'bg-purple-100 text-purple-800' : ($round->status === 'ongoing' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                    {{ $round->status === 'completed' ? '🏆 Selesai' : ($round->status === 'ongoing' ? '🟢 Sedang Berjalan' : '📝 Belum Mulai') }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">{{ $group->name }} • Nominal Iuran: Rp {{ number_format($group->contribution_amount, 0, ',', '.') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button onclick="document.getElementById('rescheduleModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-regular fa-calendar-days text-amber-600"></i>
                <span>Geser Jadwal (Reschedule)</span>
            </button>
            <button onclick="document.getElementById('hostModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-house-chimney text-teal-600"></i>
                <span>Tuan Rumah</span>
            </button>
            <a href="{{ route('draws.index', $round) }}" class="btn-rose px-4 py-2 rounded-xl font-bold text-xs shadow flex items-center gap-1.5">
                <i class="fa-solid fa-dice text-sm"></i>
                <span>{{ $round->status === 'completed' ? 'Lihat Pemenang & Pencairan' : 'Ruang Kocok Arisan' }}</span>
            </a>
        </div>
    </div>

    <!-- 3 Info Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <!-- Card 1: Tanggal & Jadwal -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-2">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jadwal Penting</h3>
            <div class="text-xs space-y-1.5 pt-1">
                <p class="flex justify-between">
                    <span class="text-slate-500">Batas Jatuh Tempo:</span>
                    <strong class="text-amber-700">{{ $round->due_date->translatedFormat('d F Y') }}</strong>
                </p>
                <p class="flex justify-between">
                    <span class="text-slate-500">Tanggal Kocokan:</span>
                    <strong class="text-emerald-700">{{ $round->draw_date->translatedFormat('d F Y') }}</strong>
                </p>
            </div>
        </div>

        <!-- Card 2: Tuan Rumah & Lokasi -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-2">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tuan Rumah Pertemuan</h3>
            <div class="text-xs space-y-1.5 pt-1">
                <p class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-check text-teal-600"></i>
                    <span>{{ $round->host ? $round->host->name : 'Belum Ditentukan' }}</span>
                </p>
                <p class="text-slate-500 text-[11px]"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> {{ $round->host_location ?? 'Balai RW / Rumah Tuan Rumah' }}</p>
            </div>
        </div>

        <!-- Card 3: Pemenang Arisan -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-2">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hasil Pemenang</h3>
            <div class="text-xs space-y-1.5 pt-1">
                @if($round->winner)
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-900">{{ $round->winner->name }}</p>
                            <p class="text-[11px] text-emerald-700 font-bold">Rp {{ number_format($round->winning_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-slate-400 italic">Belum dilakukan pengocokan.</p>
                @endif
            </div>
        </div>

    </div>

    <!-- Payments Table & Verifications -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                    <span>Daftar Pembayaran Iuran Putaran Ke-{{ $round->round_number }}</span>
                </h2>
                <p class="text-xs text-slate-500">Status iuran seluruh anggota pada putaran ini</p>
            </div>

            <!-- WhatsApp Blaster for this round -->
            <form action="{{ route('notifications.send_bulk') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="hidden" name="round_id" value="{{ $round->id }}">
                <select name="reminder_type" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 outline-none">
                    <option value="reminder_h3">Pengingat H-3</option>
                    <option value="reminder_h1">Pengingat H-1 Besok</option>
                    <option value="reminder_h0">Pengingat Hari H</option>
                </select>
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Kirim WA Massal</span>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Anggota</th>
                        <th class="py-3 px-4">No. WA</th>
                        <th class="py-3 px-4">Pokok Iuran</th>
                        <th class="py-3 px-4">Denda Telat</th>
                        <th class="py-3 px-4">Total Bayar</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Bukti Transfer</th>
                        <th class="py-3 px-4 text-right">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($round->payments as $p)
                        @php
                            $isPaid = $p->payment_status === 'paid';
                            $isPending = $p->payment_status === 'pending_verification';
                            $isLate = $p->payment_status === 'late';
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $isPending ? 'bg-amber-50/40' : '' }}">
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $p->user->name }}</td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono">{{ $p->user->phone }}</td>
                            <td class="py-3.5 px-4 font-medium">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 font-semibold {{ $p->penalty_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                Rp {{ number_format($p->penalty_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-emerald-700">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4">
                                @if($isPaid)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check mr-0.5"></i> Lunas
                                    </span>
                                @elseif($isPending)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        <i class="fa-solid fa-clock mr-0.5"></i> Menunggu Verifikasi
                                    </span>
                                @elseif($isLate)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-triangle-exclamation mr-0.5"></i> Telat
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($p->proof_image)
                                    <a href="{{ asset('storage/' . $p->proof_image) }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-700 font-bold hover:underline">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Lihat Bukti</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                @if(!$isPaid)
                                    <!-- Verifikasi Setuju Form -->
                                    <form action="{{ route('payments.verify', $p) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm">
                                            Setujui Lunas
                                        </button>
                                    </form>

                                    <!-- Quick Cash Button -->
                                    <form action="{{ route('payments.quick_cash', $p) }}" method="POST" class="inline" onsubmit="return confirm('Catat iuran tunai langsung dari {{ $p->user->name }}?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px]">
                                            Tunai
                                        </button>
                                    </form>

                                    <!-- Direct WA Reminder -->
                                    <a href="{{ route('notifications.send_reminder', ['payment' => $p, 'type' => $isLate ? 'late_penalty' : 'reminder_h3']) }}" target="_blank" class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px]">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium">Verified by {{ $p->verifier ? $p->verifier->name : 'Bendahara' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-slate-400">Belum ada tagihan iuran pada putaran ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Reschedule Jadwal -->
<div id="rescheduleModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Geser Jadwal Putaran Ke-{{ $round->round_number }}</h3>
            <button onclick="document.getElementById('rescheduleModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('rounds.reschedule', $round) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Jatuh Tempo Baru *</label>
                <input type="date" name="due_date" value="{{ $round->due_date->format('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengocokan / Pertemuan Baru *</label>
                <input type="date" name="draw_date" value="{{ $round->draw_date->format('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="cascade_next" value="1" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 mt-0.5">
                    <span class="text-xs text-amber-900 font-semibold leading-relaxed">
                        Geser tanggal putaran-putaran selanjutnya secara otomatis menyesuaikan selisih hari ini
                    </span>
                </label>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('rescheduleModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-pkk px-5 py-2 rounded-xl font-bold text-xs shadow">
                    Simpan Jadwal Baru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Atur Tuan Rumah -->
<div id="hostModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Atur Tuan Rumah Putaran Ke-{{ $round->round_number }}</h3>
            <button onclick="document.getElementById('hostModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('rounds.update_host', $round) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Ibu Tuan Rumah</label>
                <select name="host_user_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
                    <option value="">-- Belum Ditentukan --</option>
                    @foreach($allMembers as $m)
                        <option value="{{ $m->user->id }}" {{ $round->host_user_id === $m->user->id ? 'selected' : '' }}>
                            {{ $m->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat / Lokasi Pertemuan</label>
                <input type="text" name="host_location" value="{{ $round->host_location }}" placeholder="Contoh: Rumah Bu RT 02 / Balai RW 03" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Acara / Konsumsi</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Acara arisan sekaligus penyuluhan kesehatan balita..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">{{ $round->notes }}</textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('hostModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-pkk px-5 py-2 rounded-xl font-bold text-xs shadow">
                    Simpan Info Tuan Rumah
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
