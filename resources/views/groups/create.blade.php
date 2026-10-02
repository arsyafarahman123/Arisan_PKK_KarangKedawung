@extends('layouts.app')

@section('title', 'Buat Kelompok Arisan Baru — Arisan PKK KarangKedawung')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('groups.index') }}" class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Daftar Kelompok</span>
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buat Kelompok Arisan Baru</h1>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('groups.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Informasi Dasar Kelompok -->
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs">1</span>
                    <span>Informasi Kelompok</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kelompok Arisan *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Arisan Guyub Melati PKK RW 03" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Iuran per Putaran (Rp) *</label>
                        <input type="number" name="contribution_amount" value="{{ old('contribution_amount', 100000) }}" min="1000" step="1000" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                        <p class="text-[10px] text-slate-400 mt-1">Nominal yang wajib dibayar tiap anggota per pertemuan</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Pertemuan *</label>
                        <select name="period_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                            <option value="monthly" {{ old('period_type') == 'monthly' ? 'selected' : '' }}>Bulanan (Setiap 1 Bulan)</option>
                            <option value="biweekly" {{ old('period_type') == 'biweekly' ? 'selected' : '' }}>2 Mingguan</option>
                            <option value="weekly" {{ old('period_type') == 'weekly' ? 'selected' : '' }}>Mingguan (Setiap Minggu)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Jumlah Anggota (Kuota) *</label>
                        <input type="number" name="max_members" value="{{ old('max_members', 10) }}" min="2" max="100" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai Arisan *</label>
                        <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Deskripsi Kelompok</label>
                        <textarea name="description" rows="2" placeholder="Tuliskan catatan tambahan mengenai kelompok arisan ini..." class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-xs font-medium">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Aturan Pemenang & Denda -->
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs">2</span>
                    <span>Aturan Pemenang & Denda Keterlambatan</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sistem Penentuan Pemenang *</label>
                        <select name="winner_determination" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                            <option value="lottery" {{ old('winner_determination') == 'lottery' ? 'selected' : '' }}>🎲 Pengocokan Acak (Lottery / Wheel)</option>
                            <option value="fixed_order" {{ old('winner_determination') == 'fixed_order' ? 'selected' : '' }}>🔢 Urutan Tetap (Sesuai Nomor Antrean)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Syarat Ikut Kocok Arisan</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="checkbox" name="only_paid_can_win" value="1" id="only_paid_can_win" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500" {{ old('only_paid_can_win', '1') == '1' ? 'checked' : '' }}>
                            <label for="only_paid_can_win" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                Hanya anggota yang sudah LUNAS iuran yang boleh ikut dikocok
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Denda Keterlambatan per Hari (Rp)</label>
                        <input type="number" name="late_fee_per_day" value="{{ old('late_fee_per_day', 2000) }}" min="0" step="500" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                        <p class="text-[10px] text-slate-400 mt-1">Isi 0 jika tidak ada denda keterlambatan</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Batas Toleransi Keterlambatan (Hari)</label>
                        <input type="number" name="grace_period_days" value="{{ old('grace_period_days', 3) }}" min="0" max="30" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                        <p class="text-[10px] text-slate-400 mt-1">Hari toleransi setelah tanggal jatuh tempo sebelum kena denda</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Rekening Pembayaran -->
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs">3</span>
                    <span>Informasi Rekening Pembayaran Iuran</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bank / Kas</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', 'Bank BCA') }}" placeholder="Contoh: BCA / Kas PKK" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_account_no" value="{{ old('bank_account_no') }}" placeholder="Contoh: 8830192837" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Atas Nama (Bendahara)</label>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="Contoh: Ibu Endang Rahayu" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>
                </div>
            </div>

            <!-- Section 4: Pilih Anggota Awal -->
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-3 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs">4</span>
                    <span>Pilih Anggota Awal (Opsional)</span>
                </h3>
                <p class="text-xs text-slate-500 mb-3">Centang ibu-ibu yang ingin langsung dimasukkan ke dalam kelompok ini:</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-56 overflow-y-auto p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    @forelse($allMembers as $m)
                        <label class="flex items-center gap-2.5 p-2 rounded-xl bg-white border border-slate-200 hover:border-emerald-300 cursor-pointer text-xs">
                            <input type="checkbox" name="member_ids[]" value="{{ $m->id }}" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <p class="font-bold text-slate-800">{{ $m->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $m->phone }}</p>
                            </div>
                        </label>
                    @empty
                        <p class="col-span-full text-xs text-slate-400 py-3 text-center">Belum ada anggota terdaftar. Ibu bisa menambahkan anggota nanti.</p>
                    @endforelse
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('groups.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                    Batal
                </a>
                <button type="submit" class="btn-pkk px-6 py-2.5 rounded-xl font-bold text-xs shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Kelompok Arisan</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
