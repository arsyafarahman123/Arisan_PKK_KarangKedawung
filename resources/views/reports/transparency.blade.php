@extends('layouts.app')

@section('title', 'Papan Transparansi Arisan — ' . $group->name)

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="relative bg-gradient-to-r from-rose-600 via-pink-600 to-amber-500 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-rose-900/10 overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-white/10 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-md mb-2">
                    <i class="fa-solid fa-eye text-white"></i>
                    <span>Papan Transparansi Terbuka</span>
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Arisan {{ $group->name }}
                </h1>
                <p class="text-rose-100 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Daftar pemenang dan status keanggotaan dapat dipantau bersama oleh seluruh anggota secara adil, jujur, dan barokah.
                </p>
            </div>

            <!-- Group Switcher -->
            <div class="bg-white/15 backdrop-blur-md p-2 rounded-2xl border border-white/20">
                <form action="{{ route('transparency') }}" method="GET">
                    <select name="group" onchange="window.location.href='/transparency/' + this.value" class="px-3 py-2 rounded-xl bg-white text-slate-800 text-xs font-bold focus:outline-none">
                        @foreach($allGroups as $ag)
                            <option value="{{ $ag->id }}" {{ $ag->id === $group->id ? 'selected' : '' }}>
                                {{ $ag->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Iuran Bulanan</span>
            <p class="text-xl font-black text-emerald-700 mt-1">Rp {{ number_format($group->contribution_amount, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Per anggota / putaran</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Uang Cair</span>
            <p class="text-xl font-black text-slate-800 mt-1">Rp {{ number_format($group->total_pot, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Didapat oleh pemenang</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Sudah Menang</span>
            <p class="text-xl font-black text-amber-600 mt-1">{{ $members->where('has_won', true)->count() }} Orang</p>
            <p class="text-[10px] text-amber-700 font-semibold mt-0.5">Telah keluar kocokan</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Belum Menang</span>
            <p class="text-xl font-black text-emerald-600 mt-1">{{ $members->where('has_won', false)->count() }} Orang</p>
            <p class="text-[10px] text-emerald-700 font-semibold mt-0.5">Mengikuti kocokan berikutnya</p>
        </div>
    </div>

    <!-- 2 Column: Riwayat Putaran Pemenang & Daftar Status Anggota -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Column 1: Riwayat Putaran & Pemenang -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <span>Riwayat Putaran & Pemenang</span>
            </h3>

            <div class="divide-y divide-slate-100">
                @foreach($rounds as $r)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-slate-900">Putaran Ke-{{ $r->round_number }}</span>
                                <span class="text-slate-400">•</span>
                                <span class="text-slate-500">{{ $r->draw_date->translatedFormat('d M Y') }}</span>
                            </div>
                            @if($r->host)
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    <i class="fa-solid fa-house-chimney text-teal-600 mr-1"></i> Tuan Rumah: {{ $r->host->name }}
                                </p>
                            @endif
                        </div>

                        <div class="text-right">
                            @if($r->winner)
                                <div class="flex items-center gap-1.5 justify-end font-extrabold text-amber-700">
                                    <i class="fa-solid fa-crown text-amber-500"></i>
                                    <span>{{ $r->winner->name }}</span>
                                </div>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold {{ $r->prize_disbursed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                                    {{ $r->prize_disbursed ? 'Dana Diserahkan' : 'Menunggu Serah Terima' }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Belum Dikocok</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Column 2: Status Seluruh Anggota (Sudah / Belum Menang) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-users text-emerald-600"></i>
                <span>Status Keanggotaan Arisan ({{ $members->count() }} Anggota)</span>
            </h3>

            <div class="divide-y divide-slate-100">
                @foreach($members as $idx => $m)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-slate-400 font-bold">#{{ $m->fixed_order_number ?? ($idx + 1) }}</span>
                            <div>
                                <p class="font-bold text-slate-800">{{ $m->user->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $m->user->address ?? 'KarangKedawung' }}</p>
                            </div>
                        </div>

                        <div>
                            @if($m->has_won)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1">
                                    <i class="fa-solid fa-crown text-amber-500"></i>
                                    <span>Sudah Menang (Putaran {{ $m->wonRound ? $m->wonRound->round_number : '-' }})</span>
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Belum Menang
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
