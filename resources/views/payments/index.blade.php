@extends('layouts.app')

@section('title', 'Iuran & Verifikasi Pembayaran — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-money-bill-transfer text-emerald-600"></i>
                <span>Iuran & Verifikasi Pembayaran</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola status pembayaran seluruh anggota, verifikasi bukti transfer, dan catat pembayaran tunai</p>
        </div>

        <div class="flex items-center gap-3">
            @if($pendingCount > 0)
                <span class="px-3.5 py-1.5 rounded-full bg-rose-100 text-rose-800 text-xs font-bold border border-rose-200 animate-pulse flex items-center gap-1.5">
                    <i class="fa-solid fa-bell"></i>
                    <span>{{ $pendingCount }} Menunggu Verifikasi</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <form action="{{ route('payments.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <!-- Filter Kelompok -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Kelompok Arisan</label>
                <select name="group_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 outline-none">
                    <option value="">-- Semua Kelompok --</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Pembayaran</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="pending_verification" {{ $status === 'pending_verification' ? 'selected' : '' }}>⏳ Menunggu Verifikasi</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>⚪ Belum Bayar</option>
                    <option value="late" {{ $status === 'late' ? 'selected' : '' }}>🔴 Telat / Ada Denda</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>🟢 Lunas</option>
                </select>
            </div>

            <!-- Search Nama -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Nama / No WA</label>
                <div class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Ketik nama anggota..." class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full btn-pkk py-2 rounded-xl text-xs font-bold shadow">
                    Terapkan Filter
                </button>
                <a href="{{ route('payments.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Anggota</th>
                        <th class="py-3 px-4">Kelompok & Putaran</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4">Pokok</th>
                        <th class="py-3 px-4">Denda</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Bukti Bayar</th>
                        <th class="py-3 px-4 text-right">Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($payments as $p)
                        @php
                            $isPaid = $p->payment_status === 'paid';
                            $isPending = $p->payment_status === 'pending_verification';
                            $isLate = $p->payment_status === 'late';
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $isPending ? 'bg-amber-50/50' : '' }}">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $p->user->name }}
                                <p class="text-[10px] text-slate-400 font-mono font-normal">{{ $p->user->phone }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800">{{ $p->group->name }}</span>
                                <p class="text-[10px] text-emerald-700 font-bold">Putaran Ke-{{ $p->round->round_number }}</p>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">
                                {{ $p->due_date ? $p->due_date->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 font-bold {{ $p->penalty_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                Rp {{ number_format($p->penalty_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-emerald-700">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4">
                                @if($isPaid)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check mr-0.5"></i> Lunas
                                    </span>
                                @elseif($isPending)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
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
                                    <button type="button" onclick="previewImage('{{ asset('storage/' . $p->proof_image) }}', '{{ $p->user->name }}', '{{ $p->user_notes }}')" class="inline-flex items-center gap-1 text-emerald-700 font-bold hover:underline">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Lihat Bukti</span>
                                    </button>
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
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm">
                                            Setujui
                                        </button>
                                    </form>

                                    <!-- Quick Cash Payment -->
                                    <form action="{{ route('payments.quick_cash', $p) }}" method="POST" class="inline" onsubmit="return confirm('Catat iuran tunai langsung Rp {{ number_format($p->total_amount, 0, ',', '.') }} dari {{ $p->user->name }}?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px]" title="Terima Tunai">
                                            Tunai
                                        </button>
                                    </form>

                                    <!-- Direct WA Reminder -->
                                    <a href="{{ route('notifications.send_reminder', ['payment' => $p, 'type' => $isLate ? 'late_penalty' : 'reminder_h3']) }}" target="_blank" class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px]" title="Kirim WA">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                    </a>
                                @else
                                    <span class="text-[11px] text-emerald-700 font-bold"><i class="fa-solid fa-circle-check"></i> Diverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-slate-400">Tidak ada tagihan atau pembayaran yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
    </div>

</div>

<!-- Modal Preview Bukti Transfer -->
<div id="proofModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900" id="proofModalTitle">Bukti Transfer</h3>
            <button onclick="document.getElementById('proofModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="mt-4 text-center">
            <img id="proofModalImg" src="" alt="Bukti Transfer" class="max-h-96 mx-auto rounded-2xl border border-slate-200 object-contain shadow-sm">
            <p id="proofModalNotes" class="mt-3 text-xs text-slate-600 italic bg-slate-50 p-2.5 rounded-xl"></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(url, userName, notes) {
        document.getElementById('proofModalTitle').innerText = 'Bukti Transfer Ibu ' + userName;
        document.getElementById('proofModalImg').src = url;
        document.getElementById('proofModalNotes').innerText = notes ? 'Catatan: "' + notes + '"' : 'Tidak ada catatan tambahan.';
        document.getElementById('proofModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
