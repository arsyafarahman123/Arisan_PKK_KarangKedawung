@extends('layouts.app')

@section('title', $group->name . ' — Detail Kelompok Arisan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('groups.index') }}" class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Daftar Kelompok</span>
            </a>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $group->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold flex items-center gap-1 {{ $group->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($group->status === 'completed' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700') }}">
                    @if($group->status === 'active')
                        <i class="fa-solid fa-circle text-[6px] text-emerald-600"></i>
                        <span>Sedang Berjalan</span>
                    @elseif($group->status === 'completed')
                        <i class="fa-solid fa-trophy text-[10px] text-purple-700"></i>
                        <span>Selesai</span>
                    @else
                        <i class="fa-solid fa-file-pen text-[10px] text-slate-600"></i>
                        <span>Belum Mulai</span>
                    @endif
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $group->description ?? 'Arisan rutin PKK KarangKedawung' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.print', $group) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Laporan</span>
            </a>
            <a href="{{ route('reports.export_csv', $group) }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export Excel/CSV</span>
            </a>
            <a href="{{ route('groups.edit', $group) }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Ubah</span>
            </a>
            <form action="{{ route('groups.destroy', $group) }}" method="POST" onsubmit="return confirm('Apakah Ibu yakin ingin menghapus kelompok arisan ini? Seluruh data putaran dan pembayaran terkait akan ikut terhapus.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition flex items-center gap-1.5 border border-rose-200">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- 4 Stats Highlight Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Iuran per Anggota</span>
            <p class="text-xl font-black text-emerald-700 mt-1">Rp {{ number_format($group->contribution_amount, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Periode: {{ ucfirst($group->period_type) }}</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Uang Hadiah</span>
            <p class="text-xl font-black text-slate-800 mt-1">Rp {{ number_format($group->total_pot, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">{{ $activeMembers->count() }} Anggota x Rp {{ number_format($group->contribution_amount, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Progres Putaran</span>
            <p class="text-xl font-black text-slate-800 mt-1">{{ $completedRounds }} / {{ $totalRounds }} Selesai</p>
            <p class="text-[10px] text-emerald-600 font-bold mt-0.5">
                {{ $currentRound ? 'Putaran ' . $currentRound->round_number . ' Berjalan' : ($completedRounds === $totalRounds && $totalRounds > 0 ? 'Semua Selesai' : 'Belum Mulai') }}
            </p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Kas Diterima</span>
            <p class="text-xl font-black text-emerald-700 mt-1">Rp {{ number_format($totalCollected, 0, ',', '.') }}</p>
            <p class="text-[10px] text-amber-600 font-semibold mt-0.5">+ Denda Rp {{ number_format($totalPenalties, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Interactive Tabs Navigation -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        <div class="flex items-center border-b border-slate-200 px-6 pt-4 gap-6 overflow-x-auto">
            <button onclick="switchTab('tab-rounds')" id="btn-tab-rounds" class="tab-btn pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-700 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-regular fa-calendar-check"></i>
                <span>Jadwal & Putaran ({{ $totalRounds }})</span>
            </button>
            <button onclick="switchTab('tab-members')" id="btn-tab-members" class="tab-btn pb-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-users"></i>
                <span>Anggota Kelompok ({{ $activeMembers->count() }})</span>
            </button>
            <button onclick="switchTab('tab-cash')" id="btn-tab-cash" class="tab-btn pb-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-sack-dollar"></i>
                <span>Rekap Kas & Pembayaran</span>
            </button>
            <button onclick="switchTab('tab-rules')" id="btn-tab-rules" class="tab-btn pb-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-scale-balanced"></i>
                <span>Aturan & Rekening</span>
            </button>
        </div>

        <div class="p-6">

            <!-- TAB 1: JADWAL & PUTARAN -->
            <div id="tab-rounds" class="tab-content space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Daftar Seluruh Putaran Arisan</h3>
                        <p class="text-xs text-slate-500">Jadwal jatuh tempo iuran, tanggal pengocokan, dan tuan rumah</p>
                    </div>

                    @if($totalRounds === 0)
                        <form action="{{ route('groups.generate_schedule', $group) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-pkk px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center gap-1.5">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                <span>Generate Jadwal Otomatis</span>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('groups.generate_schedule', $group) }}" method="POST" onsubmit="return confirm('Apakah Ibu ingin me-reset dan men-generate ulang jadwal putaran ini?')">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1">
                                <i class="fa-solid fa-arrows-rotate text-emerald-600"></i>
                                <span>Reset Jadwal</span>
                            </button>
                        </form>
                    @endif
                </div>

                @if($group->rounds->isEmpty())
                    <div class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-2"></i>
                        <p class="text-xs font-bold text-slate-700">Jadwal Belum Dibuat</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pastikan sudah ada minimal 2 anggota sebelum men-generate jadwal.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4">Putaran</th>
                                    <th class="py-3 px-4">Jatuh Tempo</th>
                                    <th class="py-3 px-4">Tgl Kocokan</th>
                                    <th class="py-3 px-4">Tuan Rumah</th>
                                    <th class="py-3 px-4">Pemenang</th>
                                    <th class="py-3 px-4">Status Iuran</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($group->rounds as $round)
                                    @php
                                        $paid = $round->paid_members_count;
                                        $total = $round->total_members_count;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition {{ $round->status === 'ongoing' ? 'bg-emerald-50/30' : '' }}">
                                        <td class="py-3.5 px-4 font-bold">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] {{ $round->status === 'completed' ? 'bg-purple-100 text-purple-800' : ($round->status === 'ongoing' ? 'bg-emerald-100 text-emerald-800 font-black' : 'bg-slate-100 text-slate-600') }}">
                                                Putaran {{ $round->round_number }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-medium">{{ $round->due_date->translatedFormat('d M Y') }}</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-800">{{ $round->draw_date->translatedFormat('d M Y') }}</td>
                                        <td class="py-3.5 px-4">
                                            @if($round->host)
                                                <span class="font-semibold text-slate-800"><i class="fa-solid fa-house-chimney text-teal-600 mr-1"></i> {{ $round->host->name }}</span>
                                            @else
                                                <span class="text-slate-400 italic">Belum ditentukan</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($round->winner)
                                                <div class="flex items-center gap-1.5 font-extrabold text-amber-700">
                                                    <i class="fa-solid fa-crown text-amber-500"></i>
                                                    <span>{{ $round->winner->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-slate-400 italic">Belum dikocok</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 font-medium">
                                            <span class="{{ $paid === $total && $total > 0 ? 'text-emerald-700 font-bold' : 'text-slate-600' }}">
                                                {{ $paid }}/{{ $total }} Lunas
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                            <a href="{{ route('rounds.show', $round) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                                Detail
                                            </a>
                                            <a href="{{ route('draws.index', $round) }}" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-sm">
                                                <i class="fa-solid fa-dice mr-0.5"></i> {{ $round->status === 'completed' ? 'Hasil' : 'Kocok' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- TAB 2: ANGGOTA KELOMPOK -->
            <div id="tab-members" class="tab-content hidden space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Daftar Anggota dalam Kelompok Ini</h3>
                        <p class="text-xs text-slate-500">Status kemenangan, preferensi notifikasi, dan nomor antrean</p>
                    </div>

                    <button onclick="document.getElementById('addMemberModal').classList.remove('hidden')" class="btn-pkk px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center gap-1.5 self-start">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Tambah Anggota ke Kelompok</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Urut</th>
                                <th class="py-3 px-4">Nama Anggota</th>
                                <th class="py-3 px-4">No WhatsApp</th>
                                <th class="py-3 px-4">Status Menang</th>
                                <th class="py-3 px-4">Notifikasi</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($group->members as $idx => $gm)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-400">#{{ $gm->fixed_order_number ?? ($idx + 1) }}</td>
                                    <td class="py-3.5 px-4 font-bold text-slate-800">
                                        {{ $gm->user->name }}
                                        @if($gm->user->id === $group->admin_id)
                                            <span class="ml-1 px-2 py-0.2 text-[9px] bg-emerald-100 text-emerald-800 rounded font-bold">Admin/Ketua</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 font-mono">{{ $gm->user->phone }}</td>
                                    <td class="py-3.5 px-4">
                                        @if($gm->has_won)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1 w-fit">
                                                <i class="fa-solid fa-crown text-amber-500"></i>
                                                <span>Sudah Menang (Putaran {{ $gm->wonRound ? $gm->wonRound->round_number : '-' }})</span>
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Belum Menang
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="capitalize text-slate-600"><i class="fa-brands fa-whatsapp text-emerald-600 mr-1"></i> {{ $gm->notification_channel }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        @if($gm->user->id !== $group->admin_id)
                                            <form action="{{ route('members.remove_from_group', ['group' => $group, 'member' => $gm->user]) }}" method="POST" onsubmit="return confirm('Keluarkan {{ $gm->user->name }} dari kelompok ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs p-1">
                                                    <i class="fa-solid fa-user-minus"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: REKAP KAS -->
            <div id="tab-cash" class="tab-content hidden space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                        <span class="text-xs font-bold text-emerald-800">Total Iuran Terkumpul</span>
                        <p class="text-xl font-black text-emerald-900 mt-1">Rp {{ number_format($totalCollected, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100">
                        <span class="text-xs font-bold text-amber-800">Total Denda Masuk</span>
                        <p class="text-xl font-black text-amber-900 mt-1">Rp {{ number_format($totalPenalties, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-purple-50 border border-purple-100">
                        <span class="text-xs font-bold text-purple-800">Total Dana Diserahkan ke Pemenang</span>
                        <p class="text-xl font-black text-purple-900 mt-1">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-2">
                    <a href="{{ route('reports.print', $group) }}" target="_blank" class="btn-pkk px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center gap-1.5">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak Laporan Keuangan Resmi</span>
                    </a>
                </div>
            </div>

            <!-- TAB 4: ATURAN & REKENING -->
            <div id="tab-rules" class="tab-content hidden space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Aturan Kelompok</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-slate-200">
                                <span class="text-slate-500">Sistem Pemenang:</span>
                                <span class="font-bold text-slate-800">{{ $group->winner_determination === 'lottery' ? 'Kocokan Acak' : 'Urutan Tetap' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200">
                                <span class="text-slate-500">Syarat Kocok:</span>
                                <span class="font-bold text-slate-800">{{ $group->only_paid_can_win ? 'Hanya anggota yang sudah Lunas' : 'Semua anggota belum menang' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200">
                                <span class="text-slate-500">Denda per Hari:</span>
                                <span class="font-bold text-rose-600">Rp {{ number_format($group->late_fee_per_day, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-slate-500">Batas Toleransi:</span>
                                <span class="font-bold text-slate-800">{{ $group->grace_period_days }} Hari setelah jatuh tempo</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rekening Tujuan Pembayaran</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-slate-200">
                                <span class="text-slate-500">Bank:</span>
                                <span class="font-bold text-slate-800">{{ $group->bank_name ?? 'Belum diatur' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200">
                                <span class="text-slate-500">No. Rekening:</span>
                                <span class="font-mono font-bold text-slate-800">{{ $group->bank_account_no ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-slate-500">Atas Nama:</span>
                                <span class="font-bold text-slate-800">{{ $group->bank_account_name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Modal Tambah Anggota ke Kelompok -->
<div id="addMemberModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Tambah Anggota ke Kelompok</h3>
            <button onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('members.add_to_group', $group) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Anggota PKK *</label>
                <select name="user_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
                    <option value="">-- Pilih Nama Anggota --</option>
                    @foreach($allNonMembers as $anm)
                        <option value="{{ $anm->id }}">{{ $anm->name }} ({{ $anm->phone }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Channel Notifikasi</label>
                <select name="notification_channel" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
                    <option value="whatsapp">WhatsApp (Rekomendasi)</option>
                    <option value="app">Notifikasi Web / Aplikasi</option>
                    <option value="email">Email</option>
                </select>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-pkk px-5 py-2 rounded-xl font-bold text-xs shadow">
                    Tambahkan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('border-emerald-600', 'text-emerald-700');
            el.classList.add('border-transparent', 'text-slate-500');
        });

        document.getElementById(tabId).classList.remove('hidden');
        const activeBtn = document.getElementById('btn-' + tabId);
        activeBtn.classList.remove('border-transparent', 'text-slate-500');
        activeBtn.classList.add('border-emerald-600', 'text-emerald-700');
    }
</script>
@endpush
@endsection
