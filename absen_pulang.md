# Plan Implementasi: Presensi Masuk dan Pulang

> **Status:** Disetujui sebagai acuan fase berikutnya.  
> **Tujuan dokumen:** Menjaga implementasi tetap sesuai ruang lingkup. Perubahan aturan bisnis, struktur data, atau alur utama di luar dokumen ini harus disepakati terlebih dahulu sebelum dikerjakan.

## 11. Sasaran Fitur

Fitur ini memperluas presensi harian dari satu kali scan menjadi dua tahap:

1. **Presensi masuk** untuk mencatat waktu kedatangan dan status hadir/terlambat.
2. **Presensi pulang** untuk mencatat bahwa siswa pulang sesuai jadwal.
3. Scan pulang sebelum waktunya ditolak agar siswa tidak dapat pulang cepat atau meninggalkan sekolah tanpa persetujuan petugas.
4. Jam masuk dan pulang dikelola Admin melalui aplikasi, bukan hardcoded di controller atau hanya melalui `.env`.

## 12. Keputusan Final

Keputusan berikut menjadi batas implementasi:

- Satu siswa memiliki **satu record presensi per tanggal**.
- Record yang sama menyimpan data masuk dan pulang.
- Kiosk menggunakan satu input RFID dan menentukan otomatis apakah scan tersebut merupakan scan masuk atau pulang.
- Siswa tidak memilih mode masuk/pulang secara manual.
- Scan pulang sebelum jadwal **tidak disimpan** sebagai pulang normal.
- Pulang cepat hanya dapat dicatat oleh petugas berwenang melalui halaman Presensi Manual dan wajib memiliki alasan.
- Jadwal reguler disimpan di database per hari.
- Jadwal khusus berdasarkan tanggal disiapkan setelah jadwal reguler stabil.
- Seluruh waktu menggunakan timezone `Asia/Jakarta` (WIB).
- Data presensi lama wajib dipertahankan saat migration.
- `.env` hanya menjadi fallback teknis jika jadwal database belum tersedia.

## 13. Jadwal Default

| Hari | Mulai Masuk | Batas Terlambat | Mulai Pulang | Aktif |
|---|---:|---:|---:|---|
| Senin | 06:00 | 07:15 | 13:00 | Ya |
| Selasa | 06:00 | 07:15 | 13:00 | Ya |
| Rabu | 06:00 | 07:15 | 13:00 | Ya |
| Kamis | 06:00 | 07:15 | 13:00 | Ya |
| Jumat | 06:00 | 07:15 | 12:00 | Ya |
| Sabtu | 06:00 | 07:15 | 11:00 | Ya |
| Minggu | — | — | — | Tidak |

Nilai tersebut adalah seed awal dan dapat diubah Admin.

## 14. Ruang Lingkup

### Termasuk dalam fase ini

- Tabel dan CRUD jadwal presensi reguler.
- Pengaturan jam masuk, batas terlambat, jam pulang, dan status aktif per hari.
- Migration aman untuk memperluas data presensi masuk–pulang.
- Deteksi otomatis scan masuk/pulang pada Kiosk.
- Penolakan scan pulang terlalu awal.
- Modal SweetAlert2 untuk hasil masuk, pulang, terlalu awal, sudah lengkap, dan error.
- Presensi manual untuk koreksi masuk/pulang serta izin pulang cepat.
- Rekap harian/bulanan yang menampilkan waktu masuk dan pulang.
- Statistik dashboard sudah pulang, belum pulang, dan pulang cepat.
- Hak akses Admin, Tata Usaha, dan Wali Kelas.
- Feature test untuk seluruh aturan kritis.

### Tidak termasuk dalam fase ini

- Integrasi mesin RFID melalui driver khusus.
- Notifikasi WhatsApp/SMS kepada orang tua.
- Geolocation atau face recognition.
- Perhitungan payroll atau jam kerja guru.
- Penjadwalan mata pelajaran.
- Multi-shift per rombel.
- Persetujuan pulang cepat secara online oleh orang tua.

Fitur di luar ruang lingkup tidak boleh ditambahkan selama fase ini tanpa revisi plan.

