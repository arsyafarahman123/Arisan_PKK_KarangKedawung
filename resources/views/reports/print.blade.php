<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Resmi Arisan PKK — {{ $group->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
        }
        body {
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #0f172a;
        }
        .page {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 15px;
            margin: 4px 0;
            color: #334155;
        }
        .header p {
            font-size: 11px;
            margin: 0;
            color: #64748b;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            font-size: 12px;
            margin-bottom: 25px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-transform: uppercase;
        }
        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 35px;
            font-size: 12px;
        }
        .total-table {
            width: 300px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            font-size: 12px;
            page-break-inside: avoid;
        }
        .sign-col {
            text-align: center;
            width: 200px;
        }
        .sign-space {
            height: 70px;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background: #16a34a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }
        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; padding: 20px; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Dokumen / Simpan PDF</button>
    </div>

    <div class="page">
        
        <!-- Header Kop Surat PKK -->
        <div class="header">
            <h1>Pemberdayaan Kesejahteraan Keluarga (PKK)</h1>
            <h2>Laporan Rekapitulasi Kas & Arisan</h2>
            <p>Desa KarangKedawung • Buku Catatan Resmi Pengurus</p>
        </div>

        <!-- Info Kelompok -->
        <div class="info-grid">
            <div>
                <strong>Nama Kelompok:</strong> {{ $group->name }}<br>
                <strong>Nominal Iuran:</strong> Rp {{ number_format($group->contribution_amount, 0, ',', '.') }} / Putaran<br>
                <strong>Periode:</strong> {{ ucfirst($group->period_type) }}
            </div>
            <div>
                <strong>Jumlah Peserta:</strong> {{ $members->count() }} Orang<br>
                <strong>Total Hadiah Arisan:</strong> Rp {{ number_format($group->total_pot, 0, ',', '.') }}<br>
                <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB
            </div>
        </div>

        <!-- Tabel Rekapitulasi Putaran -->
        <h3 style="font-size: 13px; margin-bottom: 10px; text-transform: uppercase;">I. Rekapitulasi Putaran & Pemenang</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Jatuh Tempo</th>
                    <th>Tgl Kocok</th>
                    <th>Tuan Rumah</th>
                    <th>Pemenang</th>
                    <th>Nominal Cair</th>
                    <th>Status Kas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rounds as $r)
                    @php
                        $paidSum = $r->payments->where('payment_status', 'paid')->sum('amount');
                        $allPaid = $r->paid_members_count === $r->total_members_count;
                    @endphp
                    <tr>
                        <td style="text-align: center;">Putaran {{ $r->round_number }}</td>
                        <td>{{ $r->due_date->format('d/m/Y') }}</td>
                        <td>{{ $r->draw_date->format('d/m/Y') }}</td>
                        <td>{{ $r->host ? $r->host->name : '-' }}</td>
                        <td><strong>{{ $r->winner ? $r->winner->name : 'Belum Dikocok' }}</strong></td>
                        <td>Rp {{ number_format($r->winning_amount ?? $group->total_pot, 0, ',', '.') }}</td>
                        <td>{{ $allPaid ? 'LUNAS (100%)' : 'Belum Lengkap' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Ringkasan Keuangan -->
        <div class="total-box">
            <table class="total-table">
                <tr>
                    <td>Total Iuran Masuk (Lunas):</td>
                    <td><strong>Rp {{ number_format($totalCollected, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td>Total Denda Keterlambatan:</td>
                    <td><strong>Rp {{ number_format($totalPenalties, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td>Total Dana Diserahkan:</td>
                    <td><strong>Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</strong></td>
                </tr>
                <tr style="background: #f8fafc;">
                    <td><strong>Sisa Kas / Saldo Arisan:</strong></td>
                    <td><strong style="color: #16a34a;">Rp {{ number_format(($totalCollected + $totalPenalties) - $totalDisbursed, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Tanda Tangan Pengurus -->
        <div class="signatures">
            <div class="sign-col">
                <p>Mengetahui,<br><strong>Ketua PKK KarangKedawung</strong></p>
                <div class="sign-space"></div>
                <p><strong>( {{ $group->admin->name }} )</strong></p>
            </div>

            <div class="sign-col">
                <p>KarangKedawung, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br><strong>Bendahara PKK</strong></p>
                <div class="sign-space"></div>
                <p><strong>( Ibu Endang Rahayu )</strong></p>
            </div>
        </div>

    </div>

</body>
</html>
