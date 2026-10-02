#  Arisan PKK KarangKedawung

Aplikasi Sistem Manajemen Arisan Digital berbasis **Laravel** yang ramah pengguna, mudah dipahami, elegan, dan transparan, dirancang khusus untuk memenuhi kebutuhan **Ibu-Ibu PKK Desa KarangKedawung**.

---

##  Fitur Utama

### 1. Manajemen Kelompok Arisan
- Pembuatan kelompok arisan: nama, nominal iuran bulanan/mingguan, kuota anggota, tanggal mulai.
- Pengaturan aturan kelompok: nominal denda per hari, batas toleransi keterlambatan (*grace period*), sistem penentuan pemenang (kocokan acak atau urutan tetap).
- Dukungan multi-kelompok: satu admin/pengurus dapat mengelola lebih dari satu kelompok arisan secara bersamaan.
- Rekening pembayaran iuran (BCA, BRI, Kas PKK).

### 2. Manajemen Anggota PKK
- Data lengkap anggota: nama, nomor WhatsApp (format Indonesia `08...`/`62...`), email, dan alamat RT/RW.
- Tambah, edit, aktifkan, atau nonaktifkan anggota.
- Status anggota: otomatis terpantau apakah sudah menang atau belum menang.
- Preferensi notifikasi WhatsApp / Aplikasi.

### 3. Jadwal & Putaran Arisan
- **Generate Jadwal Otomatis**: Cukup tentukan tanggal mulai dan periode, seluruh putaran 1 s/d N serta tagihan iuran terbentuk otomatis.
- **Reschedule Pintar**: Admin dapat menggeser tanggal jatuh tempo/kocokan dengan opsi pergeseran otomatis (*cascade*) untuk putaran selanjutnya.
- **Kalender Arisan Interaktif**: Tampilan visual bulanan dengan penanda warna untuk tanggal jatuh tempo iuran dan tanggal pertemuan/pengocokan.
- Manajemen Tuan Rumah (*Host*) arisan dan lokasi pertemuan.

### 4. Iuran dan Pembayaran
- Tagihan iuran otomatis per anggota per putaran.
- Status bayar: *Belum Bayar*, *Menunggu Verifikasi*, *Lunas*, dan *Telat*.
- Upload foto bukti transfer / nota oleh anggota langsung dari galeri HP.
- Verifikasi pembayaran manual oleh admin dengan 1 kali klik.
- Fitur **Penerimaan Tunai di Tempat** untuk pertemuan tatap muka.
- Perhitungan denda otomatis jika melewati batas toleransi hari.

### 5. Ruang Pengocokan Arisan (Interactive Lucky Roulette Wheel)
- **Roda Keberuntungan Interaktif**: Animasi putaran roulette acak berhias konfeti perayaan (*confetti*).
- Hanya mengocok anggota yang belum pernah menang.
- Filter opsional: hanya anggota yang iurannya sudah lunas yang ikut dikocok.
- Hasil undian tersimpan permanen ke database secara aman dan transparan.
- Pencatatan penyerahan uang/pencairan dana ke pemenang beserta bukti serah terima foto/kwitansi.

### 6. Notifikasi & Pengingat WhatsApp
- Template pesan WhatsApp yang sopan, santun, ramah ibu-ibu dengan emotikon.
- Tautan langsung `wa.me` siap klik:
  - Pengingat jatuh tempo iuran (H-3, H-1, Hari H).
  - Pemberitahuan tagihan telat beserta rincian denda.
  - Konfirmasi pembayaran berhasil diverifikasi (LUNAS).
  - Pengumuman pemenang arisan dan info pencairan dana.
- Log notifikasi dan antrean pesan.

### 7. Laporan & Transparansi
- **Papan Transparansi Terbuka**: Dapat diakses oleh seluruh anggota untuk melihat siapa yang sudah menang, belum menang, dan status kas.
- Rekapitulasi kas per putaran: total masuk, belum masuk (tunggakan), dan denda terkumpul.
- **Cetak Laporan Resmi**: Format cetak/PDF lengkap dengan kop surat resmi PKK KarangKedawung dan kolom tanda tangan Ketua & Bendahara.
- **Export Data ke Excel/CSV**: Ekspor seluruh rincian pembayaran dengan 1 klik.

### 8. Hak Akses & Keamanan
- **Role Admin / Pengurus**: Akses penuh mengelola kelompok, anggota, jadwal, verifikasi iuran, pengocokan, dan laporan.
- **Role Anggota PKK**: Dashboard personal untuk melihat tagihan saya, upload bukti bayar, jadwal pertemuan, dan riwayat kemenangan.
- **Log Aktivitas Admin**: Riwayat audit seluruh perubahan data sistem.

---

##  Akun Uji Coba (Demo Accounts)

Aplikasi telah dilengkapi dengan tombol login cepat 1-klik pada halaman masuk:

| Peran | No. WhatsApp | Password | Akses |
|---|---|---|---|
| **Ketua PKK (Admin)** | `081234567890` | `password` | Pengurus / Akses Penuh |
| **Ibu Endang (Anggota)** | `081234567891` | `password` | Anggota PKK |
| **Ibu Sri Wahyuni (Anggota)** | `081234567892` | `password` | Anggota PKK |

---

## Cara Menjalankan Aplikasi

1. Clone repositori:
   ```bash
   git clone https://github.com/arsyafarahman123/Arisan_PKK_KarangKedawung.git
   cd Arisan_PKK_KarangKedawung
   ```

2. Install dependensi composer:
   ```bash
   composer install
   ```

3. Setup environment & database:
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

4. Jalankan server lokal:
   ```bash
   php artisan serve
   ```
   Buka browser di `http://localhost:8000`

---

Dibuat untuk **Pemberdayaan Kesejahteraan Keluarga (PKK) Desa KarangKedawung**.
