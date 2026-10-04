@extends('layouts.app')

@section('title', 'Laporan Keuangan & Kas — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                <span>Laporan Keuangan & Kas Arisan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Rekapitulasi keuangan, total kas masuk, denda keterlambatan, dan riwayat penyerahan dana pemenang</p>
        </div>

        @if($selectedGroup)
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.print', $selectedGroup) }}" target="_blank" class="btn-pkk px-4 py-2.5 rounded-xl font-bold text-xs shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Laporan Resmi</span>
                </a>
                <a href="{{ route('reports.export_csv', $selectedGroup) }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i>
                    <span>Export Excel/CSV</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Group Selector Dropdown -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between gap-4">
        <form action="{{ route('reports.index') }}" method="GET" class="flex items-center gap-3 w-full sm:w-auto">
            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Pilih Kelompok:</span>
            <select name="group_id" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:border-emerald-500 outline-none">
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ $selectedGroup && $selectedGroup->id == $g->id ? 'selected' : '' }}>
                        {{ $g->name }} ({{ $g->status === 'active' ? 'Aktif' : 'Selesai/Draft' }})
                    </option>
                @endforeach
            </select>
        </form>

        <a href="{{ route('transparency', $selectedGroup) }}" class="text-xs font-bold text-rose-600 hover:underline flex items-center gap-1">
            <i class="fa-solid fa-eye"></i>
            <span>Buku Transparansi Publik</span>
        </a>
    </div>

    @if($selectedGroup)
        <!-- 4 Summary Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Target Kas Keseluruhan</span>
                <p class="text-xl font-black text-slate-800 mt-1">Rp {{ number_format($financialSummary['totalExpected'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">{{ $selectedGroup->rounds->count() }} Putaran Arisan</p>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Total Kas Masuk (Lunas)</span>
                <p class="text-xl font-black text-emerald-700 mt-1">Rp {{ number_format($financialSummary['totalCollected'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">Tercatat lunas di sistem</p>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Kas Denda Terkumpul</span>
                <p class="text-xl font-black text-amber-600 mt-1">Rp {{ number_format($financialSummary['totalPenalties'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-amber-700 font-semibold mt-0.5">Denda keterlambatan bayar</p>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Uang Diserahkan ke Pemenang</span>
                <p class="text-xl font-black text-purple-700 mt-1">Rp {{ number_format($financialSummary['totalDisbursed'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-purple-600 font-semibold mt-0.5">Pencairan hadiah pemenang</p>
            </div>
        </div>

        <!-- Table 1: Rekapitulasi per Putaran -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 overflow-hidden">
            <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-list-ol text-emerald-600"></i>
                <span>Rekapitulasi Keuangan per Putaran</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Putaran</th>
                            <th class="py-3 px-4">Tgl Kocokan</th>
                            <th class="py-3 px-4">Pemenang</th>
                            <th class="py-3 px-4">Kas Masuk (Lunas)</th>
                            <th class="py-3 px-4">Tunggakan (Belum)</th>
                            <th class="py-3 px-4">Denda</th>
                            <th class="py-3 px-4">Status Pencairan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($financialSummary['rounds'] as $round)
                            @php
                                $paidSum = $round->payments->where('payment_status', 'paid')->sum('amount');
                                $unpaidSum = $round->payments->whereIn('payment_status', ['unpaid', 'late'])->sum('amount');
                                $penaltySum = $round->payments->where('payment_status', 'paid')->sum('penalty_amount');
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">Putaran {{ $round->round_number }}</td>
                                <td class="py-3.5 px-4 text-slate-600">{{ $round->draw_date->translatedFormat('d M Y') }}</td>
                                <td class="py-3.5 px-4 font-extrabold text-amber-700">
                                    {{ $round->winner ? $round->winner->name : '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-emerald-700">Rp {{ number_format($paidSum, 0, ',', '.') }}</td>
                                <td class="py-3.5 px-4 font-bold {{ $unpaidSum > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    Rp {{ number_format($unpaidSum, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-amber-600">Rp {{ number_format($penaltySum, 0, ',', '.') }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1 w-max {{ $round->prize_disbursed ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        @if($round->prize_disbursed)
                                            <i class="fa-solid fa-check text-[9px] text-emerald-600"></i>
                                            <span>Diserahkan</span>
                                        @else
                                            <span>Belum Cair</span>
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table 2: Daftar Tunggakan Iuran (Arrears) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 overflow-hidden">
            <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                <span>Laporan Tunggakan & Belum Bayar ({{ $arrears->count() }})</span>
            </h3>

            @if($arrears->isEmpty())
                <p class="text-xs text-emerald-600 py-3 text-center bg-emerald-50 rounded-2xl font-semibold">
                    Alhamdulillah! Tidak ada tunggakan iuran pada kelompok ini.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Nama Anggota</th>
                                <th class="py-3 px-4">No. WA</th>
                                <th class="py-3 px-4">Putaran</th>
                                <th class="py-3 px-4">Jatuh Tempo</th>
                                <th class="py-3 px-4">Pokok</th>
                                <th class="py-3 px-4">Denda</th>
                                <th class="py-3 px-4">Total Tagihan</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($arrears as $arr)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $arr->user->name }}</td>
                                    <td class="py-3.5 px-4 font-mono text-slate-500">{{ $arr->user->phone }}</td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-800">Putaran {{ $arr->round->round_number }}</td>
                                    <td class="py-3.5 px-4 text-slate-600">{{ $arr->due_date->translatedFormat('d M Y') }}</td>
                                    <td class="py-3.5 px-4">Rp {{ number_format($arr->amount, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 font-bold text-rose-600">Rp {{ number_format($arr->penalty_amount, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 font-black text-rose-700">Rp {{ number_format($arr->total_amount, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('notifications.send_reminder', ['payment' => $arr, 'type' => 'late_penalty']) }}" target="_blank" class="px-3 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm inline-flex items-center gap-1">
                                            <i class="fa-brands fa-whatsapp"></i>
                                            <span>Kirim WA</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
