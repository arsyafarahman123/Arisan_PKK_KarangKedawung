@extends('layouts.app')

@section('title', 'Ubah Kelompok — ' . $group->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('groups.show', $group) }}" class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Detail Kelompok</span>
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ubah Data Kelompok Arisan</h1>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('groups.update', $group) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Informasi Dasar Kelompok -->
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs">1</span>
                    <span>Informasi Kelompok & Status</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kelompok Arisan *</label>
                        <input type="text" name="name" value="{{ old('name', $group->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Kelompok *</label>
                        <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                            <option value="draft" {{ old('status', $group->status) == 'draft' ? 'selected' : '' }}>Belum Mulai (Draft)</option>
                            <option value="active" {{ old('status', $group->status) == 'active' ? 'selected' : '' }}>Sedang Berjalan (Aktif)</option>
                            <option value="completed" {{ old('status', $group->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Iuran per Putaran (Rp) *</label>
                        <input type="number" name="contribution_amount" value="{{ old('contribution_amount', $group->contribution_amount) }}" min="1000" step="1000" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Pertemuan *</label>
                        <select name="period_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                            <option value="monthly" {{ old('period_type', $group->period_type) == 'monthly' ? 'selected' : '' }}>Bulanan (Setiap 1 Bulan)</option>
                            <option value="biweekly" {{ old('period_type', $group->period_type) == 'biweekly' ? 'selected' : '' }}>2 Mingguan</option>
                            <option value="weekly" {{ old('period_type', $group->period_type) == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Kuota Anggota *</label>
                        <input type="number" name="max_members" value="{{ old('max_members', $group->max_members) }}" min="2" max="100" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai *</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $group->start_date->format('Y-m-d')) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Catatan</label>
                        <textarea name="description" rows="2" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-xs font-medium">{{ old('description', $group->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Aturan Pemenang & Denda -->
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs">2</span>
                    <span>Aturan Pemenang & Denda</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sistem Penentuan Pemenang *</label>
                        <select name="winner_determination" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                            <option value="lottery" {{ old('winner_determination', $group->winner_determination) == 'lottery' ? 'selected' : '' }}>Pengocokan Acak (Undian / Wheel)</option>
                            <option value="fixed_order" {{ old('winner_determination', $group->winner_determination) == 'fixed_order' ? 'selected' : '' }}>Urutan Tetap</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Syarat Ikut Kocok</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="checkbox" name="only_paid_can_win" value="1" id="only_paid_can_win" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500" {{ old('only_paid_can_win', $group->only_paid_can_win) ? 'checked' : '' }}>
                            <label for="only_paid_can_win" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                Hanya anggota yang sudah LUNAS yang boleh ikut dikocok
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Denda per Hari (Rp)</label>
                        <input type="number" name="late_fee_per_day" value="{{ old('late_fee_per_day', $group->late_fee_per_day) }}" min="0" step="500" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Toleransi Hari (Grace Period)</label>
                        <input type="number" name="grace_period_days" value="{{ old('grace_period_days', $group->grace_period_days) }}" min="0" max="30" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>
                </div>
            </div>

            <!-- Section 3: Rekening -->
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs">3</span>
                    <span>Informasi Rekening</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bank / Kas</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $group->bank_name) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $group->bank_account_no) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Atas Nama</label>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $group->bank_account_name) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('groups.show', $group) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                    Batal
                </a>
                <button type="submit" class="btn-pkk px-6 py-2.5 rounded-xl font-bold text-xs shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
