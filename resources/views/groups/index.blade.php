@extends('layouts.app')

@section('title', 'Kelompok Arisan — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-emerald-600"></i>
                <span>Kelompok Arisan PKK</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola seluruh kelompok arisan, nominal iuran, aturan, dan jadwal pertemuan</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('groups.create') }}" class="btn-pkk px-4 py-2.5 rounded-xl font-bold text-xs shadow-md flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-sm"></i>
                <span>Buat Kelompok Baru</span>
            </a>
        </div>
    </div>

    <!-- Status Tabs Filter -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto">
        <a href="{{ route('groups.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ !$status ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Semua Kelompok
        </a>
        <a href="{{ route('groups.index', ['status' => 'active']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $status === 'active' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-circle-play text-[11px] {{ $status === 'active' ? 'text-white' : 'text-emerald-600' }}"></i>
            <span>Sedang Berjalan</span>
        </a>
        <a href="{{ route('groups.index', ['status' => 'draft']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $status === 'draft' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-file-pen text-[11px] {{ $status === 'draft' ? 'text-white' : 'text-slate-500' }}"></i>
            <span>Belum Mulai</span>
        </a>
        <a href="{{ route('groups.index', ['status' => 'completed']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $status === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-trophy text-[11px] {{ $status === 'completed' ? 'text-white' : 'text-amber-500' }}"></i>
            <span>Selesai</span>
        </a>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($groups as $group)
            @php
                $totalRounds = $group->rounds->count();
                $completedRounds = $group->rounds->where('status', 'completed')->count();
                $progressPct = $totalRounds > 0 ? round(($completedRounds / $totalRounds) * 100) : 0;
            @endphp
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                <div class="p-6">
                    
                    <!-- Header Badges -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1 {{ $group->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($group->status === 'completed' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700') }}">
                            @if($group->status === 'active')
                                <i class="fa-solid fa-circle text-[6px] text-emerald-600"></i>
                                <span>Sedang Berjalan</span>
                            @elseif($group->status === 'completed')
                                <i class="fa-solid fa-trophy text-[9px] text-purple-700"></i>
                                <span>Selesai</span>
                            @else
                                <i class="fa-solid fa-file-pen text-[9px] text-slate-600"></i>
                                <span>Belum Mulai</span>
                            @endif
                        </span>
                        <span class="text-[11px] font-bold text-slate-400">
                            {{ $group->period_type === 'monthly' ? 'Bulanan' : ($group->period_type === 'biweekly' ? '2 Mingguan' : 'Mingguan') }}
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-base font-extrabold text-slate-900 leading-snug">
                        <a href="{{ route('groups.show', $group) }}" class="hover:text-emerald-700 transition">
                            {{ $group->name }}
                        </a>
                    </h3>
                    @if($group->description)
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $group->description }}</p>
                    @endif

                    <!-- Details Card -->
                    <div class="mt-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Iuran per Putaran:</span>
                            <span class="font-black text-emerald-700">Rp {{ number_format($group->contribution_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Uang Hadiah:</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($group->total_pot, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Sistem Pemenang:</span>
                            <span class="font-bold text-slate-700 flex items-center gap-1">
                                @if($group->winner_determination === 'lottery')
                                    <i class="fa-solid fa-dice text-slate-400"></i>
                                    <span>Kocokan Acak</span>
                                @else
                                    <i class="fa-solid fa-arrow-down-1-9 text-slate-400"></i>
                                    <span>Urutan Tetap</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jumlah Anggota:</span>
                            <span class="font-bold text-slate-700">{{ $group->members_count }} / {{ $group->max_members }} Orang</span>
                        </div>
                    </div>

                    <!-- Progress Putaran -->
                    @if($totalRounds > 0)
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                                <span>Progres Arisan</span>
                                <span>Putaran {{ $completedRounds }} dari {{ $totalRounds }} Selesai ({{ $progressPct }}%)</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $progressPct }}%"></div>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Footer Action Buttons -->
                <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ route('groups.show', $group) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 flex items-center gap-1">
                        <span>Kelola Detail</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>

                    @if($totalRounds === 0 && $group->members_count >= 2)
                        <form action="{{ route('groups.generate_schedule', $group) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700 transition shadow-sm">
                                Generate Jadwal
                            </button>
                        </form>
                    @endif
                </div>

            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-3xl border border-slate-200 p-8">
                <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-base font-bold text-slate-700">Belum Ada Kelompok Arisan</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Buat kelompok arisan pertama Ibu sekarang juga dengan mudah.</p>
                <a href="{{ route('groups.create') }}" class="btn-pkk mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold shadow">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Buat Kelompok Baru</span>
                </a>
            </div>
        @endforelse
    </div>

    <div class="pt-4">
        {{ $groups->links() }}
    </div>

</div>
@endsection
