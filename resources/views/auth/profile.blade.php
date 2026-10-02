@extends('layouts.app')

@section('title', 'Profil Saya — Arisan PKK KarangKedawung')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-36 h-36 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md text-white flex items-center justify-center text-2xl font-bold border border-white/30 shadow-inner">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white border border-white/30 mb-1">
                    {{ $user->isAdmin() ? 'Pengurus / Admin PKK' : 'Anggota PKK KarangKedawung' }}
                </span>
                <h1 class="text-2xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                <p class="text-emerald-100 text-xs mt-0.5"><i class="fa-brands fa-whatsapp mr-1"></i> {{ $user->phone }}</p>
            </div>
        </div>
    </div>

    <!-- Edit Profile Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <h2 class="text-base font-bold text-slate-800 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-user-pen text-emerald-600"></i>
            <span>Perbarui Data Pribadi</span>
        </h2>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp *</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email (Opsional)</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili / RT / RW</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Contoh: RT 02 / RW 03" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Ganti Kata Sandi (Kosongkan jika tidak ingin diubah)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Kata Sandi Baru</label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi baru" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm font-medium">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="btn-pkk px-6 py-2.5 rounded-xl font-bold text-sm shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