## 15. Desain Database

### 15.1 Tabel `jadwal_presensi`

| Kolom | Tipe | Aturan |
|---|---|---|
| `id` | bigint PK | |
| `hari` | unsigned tinyint, unique | ISO 1=Senin sampai 7=Minggu |
| `jam_mulai_masuk` | time, nullable | null jika hari tidak aktif |
| `batas_terlambat` | time, nullable | harus setelah/sama dengan mulai masuk |
| `jam_mulai_pulang` | time, nullable | harus setelah batas terlambat |
| `jam_akhir_pulang` | time, nullable | opsional; harus setelah mulai pulang |
| `is_aktif` | boolean | default true |
| timestamps | timestamp | |

Validasi wajib:

- Hari harus unik dan bernilai 1–7.
- Jadwal aktif wajib memiliki jam mulai masuk, batas terlambat, dan jam mulai pulang.
- `jam_mulai_masuk <= batas_terlambat < jam_mulai_pulang`.
- Jika diisi, `jam_akhir_pulang >= jam_mulai_pulang`.

### 15.2 Perubahan tabel `presensi`

| Kolom | Tipe | Catatan |
|---|---|---|
| `waktu_masuk` | time, nullable | hasil migrasi dari `waktu_scan` |
| `waktu_pulang` | time, nullable | null berarti belum scan pulang |
| `status` | enum/string | hadir, terlambat, izin, sakit, alpa |
| `status_pulang` | enum/string, nullable | tepat_waktu atau pulang_cepat |
| `metode_masuk` | enum/string | rfid atau manual |
| `metode_pulang` | enum/string, nullable | rfid atau manual |
| `alasan_pulang_cepat` | string, nullable | wajib jika pulang cepat |
| `catatan_pulang` | text, nullable | keterangan petugas |
| `pulang_dicatat_oleh` | FK users, nullable | petugas pencatat pulang manual |

Ketentuan migration:

- `waktu_scan` lama dipindahkan ke `waktu_masuk`.
- `metode` lama dipindahkan ke `metode_masuk`.
- Data, ID, relasi siswa, rombel, tanggal, status, dan pencatat lama tidak boleh hilang.
- Unique `siswa_id + tanggal` tetap dipertahankan.
- Penghapusan kolom lama dilakukan hanya setelah data berhasil disalin dan diuji.
- Migration harus kompatibel dengan MySQL production dan SQLite test.

### 15.3 Jadwal khusus (fase lanjutan setelah reguler stabil)

Tabel `pengecualian_jadwal_presensi` disiapkan untuk:

- Hari libur.
- Ujian.
- Ramadan.
- Kegiatan sekolah.
- Kepulangan lebih awal.

Prioritas jadwal:

1. Pengecualian berdasarkan tanggal.
2. Jadwal reguler berdasarkan hari.
3. Fallback konfigurasi.

Jadwal khusus tidak dikerjakan sebelum jadwal reguler dan alur masuk–pulang lulus seluruh test.

## 16. Menu dan Hak Akses

Tambahkan menu:

```text
Sistem
└── Pengaturan Presensi
```

| Aksi | Admin | Tata Usaha | Wali Kelas |
|---|:---:|:---:|:---:|
| Melihat jadwal | Ya | Ya | Ya |
| Mengubah jadwal | Ya | Tidak | Tidak |
| Melihat presensi seluruh rombel | Ya | Ya | Tidak |
| Melihat presensi rombel sendiri | Ya | Ya | Ya |
| Koreksi presensi | Ya | Ya | Rombel sendiri |
| Mencatat pulang cepat | Ya | Ya | Rombel sendiri |

Perubahan jadwal harus divalidasi server-side. Hak akses tidak boleh hanya bergantung pada tombol yang disembunyikan di UI.

## 17. Alur Scan Kiosk

Endpoint tetap:

```text
POST /presensi/scan
body: { kode_uid }
```

### 17.1 Validasi awal

