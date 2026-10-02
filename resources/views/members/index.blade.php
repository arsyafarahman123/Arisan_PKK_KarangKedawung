@extends('layouts.app')

@section('title', 'Daftar Anggota — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-users text-emerald-600"></i>
                <span>Manajemen Anggota PKK</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Data ibu-ibu anggota PKK, nomor WhatsApp, alamat, dan status keanggotaan</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('members.create') }}" class="btn-pkk px-4 py-2.5 rounded-xl font-bold text-xs shadow-md flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-sm"></i>
                <span>Tambah Anggota Baru</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form action="{{ route('members.index') }}" method="GET" class="flex-grow w-full sm:w-auto relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama anggota, nomor WhatsApp, atau RT/RW..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
        </form>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <a href="{{ route('members.index') }}" class="px-3 py-2 rounded-xl text-xs font-bold {{ $status === null ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                Semua
            </a>
            <a href="{{ route('members.index', ['status' => '1']) }}" class="px-3 py-2 rounded-xl text-xs font-bold {{ $status === '1' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                Aktif
            </a>
            <a href="{{ route('members.index', ['status' => '0']) }}" class="px-3 py-2 rounded-xl text-xs font-bold {{ $status === '0' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-600' }}">
                Nonaktif
            </a>
        </div>
    </div>

    <!-- Members Grid / Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($members as $member)
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    
                    <!-- Card Top -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-800 font-black flex items-center justify-center text-base border border-emerald-200 flex-shrink-0">
                                {{ substr($member->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">{{ $member->name }}</h3>
                                <p class="text-[11px] text-slate-500 font-mono mt-0.5"><i class="fa-brands fa-whatsapp text-emerald-600"></i> {{ $member->phone }}</p>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $member->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <!-- Address & Roles -->
                    <div class="space-y-1.5 text-xs text-slate-600 mt-3 pt-3 border-t border-slate-100">
                        <p><i class="fa-solid fa-location-dot text-slate-400 mr-1.5 w-3.5"></i> {{ $member->address ?? 'Alamat belum diisi' }}</p>
                        <p><i class="fa-solid fa-envelope text-slate-400 mr-1.5 w-3.5"></i> {{ $member->email ?? '-' }}</p>
                    </div>

                    <!-- Groups joined -->
                    <div class="mt-3 pt-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Kelompok Arisan:</span>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @forelse($member->groupMemberships as $gm)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $gm->has_won ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $gm->group->name }}
                                    @if($gm->has_won)
                                        <i class="fa-solid fa-crown text-amber-500 ml-0.5"></i>
                                    @endif
                                </span>
                            @empty
                                <span class="text-[10px] text-slate-400 italic">Belum terdaftar di kelompok manapun</span>
                            @endforelse
                        </div>
                    </div>

                </div>

                <!-- Footer Actions -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="https://wa.me/{{ $member->formatted_phone }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span>Chat WA</span>
                    </a>

                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('members.edit', $member) }}" class="p-1.5 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition text-xs">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        <form action="{{ route('members.toggle_status', $member) }}" method="POST" onsubmit="return confirm('Ubah status keaktifan anggota {{ $member->name }}?')">
                            @csrf
                            <button type="submit" class="p-1.5 {{ $member->is_active ? 'text-rose-500 hover:text-rose-700' : 'text-emerald-500 hover:text-emerald-700' }} rounded-lg hover:bg-slate-100 transition text-xs" title="{{ $member->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                <i class="fa-solid {{ $member->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-3xl border border-slate-200 p-8">
                <i class="fa-solid fa-user-xmark text-4xl text-slate-300 mb-2"></i>
                <h3 class="text-sm font-bold text-slate-700">Tidak Ada Data Anggota</h3>
                <p class="text-xs text-slate-400 mt-1">Gunakan kata kunci lain atau tambahkan anggota baru.</p>
            </div>
        @endforelse
    </div>

    <div class="pt-4">
        {{ $members->links() }}
    </div>

</div>
@endsection
