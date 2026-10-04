@extends('layouts.app')

@section('title', 'Dashboard Pengurus — Arisan PKK KarangKedawung')

@section('content')
<div class="space-y-6">

    <!-- Welcome Hero Banner -->
    <div class="relative bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-emerald-900/10 overflow-hidden">
        <!-- Decorative shapes -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-20 top-2 w-32 h-32 rounded-full bg-teal-400/20 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-emerald-100 text-xs font-semibold backdrop-blur-md mb-3 border border-white/20">
                    <i class="fa-solid fa-calendar-day"></i>
                    <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, Ibu {{ auth()->user()->name }}
                </h1>
                <p class="text-emerald-100 text-sm mt-1 max-w-xl leading-relaxed">
                    Sistem siap membantu pengelolaan arisan PKK KarangKedawung agar makin tertib, guyub rukun, dan transparan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('groups.create') }}" class="px-4 py-2.5 rounded-xl bg-white text-emerald-800 font-bold text-xs shadow-md hover:bg-emerald-50 hover:shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-emerald-600 text-sm"></i>
                    <span>Buat Kelompok Baru</span>
                </a>
                <a href="{{ route('members.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-800/80 text-white font-bold text-xs border border-white/20 hover:bg-emerald-800 transition flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                    <span>Tambah Anggota</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards (4 Cards) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- Card 1: Kelompok -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kelompok Arisan</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-black text-slate-800">{{ $totalGroups }}</p>
                <p class="text-xs text-emerald-600 font-semibold mt-0.5"><i class="fa-solid fa-circle-check"></i> {{ $activeGroups }} Kelompok Aktif</p>
            </div>
        </div>

        <!-- Card 2: Anggota -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Anggota PKK</span>
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-black text-slate-800">{{ $totalMembers }}</p>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Ibu-ibu terdaftar aktif</p>
            </div>
        </div>

        <!-- Card 3: Kas Terkumpul -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kas Masuk</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-black text-slate-800">Rp {{ number_format($totalMoneyCollected, 0, ',', '.') }}</p>
                <p class="text-xs text-amber-600 font-semibold mt-0.5">+ Denda Rp {{ number_format($totalPenalties, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Card 4: Perlu Verifikasi -->
        <div class="bg-white rounded-2xl p-5 border {{ $pendingVerificationsCount > 0 ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80' }} shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Perlu Verifikasi</span>
                <div class="w-10 h-10 rounded-xl {{ $pendingVerificationsCount > 0 ? 'bg-rose-100 text-rose-600 animate-pulse' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-black {{ $pendingVerificationsCount > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $pendingVerificationsCount }}</p>
                <p class="text-xs {{ $pendingVerificationsCount > 0 ? 'text-rose-600 font-bold' : 'text-slate-500 font-medium' }} mt-0.5">
                    {{ $pendingVerificationsCount > 0 ? 'Segera verifikasi bukti transfer' : 'Semua pembayaran lunas' }}
                </p>
            </div>
        </div>

    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Active Arisan & Pending Verifications (Span 2) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Section: Putaran Arisan yang Sedang Berjalan -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-spinner text-emerald-600"></i>
                            <span>Putaran Arisan Berjalan</span>
                        </h2>
                        <p class="text-xs text-slate-500">Kelompok yang sedang dalam tahap pembayaran / siap kocok</p>
                    </div>
                    <a href="{{ route('groups.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                </div>

                @if($activeRounds->isEmpty())
                    <div class="text-center py-8 text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                        <p class="text-xs font-medium">Belum ada putaran arisan yang sedang berjalan.</p>
                        <a href="{{ route('groups.index') }}" class="mt-2 inline-block text-xs font-bold text-emerald-600">Generate jadwal kelompok sekarang</a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($activeRounds as $round)
                            @php
                                $paidCount = $round->paid_members_count;
                                $totalCount = $round->total_members_count;
                                $pct = $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0;
                            @endphp
                            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-emerald-50/40 border border-slate-200 hover:border-emerald-300 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Putaran Ke-{{ $round->round_number }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-800">{{ $round->group->name }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-slate-600">
                                            <span><i class="fa-regular fa-calendar-check text-amber-500 mr-1"></i> Jatuh Tempo: <strong>{{ $round->due_date->translatedFormat('d M Y') }}</strong></span>
                                            <span><i class="fa-solid fa-gift text-rose-500 mr-1"></i> Kocokan: <strong>{{ $round->draw_date->translatedFormat('d M Y') }}</strong></span>
                                            @if($round->host)
                                                <span><i class="fa-solid fa-house-chimney text-teal-600 mr-1"></i> Tuan Rumah: <strong>{{ $round->host->name }}</strong></span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <a href="{{ route('rounds.show', $round) }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                                            Detail & Iuran
                                        </a>
                                        <a href="{{ route('draws.index', $round) }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white text-xs font-bold shadow-md hover:from-rose-600 hover:to-pink-700 transition flex items-center gap-1.5 animate-bounce">
                                            <i class="fa-solid fa-dice text-sm"></i>
                                            <span>Kocok Arisan!</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Progress Bar Lunas -->
                                <div class="mt-4 pt-3 border-t border-slate-200/60">
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Progres Pembayaran Iuran</span>
                                        <span class="{{ $paidCount === $totalCount ? 'text-emerald-600' : 'text-slate-700' }}">{{ $paidCount }} dari {{ $totalCount }} Lunas ({{ $pct }}%)</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Section: Pembayaran Menunggu Verifikasi -->
            @if($pendingPayments->isNotEmpty())
                <div class="bg-white rounded-3xl border border-rose-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-bold text-rose-800 flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left text-rose-600"></i>
                                <span>Verifikasi Pembayaran Masuk ({{ $pendingPayments->count() }})</span>
                            </h2>
                            <p class="text-xs text-slate-500">Ibu-ibu yang baru saja mengirimkan bukti transfer</p>
                        </div>
                        <a href="{{ route('payments.index', ['status' => 'pending_verification']) }}" class="text-xs font-bold text-rose-700 hover:text-rose-800">
                            Lihat Semua Bukti
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($pendingPayments as $p)
                            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm flex-shrink-0 border border-rose-200">
                                        {{ substr($p->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">{{ $p->user->name }}</p>
                                        <p class="text-[11px] text-slate-500">
                                            {{ $p->group->name }} • Putaran {{ $p->round->round_number }} • 
                                            <span class="font-bold text-emerald-700">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</span>
                                        </p>
                                        @if($p->user_notes)
                                            <p class="text-[11px] text-slate-600 bg-slate-50 rounded-lg p-1.5 mt-1 italic border border-slate-200/60">
                                                "{{ $p->user_notes }}"
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0 self-end sm:self-center">
                                    @if($p->proof_image)
                                        <a href="{{ asset('storage/' . $p->proof_image) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition flex items-center gap-1">
                                            <i class="fa-solid fa-image"></i>
                                            <span>Lihat Struk</span>
                                        </a>
                                    @endif

                                    <form action="{{ route('payments.verify', $p) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Setujui</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Section: Tagihan Belum Lunas / Tunggakan & Direct WA -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-user-clock text-amber-500"></i>
                            <span>Anggota Belum Bayar Putaran Berjalan</span>
                        </h2>
                        <p class="text-xs text-slate-500">Kirimkan pengingat WhatsApp dengan 1 kali klik santun</p>
                    </div>
                </div>

                @if($unpaidOverduePayments->isEmpty())
                    <div class="text-center py-6 text-emerald-600 font-medium text-xs bg-emerald-50 rounded-2xl">
                        <i class="fa-solid fa-circle-check text-xl mb-1"></i>
                        <p>Alhamdulillah, semua tagihan pada putaran berjalan sudah lunas!</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($unpaidOverduePayments as $up)
                            @php
                                $isLate = $up->payment_status === 'late';
                            @endphp
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-800">{{ $up->user->name }}</span>
                                        @if($isLate)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Telat (+Rp {{ number_format($up->penalty_amount, 0, ',', '.') }})</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Belum Bayar</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $up->group->name }} • Putaran {{ $up->round->round_number }} • Total: <strong class="text-slate-800">Rp {{ number_format($up->total_amount, 0, ',', '.') }}</strong>
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <!-- Quick Cash Payment Button (Cash on spot) -->
                                    <form action="{{ route('payments.quick_cash', $up) }}" method="POST" onsubmit="return confirm('Konfirmasi terima uang tunai Rp {{ number_format($up->total_amount, 0, ',', '.') }} dari {{ $up->user->name }}?')">
                                        @csrf
                                        <button type="submit" title="Terima Tunai di Tempat" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1">
                                            <i class="fa-solid fa-hand-holding-dollar text-emerald-600"></i>
                                            <span>Terima Tunai</span>
                                        </button>
                                    </form>

                                    <!-- Quick WhatsApp Reminder Button -->
                                    <a href="{{ route('notifications.send_reminder', ['payment' => $up, 'type' => $isLate ? 'late_penalty' : 'reminder_h3']) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-sm">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                        <span>Kirim WA</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Upcoming Meetings, Recent Winners, Activity Log -->
        <div class="space-y-6">

            <!-- Card: Jadwal Pertemuan / Kocok Terdekat -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fa-regular fa-calendar-star text-amber-500"></i>
                    <span>Jadwal Kocokan Terdekat</span>
                </h3>

                @if($upcomingDraws->isEmpty())
                    <p class="text-xs text-slate-400 py-3 text-center">Belum ada jadwal terdekat.</p>
                @else
                    <div class="space-y-3">
                        @foreach($upcomingDraws as $ud)
                            <div class="p-3.5 rounded-2xl bg-amber-50/50 border border-amber-200/70">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-amber-900">{{ $ud->group->name }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-200 text-amber-900">Putaran {{ $ud->round_number }}</span>
                                </div>
                                <p class="text-xs font-bold text-slate-800 mt-1">
                                    <i class="fa-solid fa-clock text-amber-600 mr-1"></i> {{ $ud->draw_date->translatedFormat('l, d F Y') }}
                                </p>
                                <p class="text-[11px] text-slate-600 mt-1">
                                    <i class="fa-solid fa-house-user text-slate-400 mr-1"></i> Tuan Rumah: <strong>{{ $ud->host ? $ud->host->name : 'Belum Ditentukan' }}</strong>
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('calendar') }}" class="mt-4 block text-center text-xs font-bold text-emerald-700 hover:text-emerald-800 py-2 rounded-xl bg-slate-50 border border-slate-200 transition">
                    Buka Kalender Arisan Lengkap
                </a>
            </div>

            <!-- Card: Riwayat Pemenang Terbaru -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    <span>Pemenang Arisan Terbaru</span>
                </h3>

                @if($recentWinners->isEmpty())
                    <p class="text-xs text-slate-400 py-3 text-center">Belum ada pemenang yang tercatat.</p>
                @else
                    <div class="space-y-3">
                        @foreach($recentWinners as $rw)
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                                <div class="overflow-hidden flex-grow">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $rw->winner->name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $rw->group->name }} (Putaran {{ $rw->round_number }})</p>
                                    <p class="text-[11px] font-bold text-emerald-700">Rp {{ number_format($rw->winning_amount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Card: Log Aktivitas -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-slate-400"></i>
                    <span>Aktivitas Sistem Terbaru</span>
                </h3>

                <div class="space-y-3">
                    @foreach($recentActivities as $act)
                        <div class="text-[11px] text-slate-600 pb-2 border-b border-slate-100 last:border-0 last:pb-0">
                            <p class="font-semibold text-slate-800">{{ $act->description }}</p>
                            <span class="text-[10px] text-slate-400">{{ $act->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