1. Normalisasi UID menjadi uppercase.
2. Cari kartu RFID aktif.
3. Pastikan siswa aktif.
4. Cari keanggotaan rombel aktif pada Tahun Ajaran aktif.
5. Ambil jadwal hari ini menggunakan timezone WIB.
6. Jika hari tidak aktif atau jadwal tidak ditemukan, tolak scan dengan pesan yang jelas.
7. Semua proses baca/tulis record presensi dilakukan dalam transaction dan row lock.

### 17.2 Scan pertama — masuk

Jika record presensi hari ini belum ada:

- Buat satu record presensi.
- Isi `waktu_masuk` dengan waktu sekarang.
- Isi `metode_masuk = rfid`.
- Jika waktu masuk sampai batas terlambat: `status = hadir`.
- Jika waktu masuk setelah batas terlambat: `status = terlambat`.
- Jangan mengisi waktu pulang.

Respons Kiosk:

```text
Presensi Masuk Berhasil
Nama siswa
Kelas 7
Masuk 07:05 WIB
Status Hadir
```

### 17.3 Scan kedua sebelum jam pulang — ditolak

Jika record sudah memiliki waktu masuk, belum memiliki waktu pulang, dan waktu sekarang sebelum `jam_mulai_pulang`:

- Jangan mengubah record.
- Tampilkan jadwal pulang hari ini.
- Arahkan siswa menghubungi petugas jika memiliki izin khusus.

Respons:

```text
Belum Waktunya Pulang
Jadwal pulang hari ini pukul 13:00 WIB.
```

### 17.4 Scan kedua setelah jam pulang — pulang

Jika record sudah memiliki waktu masuk, belum memiliki waktu pulang, dan waktu sekarang sudah mencapai jadwal pulang:

- Isi `waktu_pulang`.
- Isi `metode_pulang = rfid`.
- Isi `status_pulang = tepat_waktu`.

Respons:

```text
Presensi Pulang Berhasil
Nama siswa
Kelas 7
Pulang 13:03 WIB
```

### 17.5 Scan berikutnya — sudah lengkap

Jika waktu masuk dan pulang sudah terisi:

- Jangan mengubah data.
- Tampilkan informasi masuk dan pulang.

```text
Presensi Hari Ini Sudah Lengkap
Masuk 07:05 WIB
Pulang 13:03 WIB
```

## 18. Aturan Pulang Cepat

Pulang cepat tidak boleh dicatat otomatis dari scan RFID siswa.

Petugas wajib mengisi:

- Waktu pulang.
- Alasan pulang cepat.
- Catatan opsional.

Alasan minimal:

- Sakit.
- Izin orang tua.
- Keperluan sekolah.
- Lainnya.

Saat disimpan:

- `status_pulang = pulang_cepat`.
- `metode_pulang = manual`.
- `pulang_dicatat_oleh = user login`.
- `alasan_pulang_cepat` wajib terisi.
- Waktu pulang harus setelah waktu masuk dan sebelum jadwal pulang.

Siswa tanpa waktu masuk tidak boleh langsung dicatat pulang kecuali petugas juga mengisi waktu masuk melalui koreksi manual.

## 19. Perubahan Presensi Manual

Tabel Presensi Harian menampilkan:

| Siswa | Status Masuk | Waktu Masuk | Waktu Pulang | Status Pulang | Aksi |
|---|---|---:|---:|---|---|

Petugas dapat:

- Mengubah status hadir/terlambat/izin/sakit/alpa.
- Mengisi atau memperbaiki waktu masuk.
- Mengisi atau memperbaiki waktu pulang.
- Mencatat pulang cepat dan alasannya.

Aturan:

- Semua perubahan menggunakan validasi inline dan validasi backend.
- Riwayat RFID tidak boleh hilang tanpa alasan.
- Koreksi manual harus menyimpan user pencatat.
- Wali kelas hanya dapat mengubah siswa rombelnya.

## 20. Perubahan Laporan

### Rekap harian

Tambahkan:

- Waktu masuk.
- Status masuk.
- Waktu pulang.
- Status pulang.
- Durasi di sekolah.
- Alasan pulang cepat.

### Rekap bulanan

Tambahkan ringkasan:

- Jumlah terlambat.
- Jumlah presensi pulang lengkap.
- Jumlah lupa/belum scan pulang.
- Jumlah pulang cepat.

