@extends('layouts.app')

@section('title', 'Kalender Arisan — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Header & Month Selector -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-regular fa-calendar-days text-emerald-600"></i>
                <span>Kalender Arisan & Pertemuan PKK</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Jadwal jatuh tempo iuran, tanggal pengocokan, dan tuan rumah arisan</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Filter Kelompok -->
            <form action="{{ route('calendar') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">
                <select name="group_id" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 focus:border-emerald-500 outline-none shadow-sm">
                    <option value="">-- Semua Kelompok --</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </form>

            <!-- Month Navigator Buttons -->
            @php
                $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
                $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
            @endphp
            <div class="flex items-center bg-white rounded-xl border border-slate-200 p-1 shadow-sm">
                <a href="{{ route('calendar', ['month' => $prevMonth, 'group_id' => $groupId]) }}" class="p-1.5 px-2.5 text-slate-600 hover:text-emerald-700 hover:bg-slate-50 rounded-lg text-xs font-bold transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                <span class="px-3 text-xs font-bold text-slate-800">
                    {{ $currentMonth->translatedFormat('F Y') }}
                </span>
                <a href="{{ route('calendar', ['month' => $nextMonth, 'group_id' => $groupId]) }}" class="p-1.5 px-2.5 text-slate-600 hover:text-emerald-700 hover:bg-slate-50 rounded-lg text-xs font-bold transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar Legend -->
    <div class="flex flex-wrap items-center gap-4 bg-white p-3.5 rounded-2xl border border-slate-200 text-xs shadow-sm">
        <span class="font-bold text-slate-500 text-[11px] uppercase">Keterangan Warna:</span>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
            <span class="font-medium text-slate-700">Jatuh Tempo Iuran</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <span class="font-medium text-slate-700">Hari Pengocokan & Pertemuan PKK</span>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 sm:p-6 overflow-hidden">
        
        <!-- Day Names Header -->
        <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2 text-center text-xs font-extrabold text-slate-500 uppercase tracking-wider">
            <div class="py-2 text-rose-600">Min</div>
            <div class="py-2">Sen</div>
            <div class="py-2">Sel</div>
            <div class="py-2">Rab</div>
            <div class="py-2">Kam</div>
            <div class="py-2">Jum</div>
            <div class="py-2 text-emerald-700">Sab</div>
        </div>

        @php
            $startDayOfWeek = $currentMonth->copy()->startOfMonth()->dayOfWeek; // 0 (Sun) - 6 (Sat)
            $daysInMonth = $currentMonth->daysInMonth;
            $todayStr = now()->format('Y-m-d');
        @endphp

        <div class="grid grid-cols-7 gap-1 sm:gap-2">
            <!-- Empty cells before first day of month -->
            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="min-h-[80px] sm:min-h-[105px] p-2 bg-slate-50/50 rounded-2xl border border-dashed border-slate-100 opacity-40"></div>
            @endfor

            <!-- Days of month -->
            @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $dateStr = $currentMonth->copy()->day($d)->format('Y-m-d');
                    $isToday = $dateStr === $todayStr;
                    $dayEvents = collect($events)->where('date', $dateStr);
                @endphp
                <div class="min-h-[80px] sm:min-h-[105px] p-1.5 sm:p-2 rounded-2xl border {{ $isToday ? 'border-emerald-500 bg-emerald-50/20 shadow-sm ring-2 ring-emerald-200' : 'border-slate-100 bg-white hover:border-slate-300' }} transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold {{ $isToday ? 'w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center' : 'text-slate-800' }}">
                            {{ $d }}
                        </span>
                        @if($isToday)
                            <span class="text-[9px] font-extrabold text-emerald-700 hidden sm:inline">Hari Ini</span>
                        @endif
                    </div>

                    <!-- Events on this date -->
                    <div class="space-y-1 mt-1 overflow-y-auto max-h-[60px]">
                        @foreach($dayEvents as $ev)
                            @if($ev['type'] === 'due')
                                <a href="{{ route('rounds.show', $ev['round']) }}" class="block p-1 rounded-lg bg-amber-100/80 hover:bg-amber-200 border border-amber-200 text-[10px] font-bold text-amber-950 truncate transition" title="{{ $ev['title'] }}">
                                    ⏳ Iuran: {{ $ev['round']->group->name }}
                                </a>
                            @else
                                <a href="{{ route('draws.index', $ev['round']) }}" class="block p-1 rounded-lg bg-emerald-100/90 hover:bg-emerald-200 border border-emerald-300 text-[10px] font-extrabold text-emerald-950 truncate transition" title="{{ $ev['title'] }}">
                                    🎲 Kocok: {{ $ev['round']->group->name }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endfor
        </div>

    </div>

    <!-- Agenda List for this month -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-list-check text-emerald-600"></i>
            <span>Daftar Agenda Pertemuan Bulan {{ $currentMonth->translatedFormat('F Y') }}</span>
        </h3>

        @if(empty($events))
            <p class="text-xs text-slate-400 py-4 text-center">Tidak ada jadwal arisan yang tercatat pada bulan ini.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($rounds as $r)
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900">{{ $r->group->name }} (Putaran Ke-{{ $r->round_number }})</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $r->status === 'completed' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $r->status === 'completed' ? 'Selesai' : 'Akan Datang' }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-600 space-y-1">
                            <p><i class="fa-regular fa-calendar-check text-amber-600 mr-1.5"></i> Jatuh Tempo Iuran: <strong>{{ $r->due_date->translatedFormat('l, d F Y') }}</strong></p>
                            <p><i class="fa-solid fa-dice text-emerald-600 mr-1.5"></i> Pertemuan & Kocokan: <strong>{{ $r->draw_date->translatedFormat('l, d F Y') }}</strong></p>
                            @if($r->host)
                                <p><i class="fa-solid fa-house-chimney text-teal-600 mr-1.5"></i> Tuan Rumah: <strong>{{ $r->host->name }}</strong> ({{ $r->host_location ?? 'Balai RW' }})</p>
                            @endif
                        </div>
                        <div class="pt-2 flex justify-end">
                            <a href="{{ route('rounds.show', $r) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">
                                Buka Detail Putaran →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
