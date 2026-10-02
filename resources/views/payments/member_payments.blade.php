@extends('layouts.app')

@section('title', 'Tagihan & Riwayat Pembayaran Saya — Arisan PKK KarangKedawung')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-wallet text-emerald-600"></i>
                <span>Tagihan & Riwayat Pembayaran Saya</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Daftar seluruh iuran arisan Ibu {{ $user->name }}, status verifikasi, dan bukti transfer</p>
        </div>
    </div>

    <!-- Bills Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Kelompok Arisan</th>
                        <th class="py-3 px-4">Putaran</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4">Pokok Iuran</th>
                        <th class="py-3 px-4">Denda</th>
                        <th class="py-3 px-4">Total Bayar</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($myBills as $bill)
                        @php
                            $isPaid = $bill->payment_status === 'paid';
                            $isPending = $bill->payment_status === 'pending_verification';
                            $isLate = $bill->payment_status === 'late';
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $bill->group->name }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">Putaran {{ $bill->round->round_number }}</td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">{{ $bill->due_date->translatedFormat('d M Y') }}</td>
                            <td class="py-3.5 px-4">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 font-semibold {{ $bill->penalty_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                Rp {{ number_format($bill->penalty_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-emerald-700">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4">
                                @if($isPaid)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check mr-0.5"></i> Lunas
                                    </span>
                                @elseif($isPending)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
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
                            <td class="py-3.5 px-4 text-right">
                                @if(!$isPaid && !$isPending)
                                    <button type="button" onclick="openUploadModal('{{ $bill->id }}', '{{ $bill->group->name }}', '{{ $bill->round->round_number }}', '{{ number_format($bill->total_amount, 0, ',', '.') }}', '{{ $bill->group->bank_name }}', '{{ $bill->group->bank_account_no }}', '{{ $bill->group->bank_account_name }}')" class="btn-pkk px-3 py-1.5 rounded-xl text-xs font-bold shadow flex items-center gap-1 ml-auto">
                                        <i class="fa-solid fa-upload"></i>
                                        <span>Upload Bukti</span>
                                    </button>
                                @elseif($isPaid)
                                    <span class="text-[11px] text-emerald-700 font-semibold">Telah Lunas</span>
                                @else
                                    <span class="text-[11px] text-amber-700 font-semibold">Sedang Diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">Ibu belum memiliki riwayat tagihan atau pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $myBills->links() }}
        </div>
    </div>

</div>

<!-- Modal Upload Bukti Transfer -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up text-emerald-600"></i>
                <span>Upload Bukti Transfer</span>
            </h3>
            <button onclick="closeUploadModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="uploadForm" action="" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf

            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-900 space-y-1">
                <p id="modalGroupTitle" class="font-bold"></p>
                <p>Total Iuran: <strong id="modalAmount" class="text-emerald-800"></strong></p>
                <p id="modalBankInfo" class="text-[11px] text-emerald-700"></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran *</label>
                <select name="payment_method" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 outline-none">
                    <option value="transfer">Transfer Bank (BCA / BRI / Mandiri / Kas)</option>
                    <option value="qris">QRIS / E-Wallet (GoPay / OVO / Dana)</option>
                    <option value="cash">Titip Tunai ke Pengurus</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Foto Bukti Transfer / Struk *</label>
                <input type="file" name="proof_image" accept="image/*" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="user_notes" rows="2" placeholder="Contoh: Transfer lewat BCA an Suami" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-pkk px-5 py-2 rounded-xl font-bold text-xs shadow flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Kirim Bukti Pembayaran</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openUploadModal(paymentId, groupName, roundNumber, amount, bankName, accNo, accName) {
        const form = document.getElementById('uploadForm');
        form.action = '/payments/' + paymentId + '/upload-proof';
        document.getElementById('modalGroupTitle').innerText = groupName + ' (Putaran ' + roundNumber + ')';
        document.getElementById('modalAmount').innerText = 'Rp ' + amount;
        
        if (accNo) {
            document.getElementById('modalBankInfo').innerText = 'Transfer ke: ' + bankName + ' - ' + accNo + ' (a.n ' + accName + ')';
        } else {
            document.getElementById('modalBankInfo').innerText = '';
        }

        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