### Filter

- Sudah pulang.
- Belum pulang.
- Pulang cepat.
- Presensi lengkap.

Ekspor CSV dan halaman cetak wajib mengikuti kolom baru.

## 21. Perubahan Dashboard

Tambahkan statistik hari ini:

- Sudah masuk.
- Terlambat.
- Sudah pulang.
- Belum pulang.
- Pulang cepat.

`Belum pulang` dihitung dari siswa yang memiliki waktu masuk tetapi `waktu_pulang` masih null. Sebelum jadwal pulang, statistik ini bersifat informatif; setelah jam akhir pulang, data menjadi perhatian petugas.

## 22. Penanganan Kondisi Khusus

- **Izin/sakit/alpa:** tidak wajib memiliki waktu masuk dan pulang.
- **Kartu tidak aktif:** scan ditolak.
- **Siswa tidak aktif:** scan ditolak.
- **Tidak memiliki rombel aktif:** scan ditolak.
- **Hari tidak aktif/libur:** scan ditolak.
- **Jadwal tidak lengkap:** scan ditolak dan dicatat pada log aplikasi.
- **Dua scan bersamaan:** transaction dan `lockForUpdate()` mencegah data ganda.
- **Waktu server/perangkat berbeda:** keputusan status menggunakan waktu server Laravel dalam `Asia/Jakarta`, bukan jam browser.
- **Setelah tengah malam:** record dihitung sebagai tanggal baru berdasarkan WIB.

## 23. Urutan Implementasi

Urutan ini wajib diikuti agar perubahan mudah diuji dan tidak merusak data lama.

### Fase A — Jadwal reguler

- [x] Migration tabel `jadwal_presensi`.
- [x] Model dan relasi/akses jadwal.
- [x] Seeder jadwal default Senin–Sabtu.
- [x] CRUD Pengaturan Presensi untuk Admin.
- [x] Validasi urutan jam.
- [x] Test CRUD, role, dan jadwal aktif/nonaktif.

### Fase B — Migration presensi masuk–pulang

- [x] Tambahkan kolom baru secara aman.
- [x] Migrasikan `waktu_scan` ke `waktu_masuk`.
- [x] Migrasikan `metode` ke `metode_masuk`.
- [x] Perbarui model dan casts.
- [x] Verifikasi jumlah dan isi data sebelum/sesudah migration.
- [ ] Test migration pada MySQL belum dijalankan; migration telah lulus pada SQLite test dan database lokal.

### Fase C — Kiosk masuk–pulang

- [x] Refactor endpoint scan menjadi alur masuk/pulang otomatis.
- [x] Tambahkan transaction dan row lock untuk kedua tahap.
- [x] Tambahkan respons scan masuk.
- [x] Tambahkan penolakan pulang terlalu awal.
- [x] Tambahkan respons scan pulang.
- [x] Tambahkan respons sudah lengkap.
- [x] Perbarui SweetAlert2 Kiosk.
- [x] Test seluruh cabang waktu.

### Fase D — Presensi manual dan pulang cepat

- [x] Perbarui tabel/form Presensi Manual.
- [x] Tambahkan koreksi waktu masuk/pulang.
- [x] Tambahkan alasan pulang cepat.
- [x] Simpan pencatat perubahan.
- [x] Terapkan pembatasan role dan rombel.
- [x] Test validasi dan otorisasi.

### Fase E — Laporan dan Dashboard

- [x] Perbarui rekap harian.
- [x] Perbarui rekap bulanan.
- [x] Perbarui CSV dan cetak.
- [x] Tambahkan filter status pulang.
- [x] Tambahkan statistik dashboard.
- [x] Test perhitungan dan hak akses.

### Fase F — Jadwal khusus

Dikerjakan hanya setelah Fase A–E stabil dan disetujui.

- [ ] Tabel pengecualian jadwal.
- [ ] Hari libur.
- [ ] Override jam berdasarkan tanggal.
- [ ] UI kalender jadwal khusus.
- [ ] Test prioritas jadwal khusus terhadap reguler.

## 24. Acceptance Criteria

