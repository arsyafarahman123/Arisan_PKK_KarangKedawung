@extends('layouts.app')

@section('title', 'Log Aktivitas Admin — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                <span>Log Aktivitas Sistem</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Catatan audit riwayat aksi pengurus: penambahan anggota, verifikasi pembayaran, pengocokan, dan reschedule</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <form action="{{ route('activity_logs.index') }}" method="GET" class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari aktivitas atau nama pengurus..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Pengurus</th>
                        <th class="py-3 px-4">Aksi</th>
                        <th class="py-3 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3 px-4">Alamat IP</th>
                        <th class="py-3 px-4 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $log->user ? $log->user->name : 'Sistem Otomatis' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 font-mono">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800">{{ $log->description }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">{{ $log->ip_address ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-right text-slate-500 text-[11px] whitespace-nowrap">
                                {{ $log->created_at->translatedFormat('d M Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-400">Belum ada catatan aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
