@extends('layouts.app')

@section('title', 'Ubah Data Anggota — ' . $member->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Breadcrumb -->
    <div>
        <a href="{{ route('members.index') }}" class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1 mb-1">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Kembali ke Daftar Anggota</span>
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ubah Data Anggota PKK</h1>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('members.update', $member) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Ibu *</label>
                <input type="text" name="name" value="{{ old('name', $member->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp *</label>
                    <input type="text" name="phone" value="{{ old('phone', $member->phone) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email (Opsional)</label>
                    <input type="email" name="email" value="{{ old('email', $member->email) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili / RT / RW</label>
                <input type="text" name="address" value="{{ old('address', $member->address) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Peran Akun *</label>
                    <select name="role" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                        <option value="member" {{ old('role', $member->role) == 'member' ? 'selected' : '' }}>Anggota Biasa</option>
                        <option value="admin" {{ old('role', $member->role) == 'admin' ? 'selected' : '' }}>Pengurus / Admin PKK</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Keaktifan *</label>
                    <select name="is_active" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
                        <option value="1" {{ old('is_active', $member->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $member->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" placeholder="Masukkan password baru jika ingin diganti" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-sm font-medium">
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('members.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
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
