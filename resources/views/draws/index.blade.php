@extends('layouts.app')

@section('title', 'Kocok Arisan Putaran Ke-' . $round->round_number . ' — ' . $group->name)

@section('content')
<div class="space-y-6">

    <!-- Header Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('rounds.show', $round) }}" class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Detail Putaran</span>
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-dice text-rose-500"></i>
                <span>Ruang Pengocokan Arisan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ $group->name }} • Putaran Ke-{{ $round->round_number }}</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold {{ $round->status === 'completed' ? 'bg-purple-100 text-purple-800' : 'bg-rose-100 text-rose-800 animate-pulse' }} flex items-center gap-1.5">
                @if($round->status === 'completed')
                    <i class="fa-solid fa-trophy"></i>
                    <span>Pengocokan Selesai</span>
                @else
                    <i class="fa-solid fa-dice"></i>
                    <span>Siap Dikocok</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Jackpot Prize Banner -->
    <div class="relative bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-amber-900/10 overflow-hidden text-center">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-white/15 blur-xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-44 h-44 rounded-full bg-white/15 blur-xl pointer-events-none"></div>

        <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-md mb-2">
            <i class="fa-solid fa-gift mr-1"></i> Total Uang Arisan Putaran Ini
        </span>
        <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-white drop-shadow-sm">
            Rp {{ number_format($round->winning_amount ?? $totalPrize, 0, ',', '.') }}
        </h2>
        <p class="text-amber-100 text-xs mt-2 font-medium">
            Pengocokan disaksikan bersama • Sah & Transparan untuk seluruh anggota PKK
        </p>
    </div>

    @if($round->status === 'completed' && $round->winner)
        <!-- Winner Announcement Card -->
        <div class="bg-white rounded-3xl border-2 border-amber-300 shadow-xl p-6 sm:p-8 text-center relative overflow-hidden">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-tr from-amber-400 to-yellow-300 text-white shadow-lg shadow-amber-400/40 mb-4 text-3xl animate-bounce">
                <i class="fa-solid fa-crown"></i>
            </div>
            
            <h3 class="text-xs font-black text-amber-600 uppercase tracking-widest">Alhamdulillah, Selamat Kepada:</h3>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-1 mb-2">{{ $round->winner->name }}</h2>
            <p class="text-sm font-semibold text-slate-600 font-mono"><i class="fa-brands fa-whatsapp text-emerald-600"></i> {{ $round->winner->phone }}</p>
            
            <div class="max-w-md mx-auto mt-5 p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs text-amber-950 space-y-1">
                <p>Uang Arisan: <strong class="text-emerald-700 font-black text-sm">Rp {{ number_format($round->winning_amount, 0, ',', '.') }}</strong></p>
                <p>Status Pencairan: 
                    <strong class="{{ $round->prize_disbursed ? 'text-emerald-700' : 'text-amber-800' }} flex items-center gap-1 inline-flex">
                        @if($round->prize_disbursed)
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>Dana Sudah Diserahkan</span>
                        @else
                            <i class="fa-solid fa-clock text-amber-600"></i>
                            <span>Menunggu Penyerahan Dana</span>
                        @endif
                    </strong>
                </p>
                @if($round->disbursement_notes)
                    <p class="italic text-[11px] text-slate-600 pt-1">Catatan: "{{ $round->disbursement_notes }}"</p>
                @endif
            </div>

            <!-- Direct WA Congratulations Button -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="https://wa.me/{{ $round->winner->formatted_phone }}?text={{ rawurlencode("Assalamu'alaikum Ibu {$round->winner->name}, Selamat atas penetapan pemenang pada Arisan PKK {$group->name} Putaran ke-{$round->round_number}.") }}" target="_blank" class="btn-pkk px-5 py-2.5 rounded-xl font-bold text-xs shadow flex items-center gap-2">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                    <span>Kirim Notifikasi WA ke Pemenang</span>
                </a>

                @if(!$round->prize_disbursed && auth()->user()->isAdmin())
                    <button onclick="document.getElementById('disburseModal').classList.remove('hidden')" class="btn-rose px-5 py-2.5 rounded-xl font-bold text-xs shadow flex items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Catat Penyerahan / Pencairan Dana</span>
                    </button>
                @endif
            </div>
        </div>
    @else
        <!-- Drawing Room Arena (Interactive Lucky Wheel / Animation) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Interactive Spinning Canvas / Wheel (Span 2) -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col items-center justify-center text-center">
                
                <h3 class="text-base font-bold text-slate-800 mb-1 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-dharmachakra text-emerald-600"></i>
                    <span>Roda Undian Arisan PKK</span>
                </h3>
                <p class="text-xs text-slate-500 mb-6">Klik tombol kocok untuk mengundi pemenang secara acak dan transparan</p>

                <!-- Canvas Roulette Wheel -->
                <div class="relative flex items-center justify-center mb-6">
                    <!-- Pointer Arrow -->
                    <div class="absolute -top-4 z-20 text-rose-600 text-3xl filter drop-shadow">
                        <i class="fa-solid fa-caret-down"></i>
                    </div>
                    <canvas id="wheelCanvas" width="340" height="340" class="rounded-full shadow-xl border-4 border-white bg-slate-50"></canvas>
                </div>

                <!-- Animated Display of Name during spin -->
                <div id="spinningNameBox" class="min-h-[48px] flex items-center justify-center mb-4">
                    <span id="spinningNameText" class="text-lg font-extrabold text-slate-700 tracking-wide">
                        Siap untuk mengundi...
                    </span>
                </div>

                <!-- Draw Action Form -->
                @if(auth()->user()->isAdmin())
                    <form id="drawForm" action="{{ route('draws.process', $round) }}" method="POST">
                        @csrf
                        <input type="hidden" name="winner_id" id="winner_id_input" value="">
                        
                        <button type="button" id="startSpinBtn" onclick="startSpinning()" class="btn-rose px-8 py-3.5 rounded-2xl font-black text-sm shadow-xl shadow-rose-600/30 hover:scale-105 transition flex items-center gap-2">
                            <i class="fa-solid fa-dice text-lg"></i>
                            <span>PUTAR / KOCOK SEKARANG!</span>
                        </button>
                    </form>
                @else
                    <p class="text-xs text-slate-500 bg-slate-50 px-4 py-2 rounded-xl">Hanya Admin/Pengurus PKK yang dapat menekan tombol kocok.</p>
                @endif

            </div>

            <!-- Eligible Candidates List (Right Column) -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Peserta yang Ikut Kocokan</h3>
                        <p class="text-[11px] text-slate-500">Anggota belum menang</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        {{ $canDrawCandidates->count() }} Orang
                    </span>
                </div>

                @if($group->only_paid_can_win)
                    <p class="text-[10px] text-amber-800 bg-amber-50 p-2.5 rounded-xl border border-amber-200 flex items-start gap-1.5">
                        <i class="fa-solid fa-circle-exclamation text-amber-600 mt-0.5"></i>
                        <span>Aturan kelompok: Hanya anggota yang <strong>sudah Lunas</strong> iuran pada putaran ini yang dimasukkan ke roda undian.</span>
                    </p>
                @endif

                <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto pr-1">
                    @forelse($eligibleCandidates as $cand)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-[10px]">
                                    {{ substr($cand['name'], 0, 1) }}
                                </div>
                                <span class="font-bold text-slate-800">{{ $cand['name'] }}</span>
                            </div>
                            <div>
                                @if($cand['is_paid'])
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-rose-100 text-rose-800">Belum Lunas</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Tidak ada peserta yang memenuhi syarat.</p>
                    @endforelse
                </div>
            </div>

        </div>
    @endif

    <!-- Past Winners History in this Group -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>Daftar Pemenang Arisan {{ $group->name }}</span>
        </h3>

        @if($pastWinners->isEmpty())
            <p class="text-xs text-slate-400 py-4 text-center">Belum ada pemenang yang tercatat pada kelompok ini.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($pastWinners as $pw)
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-sm">
                            #{{ $pw->round_number }}
                        </div>
                        <div class="overflow-hidden flex-grow">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $pw->winner->name }}</p>
                            <p class="text-[10px] text-slate-500">{{ $pw->draw_date->translatedFormat('d M Y') }}</p>
                            <span class="text-[11px] font-extrabold text-emerald-700">Rp {{ number_format($pw->winning_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<!-- Modal Catat Pencairan Dana -->
<div id="disburseModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Catat Serah Terima Uang Arisan</h3>
            <button onclick="document.getElementById('disburseModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('draws.disburse', $round) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Penyerahan Uang *</label>
                <input type="datetime-local" name="disbursed_at" value="{{ date('Y-m-d\TH:i') }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Foto Bukti Serah Terima / Kwitansi (Opsional)</label>
                <input type="file" name="disbursement_proof" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Penyerahan</label>
                <textarea name="disbursement_notes" rows="2" placeholder="Contoh: Diserahkan tunai di Balai RW 03 disaksikan seluruh anggota PKK" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:border-emerald-500 outline-none"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('disburseModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-pkk px-5 py-2 rounded-xl font-bold text-xs shadow">
                    Simpan Pencairan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Confetti effect if just won
    @if(session('winner_modal'))
        confetti({
            particleCount: 150,
            spread: 90,
            origin: { y: 0.6 }
        });
    @endif

    // ROULETTE WHEEL JS LOGIC
    const candidates = @json($canDrawCandidates);
    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas ? canvas.getContext('2d') : null;

    const colors = [
        '#10b981', '#f43f5e', '#3b82f6', '#f59e0b', '#8b5cf6',
        '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#6366f1'
    ];

    let startAngle = 0;
    let arc = candidates.length > 0 ? (2 * Math.PI) / candidates.length : 0;
    let spinTimeout = null;
    let spinArcStart = 10;
    let spinTime = 0;
    let spinTimeTotal = 0;

    function drawRouletteWheel() {
        if (!canvas || candidates.length === 0) return;

        const outsideRadius = 150;
        const textRadius = 105;
        const insideRadius = 35;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 3;

        ctx.font = 'bold 11px Plus Jakarta Sans, sans-serif';

        for (let i = 0; i < candidates.length; i++) {
            const angle = startAngle + i * arc;
            ctx.fillStyle = colors[i % colors.length];

            ctx.beginPath();
            ctx.arc(170, 170, outsideRadius, angle, angle + arc, false);
            ctx.arc(170, 170, insideRadius, angle + arc, angle, true);
            ctx.stroke();
            ctx.fill();

            ctx.save();
            ctx.fillStyle = '#ffffff';
            ctx.translate(
                170 + Math.cos(angle + arc / 2) * textRadius,
                170 + Math.sin(angle + arc / 2) * textRadius
            );
            ctx.rotate(angle + arc / 2 + Math.PI / 2);
            const text = candidates[i].name.split(' ')[0] + ' ' + (candidates[i].name.split(' ')[1] || '');
            ctx.fillText(text, -ctx.measureText(text).width / 2, 0);
            ctx.restore();
        }

        // Center circle
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(170, 170, insideRadius - 5, 0, 2 * Math.PI, false);
        ctx.fill();
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 2;
        ctx.stroke();

        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 10px Plus Jakarta Sans';
        ctx.fillText('PKK', 160, 173);
    }

    if (canvas && candidates.length > 0) {
        drawRouletteWheel();
    }

    function rotateWheel() {
        spinTime += 30;
        if (spinTime >= spinTimeTotal) {
            stopRotateWheel();
            return;
        }
        const spinAngle = spinArcStart - easeOut(spinTime, 0, spinArcStart, spinTimeTotal);
        startAngle += (spinAngle * Math.PI / 180);
        drawRouletteWheel();

        // Update spinning name text
        const degrees = startAngle * 180 / Math.PI + 90;
        const arcd = arc * 180 / Math.PI;
        const index = Math.floor((360 - degrees % 360) % 360 / arcd);
        if (candidates[index]) {
            document.getElementById('spinningNameText').innerText = candidates[index].name + '...';
        }

        spinTimeout = setTimeout(rotateWheel, 30);
    }

    function stopRotateWheel() {
        clearTimeout(spinTimeout);
        const degrees = startAngle * 180 / Math.PI + 90;
        const arcd = arc * 180 / Math.PI;
        const index = Math.floor((360 - degrees % 360) % 360 / arcd);
        const winner = candidates[index];

        document.getElementById('spinningNameText').innerHTML = 'PEMENANG: <strong class="text-rose-600 font-black">' + winner.name + '</strong>';

        // Trigger Confetti!
        confetti({
            particleCount: 200,
            spread: 100,
            origin: { y: 0.6 }
        });

        // Submit form after 1.5 seconds so winner sees celebration
        setTimeout(() => {
            document.getElementById('winner_id_input').value = winner.id;
            document.getElementById('drawForm').submit();
        }, 1500);
    }

    function easeOut(t, b, c, d) {
        const ts = (t /= d) * t;
        const tc = ts * t;
        return b + c * (tc + -3 * ts + 3 * t);
    }

    function startSpinning() {
        if (candidates.length === 0) {
            alert('Tidak ada peserta yang memenuhi syarat kocok!');
            return;
        }

        document.getElementById('startSpinBtn').disabled = true;
        document.getElementById('startSpinBtn').classList.add('opacity-50', 'cursor-not-allowed');

        spinArcStart = Math.random() * 10 + 10;
        spinTime = 0;
        spinTimeTotal = Math.random() * 3000 + 4000;
        rotateWheel();
    }
</script>
@endpush
@endsection
