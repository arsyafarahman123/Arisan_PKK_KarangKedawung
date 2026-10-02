@extends('layouts.app')

@section('title', 'Dashboard Anggota — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Welcome Member Banner -->
    <div class="relative bg-gradient-to-r from-emerald-600 via-teal-600 to-rose-500 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-teal-900/10 overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-md mb-2">
                    <i class="fa-solid fa-heart text-rose-300"></i>
                    <span>Anggota PKK KarangKedawung</span>
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Sugeng Rawuh, Ibu {{ $user->name }}! 🌸
                </h1>
                <p class="text-emerald-50 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Halaman personal arisan Ibu. Pantau tagihan, upload bukti pembayaran, dan lihat hasil kocokan dengan mudah.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('transparency') }}" class="px-4 py-2.5 rounded-xl bg-white text-emerald-800 font-bold text-xs shadow-md hover:bg-emerald-50 transition flex items-center gap-2">
                    <i class="fa-solid fa-eye text-emerald-600"></i>
                    <span>Buku Transparansi</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Active Bills / Tagihan Bulan Ini -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-emerald-600"></i>
                    <span>Tagihan Iuran Saya Saat Ini</span>
                </h2>
                <p class="text-xs text-slate-500">Iuran yang perlu dibayarkan pada putaran aktif</p>
            </div>
        </div>

        @if($myBills->isEmpty())
            <div class="text-center py-8 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-2 text-xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 class="text-sm font-bold text-emerald-950">Alhamdulillah, Tidak Ada Tagihan Tertunggak!</h3>
                <p class="text-xs text-emerald-700 mt-0.5">Semua iuran putaran berjalan telah Ibu lunasi. Terima kasih atas partisipasinya ya Bu! ✨</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($myBills as $bill)
                    @php
                        $isPending = $bill->payment_status === 'pending_verification';
                        $isLate = $bill->payment_status === 'late';
                    @endphp
                    <div class="p-5 rounded-2xl border {{ $isPending ? 'bg-amber-50/40 border-amber-200' : ($isLate ? 'bg-rose-50/40 border-rose-200' : 'bg-slate-50 border-slate-200') }} transition">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $isPending ? 'bg-amber-100 text-amber-800' : ($isLate ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-800') }}">
                                    {{ $isPending ? 'Menunggu Verifikasi Bendahara' : ($isLate ? 'Terlambat / Ada Denda' : 'Belum Dibayar') }}
                                </span>
                                <h3 class="text-sm font-bold text-slate-900 mt-2">{{ $bill->group->name }}</h3>
                                <p class="text-xs text-slate-500">Putaran Ke-{{ $bill->round->round_number }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-500">Total Iuran:</span>
                                <p class="text-lg font-extrabold text-emerald-700">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</p>
                                @if($bill->penalty_amount > 0)
                                    <span class="text-[10px] text-rose-600 font-semibold">(Pokok Rp {{ number_format($bill->amount, 0, ',', '.') }} + Denda Rp {{ number_format($bill->penalty_amount, 0, ',', '.') }})</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200/70 text-xs text-slate-600 space-y-1">
                            <p><i class="fa-regular fa-calendar text-slate-400 mr-1.5"></i> Batas Jatuh Tempo: <strong>{{ $bill->due_date->translatedFormat('l, d F Y') }}</strong></p>
                            @if($bill->group->bank_account_no)
                                <p><i class="fa-solid fa-credit-card text-slate-400 mr-1.5"></i> Rekening: <strong>{{ $bill->group->bank_name }} - {{ $bill->group->bank_account_no }} (a.n {{ $bill->group->bank_account_name }})</strong></p>
                            @endif
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-2">
                            @if($isPending)
                                <span class="text-xs text-amber-700 font-semibold flex items-center gap-1.5">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    <span>Bukti sudah dikirim, mohon tunggu verifikasi Bendahara</span>
                                </span>
                            @else
                                <button type="button" onclick="openUploadModal('{{ $bill->id }}', '{{ $bill->group->name }}', '{{ $bill->round->round_number }}', '{{ number_format($bill->total_amount, 0, ',', '.') }}', '{{ $bill->group->bank_name }}', '{{ $bill->group->bank_account_no }}', '{{ $bill->group->bank_account_name }}')" class="btn-pkk px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center gap-1.5">
                                    <i class="fa-solid fa-upload"></i>
                                    <span>Bayar / Kirim Bukti Transfer</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 2 Column: Kelompok Arisan Saya & Riwayat Kemenangan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kelompok yang Diikuti (Span 2) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-emerald-600"></i>
                    <span>Kelompok Arisan yang Saya Ikuti</span>
                </h2>

                <div class="space-y-4">
                    @forelse($memberships as $m)
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-900">{{ $m->group->name }}</h3>
                                        @if($m->has_won)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1">
                                                <i class="fa-solid fa-crown text-amber-500"></i>
                                                <span>Sudah Menang (Putaran {{ $m->wonRound ? $m->wonRound->round_number : '-' }})</span>
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Belum Menang (Ikut Kocokan)
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Iuran: <strong>Rp {{ number_format($m->group->contribution_amount, 0, ',', '.') }}</strong> / bulan • Total Peserta: {{ $m->group->members->count() }} Orang
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('transparency', $m->group) }}" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 transition">
                                        Daftar Putaran
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Ibu belum didaftarkan pada kelompok arisan manapun. Silakan hubungi Ketua PKK.</p>
                    @endforelse
                </div>
            </div>

            <!-- Riwayat Pembayaran Lunas -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <span>Riwayat Pembayaran Iuran Saya</span>
                </h2>

                @if($paymentHistory->isEmpty())
                    <p class="text-xs text-slate-400 py-3 text-center">Belum ada riwayat pembayaran yang tercatat.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($paymentHistory as $ph)
                            <div class="py-3 flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $ph->group->name }} (Putaran {{ $ph->round->round_number }})</p>
                                    <p class="text-slate-500 text-[11px]">{{ $ph->verified_at ? $ph->verified_at->translatedFormat('d F Y H:i') : $ph->paid_at->translatedFormat('d F Y') }} • Metode: {{ ucfirst($ph->payment_method) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-extrabold text-emerald-700">Rp {{ number_format($ph->total_amount, 0, ',', '.') }}</p>
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">LUNAS</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Jadwal & Kemenangan Saya -->
        <div class="space-y-6">

            <!-- Card: Kemenangan Arisan Saya -->
            @if($myWonRounds->isNotEmpty())
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl border border-amber-200 p-6 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-md shadow-amber-500/30 mb-3">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                    <h3 class="text-base font-black text-amber-950">Kemenangan Arisan Ibu 🌸</h3>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                        Alhamdulillah, nama Ibu telah keluar sebagai pemenang arisan!
                    </p>

                    <div class="mt-4 space-y-3">
                        @foreach($myWonRounds as $mwr)
                            <div class="p-3 bg-white/80 backdrop-blur rounded-2xl border border-amber-200">
                                <p class="text-xs font-bold text-slate-900">{{ $mwr->group->name }}</p>
                                <p class="text-[11px] text-slate-600">Putaran Ke-{{ $mwr->round_number }} • {{ $mwr->draw_date->translatedFormat('d F Y') }}</p>
                                <p class="text-sm font-extrabold text-emerald-700 mt-1">Total Hadiah: Rp {{ number_format($mwr->winning_amount, 0, ',', '.') }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold {{ $mwr->prize_disbursed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                                    {{ $mwr->prize_disbursed ? 'Dana Sudah Diserahkan' : 'Menunggu Penyerahan Uang' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Card: Pertemuan & Arisan Terdekat -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fa-regular fa-calendar-check text-emerald-600"></i>
                    <span>Jadwal Pertemuan Terdekat</span>
                </h3>

                <div class="space-y-3">
                    @forelse($upcomingEvents as $ue)
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">{{ $ue->group->name }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Putaran {{ $ue->round_number }}</span>
                            </div>
                            <p class="text-xs font-semibold text-slate-700 mt-1">
                                <i class="fa-regular fa-clock text-emerald-600 mr-1"></i> {{ $ue->draw_date->translatedFormat('l, d F Y') }}
                            </p>
                            @if($ue->host)
                                <p class="text-[11px] text-slate-500 mt-1">
                                    <i class="fa-solid fa-house-chimney text-slate-400 mr-1"></i> Tuan Rumah: <strong>{{ $ue->host->name }}</strong>
                                </p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-3 text-center">Belum ada agenda pertemuan terdekat.</p>
                    @endforelse
                </div>
            </div>

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
                <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maksimal 3MB)</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="user_notes" rows="2" placeholder="Contoh: Transfer lewat rekening BCA atas nama Bpk. Slamet (Suami)" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
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