Fitur dianggap selesai hanya jika seluruh kriteria berikut terpenuhi:

- [ ] Admin dapat mengatur jam masuk, terlambat, dan pulang per hari.
- [ ] Senin–Kamis default pulang pukul 13:00 WIB.
- [ ] Jumat default pulang pukul 12:00 WIB.
- [ ] Sabtu default pulang pukul 11:00 WIB.
- [ ] Minggu default tidak aktif.
- [ ] Scan pertama mencatat waktu masuk.
- [ ] Scan setelah batas terlambat berstatus terlambat.
- [ ] Scan sebelum jam pulang ditolak tanpa mengubah data.
- [ ] Scan setelah jam pulang mencatat waktu pulang.
- [ ] Scan setelah presensi lengkap tidak mengubah data.
- [ ] Pulang cepat hanya dapat dicatat petugas berwenang dan memiliki alasan.
- [ ] Data presensi lama tetap tersedia setelah migration.
- [ ] Wali kelas tidak dapat mengubah siswa rombel lain.
- [ ] Rekap, CSV, cetak, dan dashboard menggunakan data masuk–pulang.
- [ ] Semua keputusan waktu menggunakan WIB.
- [ ] Seluruh feature test baru dan test lama lulus.
- [ ] Tidak ada perubahan di luar scope tanpa revisi plan.

## 25. Strategi Pengujian

Minimal test yang wajib tersedia:

1. Admin dapat mengubah jadwal; role lain ditolak.
2. Jadwal aktif wajib memiliki seluruh jam.
3. Urutan jam yang tidak valid ditolak.
4. Scan pertama sebelum batas terlambat menghasilkan hadir.
5. Scan pertama setelah batas terlambat menghasilkan terlambat.
6. Scan kedua sebelum jam pulang ditolak.
7. Scan kedua tepat pada jam pulang diterima.
8. Scan kedua setelah jam pulang diterima.
9. Scan ketiga tidak mengubah data.
10. Jumat menggunakan jadwal pulang 12:00.
11. Sabtu menggunakan jadwal pulang 11:00.
12. Hari tidak aktif menolak scan.
13. Pulang cepat membutuhkan alasan.
14. Pulang cepat menyimpan user pencatat.
15. Wali kelas dibatasi pada rombel sendiri.
16. Dua scan bersamaan tidak menciptakan data ganda.
17. Data lama tetap terbaca setelah migration.
18. Rekap dan dashboard menghitung status pulang dengan benar.
19. Timezone selalu `Asia/Jakarta`.

## 26. Definition of Done

Setiap fase dinyatakan selesai apabila:

- Migration aman dan dapat dijalankan pada data existing.
- Validasi frontend dan backend tersedia.
- Otorisasi diterapkan pada route dan query data.
- UI mendukung light/dark mode dan responsif.
- Pesan keberhasilan/kegagalan jelas bagi pengguna.
- Test terkait fase tersebut lulus.
- Test lama tidak mengalami regresi.
- `php artisan test`, `npm run build`, dan `php artisan view:cache` berhasil.
- Dokumentasi `plan.md` diperbarui jika ada keputusan baru.

## 27. Guardrails Pengerjaan

Selama implementasi, aturan berikut wajib dipatuhi:

1. Jangan menghapus atau me-reset data production untuk menyesuaikan skema baru.
2. Jangan menjalankan seeder destruktif pada database production.
3. Jangan mengubah satu-record-per-siswa-per-tanggal tanpa revisi plan.
4. Jangan membuat tombol mode masuk/pulang pada Kiosk; deteksi harus otomatis.
5. Jangan menerima pulang cepat melalui scan RFID biasa.
6. Jangan menggunakan waktu browser untuk keputusan bisnis.
7. Jangan memperluas scope ke notifikasi, geolocation, atau modul akademik.
8. Jangan mengubah laporan hanya sebagian; harian, bulanan, CSV, dan cetak harus konsisten.
9. Jangan menandai fase selesai sebelum acceptance criteria dan test terkait lulus.
10. Setiap perubahan aturan bisnis harus dicatat terlebih dahulu pada dokumen ini.
