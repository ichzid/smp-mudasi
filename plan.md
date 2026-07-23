# Plan: Sistem Informasi Sekolah — SMP Muhammadiyah Danau Sijabut

## 1. Tujuan

Membangun fondasi sistem informasi sekolah, dimulai dari tiga modul inti:
1. **Data master** — tahun ajaran, rombel (kelas), siswa, kartu RFID, guru, user & role.
2. **Presensi** — input via simulasi kode/RFID, rekap harian, izin/sakit manual.
3. **Laporan** — rekap kehadiran per siswa/kelas/periode.

Modul akademik (nilai, jadwal pelajaran, dst) **sengaja tidak masuk MVP** — baru dikerjakan setelah tiga modul di atas stabil dan dipakai.

## 2. Tech stack

| Layer | Pilihan | Catatan |
|---|---|---|
| Backend | Laravel 13 | PHP 8.3+ |
| Frontend | Blade + Alpine.js | murni server-rendered, tanpa Vue/React/Inertia — Alpine sudah terpasang bawaan TailAdmin Laravel untuk interaktivitas ringan (dropdown, modal, toggle, dan fetch() untuk kiosk) |
| Styling | Tailwind CSS | bawaan TailAdmin Laravel |
| Starter kit + template admin | **[TailAdmin/tailadmin-laravel](https://github.com/TailAdmin/tailadmin-laravel)** (resmi) | project Laravel penuh (bukan sekadar file HTML) — Laravel 12+/Tailwind v4/Alpine.js, sudah termasuk layout sidebar, komponen UI, dan halaman auth siap pakai — lihat bagian 8 |
| Database | MySQL (production) / SQLite (lokal, biar cepat mulai) | |
| Auth | Laravel Fortify (headless), dipasangkan ke Blade view auth bawaan TailAdmin | Fortify cuma nyediain logic backend (login, register, reset password) — tampilannya pakai halaman Auth yang sudah ada di TailAdmin, jadi tidak perlu bikin dari nol maupun pakai starter kit auth terpisah |
| Autorisasi | Laravel Policy + middleware role | lihat bagian 6 |
| Testing | Pest | opsional tapi disarankan untuk `PresensiController` |

Perintah awal proyek:
```bash
git clone https://github.com/TailAdmin/tailadmin-laravel.git sistem-sekolah
cd sistem-sekolah
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate
composer require laravel/fortify
php artisan fortify:install   # atau publish config secara manual kalau tidak ada command ini di versi Fortify terbaru
npm run dev
```
Setelah Fortify terpasang, arahkan `config/fortify.php` (view login/register/dsb.) dan `FortifyServiceProvider` supaya me-render Blade view Auth yang sudah ada di TailAdmin (`resources/views/auth/...`), bukan view bawaan Fortify — cek dulu path view Auth yang disediakan template ini karena bisa beda nama antar versi.

## 3. Aturan bisnis penting

Catat ini supaya tidak terlewat saat coding:

- **Satu siswa hanya boleh punya satu kartu RFID aktif.** Relasi `siswa` ↔ `kartu_rfid` adalah **one-to-one**, bukan one-to-many. Ditegakkan di dua lapis:
  - **Database**: kolom `siswa_id` di tabel `kartu_rfid` diberi constraint **`unique`** (bukan cuma foreign key biasa) — mencegah satu siswa terdaftar dua kartu sekaligus di level DB, bukan cuma di validasi form.
  - **Kode kartu (`kode_uid`) juga `unique`** — mencegah dua siswa berbeda punya kode kartu yang sama.
  - **Aplikasi**: kalau kartu hilang/rusak, admin **meng-update** `kode_uid` pada baris yang sudah ada (bukan insert baris baru), karena satu siswa memang cuma boleh punya satu baris kartu. Kalau butuh jejak audit "kartu lama pernah dipakai", itu tabel riwayat terpisah — masuk fase lanjutan, tidak perlu di MVP.
  - Validasi di `StoreKartuRfidRequest` / `UpdateKartuRfidRequest`: rule `unique:kartu_rfid,siswa_id` dan `unique:kartu_rfid,kode_uid` (dengan pengecualian ID sendiri saat update).
- **Rombel bukan kolom statis di siswa.** Siswa terhubung ke rombel lewat tabel pivot `rombel_siswa` yang punya `tanggal_masuk` / `tanggal_keluar`, supaya kenaikan kelas dan mutasi tidak menimpa riwayat.
- **`presensi` menyimpan `rombel_id` miliknya sendiri** saat dicatat, supaya rekap lama tetap benar walau siswa kemudian pindah rombel.
- **Status siswa** (`aktif`, `lulus`, `pindah`, `keluar`) dipakai untuk filter di seluruh sistem — siswa yang sudah lulus/keluar tidak boleh muncul di daftar presensi aktif.

## 4. Skema database

### `tahun_ajaran`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| nama | string | contoh: "2025/2026" |
| semester | tinyint | 1 atau 2 |
| is_aktif | boolean | hanya satu baris boleh aktif — tegakkan di service, bukan constraint DB |

### `rombel`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| nama | string | contoh: "7 A" |
| tingkat | string | 7 / 8 / 9 |
| tahun_ajaran_id | FK → tahun_ajaran | |
| wali_guru_id | FK → guru, nullable | |

### `siswa`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| nis | string, unique | |
| nisn | string, unique, nullable | |
| nama | string | |
| jenis_kelamin | enum('L','P') | |
| tempat_lahir | string, nullable | |
| tanggal_lahir | date, nullable | |
| alamat | text, nullable | |
| foto_url | string, nullable | |
| status | enum('aktif','lulus','pindah','keluar') default 'aktif' | |

### `rombel_siswa` (pivot riwayat)
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| rombel_id | FK → rombel | |
| siswa_id | FK → siswa | |
| tanggal_masuk | date | |
| tanggal_keluar | date, nullable | null berarti masih aktif di rombel ini |

### `kartu_rfid` — **one-to-one dengan siswa**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| siswa_id | FK → siswa, **unique** | satu siswa = satu baris, tidak boleh lebih |
| kode_uid | string, **unique** | kode UID kartu, huruf besar |
| status | enum('aktif','nonaktif') default 'aktif' | untuk menonaktifkan sementara tanpa hapus data |
| diterbitkan_pada | date | |

### `presensi`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| siswa_id | FK → siswa | |
| rombel_id | FK → rombel | disalin saat presensi dicatat, lihat aturan bisnis |
| tanggal | date | |
| waktu_scan | time | |
| status | enum('hadir','terlambat','izin','sakit','alpa') | |
| metode | enum('rfid','manual') | |
| dicatat_oleh | FK → users, nullable | terisi kalau `metode = manual` |

### `guru`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| nip | string, nullable | |
| nama | string | |
| user_id | FK → users, nullable | kalau guru juga punya akun login |

### `users` (bawaan TailAdmin Laravel, ditambah)
| Kolom tambahan | Tipe | Catatan |
|---|---|---|
| role | enum('admin','tu','wali_kelas') | dipakai middleware & policy |

Contoh migration untuk bagian paling krusial (`kartu_rfid`):
```php
Schema::create('kartu_rfid', function (Blueprint $table) {
    $table->id();
    $table->foreignId('siswa_id')->unique()->constrained('siswa')->cascadeOnDelete();
    $table->string('kode_uid')->unique();
    $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
    $table->date('diterbitkan_pada');
    $table->timestamps();
});
```

## 5. Struktur folder (Laravel + Blade)

```
app/
  Http/
    Controllers/
      RombelController.php
      SiswaController.php
      KartuRfidController.php
      PresensiController.php      # termasuk endpoint scan
      LaporanController.php
    Requests/
      StoreSiswaRequest.php
      StoreKartuRfidRequest.php   # validasi unique siswa_id & kode_uid di sini
      UpdateKartuRfidRequest.php
  Models/
    TahunAjaran.php
    Rombel.php
    Siswa.php                     # relasi hasOne(KartuRfid::class)
    RombelSiswa.php
    KartuRfid.php                 # relasi belongsTo(Siswa::class)
    Presensi.php
    Guru.php
  Policies/
    SiswaPolicy.php
    PresensiPolicy.php

resources/views/                  # struktur bawaan TailAdmin Laravel dipakai apa adanya
  layouts/
    app.blade.php                 # layout sidebar admin bawaan TailAdmin
    kiosk.blade.php                # layout khusus, tambahan di luar bawaan TailAdmin (tanpa sidebar admin)
  auth/                            # halaman login/register bawaan TailAdmin, dipasangkan ke Fortify (lihat bagian 8)
  components/
    kartu-id-siswa.blade.php      # modal sukses, reuse dari desain kiosk, pakai Alpine x-show untuk animasi
  presensi/
    kiosk.blade.php                # halaman scan, Alpine x-data + fetch() ke POST /presensi/scan
  siswa/
    index.blade.php
    form.blade.php
  rombel/
    index.blade.php
  laporan/
    rekap.blade.php

routes/
  web.php                         # semua route Blade, tidak perlu api.php terpisah (fetch dari Alpine tetap lewat web.php + CSRF token meta tag)
```


## 6. Role & hak akses (rencana awal)

| Role | Bisa akses |
|---|---|
| `admin` | semua modul, termasuk kelola user & kartu RFID |
| `tu` (tata usaha) | data master siswa, rombel, kartu RFID, laporan |
| `wali_kelas` | presensi manual (izin/sakit) untuk rombel yang diampu, lihat laporan rombelnya sendiri |

Ditegakkan lewat middleware `role:admin,tu` di route group + `SiswaPolicy` / `PresensiPolicy` untuk detail per-object.

## 7. Alur endpoint presensi (kiosk)

```
POST /presensi/scan
  body: { kode_uid: string }

  1. Cari kartu_rfid dengan kode_uid = input, status = aktif
     → tidak ketemu: return 404, tampilkan "Kartu tidak dikenali"
  2. Ambil siswa dari relasi kartu → kartu.siswa
     → siswa.status != 'aktif': return 422, "Siswa tidak aktif"
  3. Cari rombel_siswa aktif milik siswa (tanggal_keluar null) untuk tahun ajaran aktif
     → tidak ketemu: return 422, "Siswa belum terdaftar di rombel manapun"
  4. Tentukan status (hadir/terlambat) dari jam sekarang vs jam masuk (pengaturan)
  5. Insert ke presensi (cegah duplikat: unique index pada siswa_id + tanggal)
  6. Return data siswa + foto + rombel + status → dikonsumsi di sisi Alpine (`x-data`) sebagai JSON, dipakai untuk isi modal sukses (`kartu-id-siswa.blade.php`) tanpa reload halaman
```

Index unique yang perlu ditambahkan di migration `presensi`: `unique(['siswa_id', 'tanggal'])` supaya satu siswa tidak tercatat dua kali hadir di hari yang sama lewat scan berulang.

Catatan implementasi kiosk: karena tidak ada Vue/Inertia, halaman `presensi/kiosk.blade.php` memakai `x-data` Alpine untuk state (input kode_uid, hasil scan terakhir), lalu `fetch('/presensi/scan', { method: 'POST', headers: { 'X-CSRF-TOKEN': ... } })` untuk memanggil endpoint tanpa reload. Token CSRF diambil dari `<meta name="csrf-token">` yang sudah ada di layout bawaan TailAdmin.

## 8. Template admin: TailAdmin resmi (Blade + Alpine.js)

**Keputusan final:** proyek dimulai dari **[`TailAdmin/tailadmin-laravel`](https://github.com/TailAdmin/tailadmin-laravel)** — repo resmi TailAdmin untuk Laravel, bukan versi komunitas seperti draf sebelumnya. Ini sekarang cocok karena keseluruhan stack sudah diputuskan pure Laravel + Blade + Alpine.js, tanpa Vue/React/Inertia — persis apa yang dipakai template ini.

Yang perlu diketahui:
- Repo ini **project Laravel yang utuh** (bukan cuma file HTML statis) — sudah ada struktur `app/`, `database/`, `routes/`, `resources/views/` dengan Blade, ditenagai Laravel 12+, Tailwind CSS v4, Alpine.js, dan Vite. Tinggal `git clone`, `composer install`, `npm install`, langsung bisa dipakai untuk nambah modul sendiri.
- Repo resmi ini **kemungkinan belum menyertakan logic auth lengkap** (login/register backend) — dokumentasinya menyebut starter kit komunitas dibuat belakangan justru untuk melengkapi bagian auth ini. Tapi dari sisi tampilan, template sudah menyediakan halaman Auth siap pakai. Solusinya: pasang **Laravel Fortify** (package headless, cuma nyediain logic backend) dan arahkan supaya me-render Blade view Auth yang sudah ada di TailAdmin, bukan bikin dari nol. Cek dulu struktur `resources/views/auth/` begitu clone, karena nama file bisa beda-beda tergantung versi template saat ini.
- Semua interaktivitas (dropdown, modal, sidebar toggle, dark mode) sudah pakai Alpine.js bawaan template — dipakai apa adanya, jangan tambah Livewire atau framework JS lain supaya tetap satu paradigma (pure Blade + Alpine).
- Untuk kebutuhan "serasa SPA" di endpoint yang butuh respons cepat tanpa reload (khususnya kiosk presensi, bagian 7), cukup pakai kombinasi Alpine `x-data` + `fetch()` ke route Blade biasa — tidak perlu REST API terpisah atau library tambahan.
- Struktur `resources/views/layouts` dan komponen bawaan dipakai apa adanya (lihat bagian 5) — halaman baru mengikuti pola Blade yang sudah ada di template (potongan sidebar, card, table) supaya konsisten secara visual, bukan bikin layout paralel.
- Karena ini template pihak ketiga (meski resmi dari TailAdmin, bukan first-party Laravel), tetap ada risiko dukungan/update tidak secepat starter kit resmi Laravel sendiri — kalau nanti versi Laravel naik dan ada breaking change, mungkin perlu tunggu update dari TailAdmin atau porting manual bagian yang terpengaruh.

## 9. Roadmap eksekusi

- [x] **Fase 0 — fondasi, autentikasi, dan otorisasi**
  - [x] TailAdmin terpasang dan layout aplikasi tersedia.
  - [x] Laravel Fortify dan kolom `role` pada tabel `users` tersedia.
  - [x] View login/register terhubung ke Fortify dan login/logout berjalan end-to-end.
  - [x] Halaman administrasi dilindungi middleware `auth`.
  - [x] Middleware role `admin`, `tu`, dan `wali_kelas` aktif; presensi/laporan wali kelas dibatasi berdasarkan rombel yang diampu.
- [x] **Fase 1 — data master**
  - [x] Migration & model tersedia untuk `tahun_ajaran`, `rombel`, `siswa`, `rombel_siswa`, `kartu_rfid`, dan `guru`.
  - [x] CRUD tahun ajaran, guru, siswa, dan rombel per tahun ajaran.
  - [x] CRUD siswa selaras untuk `nama_lengkap`, data wali, status siswa, NISN opsional, dan upload foto.
  - [x] Histori Rombel Siswa dipertahankan melalui `tanggal_keluar` dan perpindahan kelas tidak menghapus riwayat.
  - [x] Penempatan rombel hanya untuk siswa aktif dan menolak rentang keanggotaan yang tumpang tindih.
  - [x] Kartu RFID menegakkan satu siswa satu kartu, menormalisasi UID, dan tidak memiliki route detail tanpa halaman.
- [x] **Fase 2 — presensi**
  - [x] Endpoint `POST /presensi/scan` memvalidasi UID, dibatasi rate limit, aman terhadap scan ganda, dan memakai batas terlambat dari konfigurasi.
  - [x] Halaman kiosk menggunakan Alpine `x-data` dan `fetch()` ke endpoint asli, dengan input UID manual sementara.
  - [x] Presensi manual memvalidasi keanggotaan siswa, memakai user login sebagai pencatat, dan mempertahankan jejak RFID jika status tidak berubah.
  - [x] Wali kelas hanya dapat mengelola presensi rombel yang diampu.
- [x] **Fase 3 — laporan**
  - [x] Rekap harian per rombel tersedia pada modul laporan terpisah.
  - [x] Rekap bulanan per siswa dan ekspor CSV kompatibel Excel tersedia.
  - [x] Halaman cetak tersedia untuk Print / Save as PDF tanpa dependency PDF tambahan.
  - [x] Dashboard ringkas jumlah hadir, terlambat, izin, sakit, dan alpa hari ini.
  - [x] Denominator dashboard hanya menghitung siswa aktif pada rombel tahun ajaran aktif.
- [x] **Kualitas dan integritas aplikasi**
  - [x] Unique gabungan nama dan semester Tahun Ajaran tersedia.
  - [x] Invariant satu Tahun Ajaran aktif diperkuat menggunakan transaksi dan row locking pada controller.
  - [x] Feature test tersedia untuk autentikasi/role, CRUD siswa, Tahun Ajaran, kartu RFID, mutasi rombel, dashboard, presensi, dan laporan.
  - [x] Nama tabel/kolom utama antara migration, model, controller, validation, dan Blade sudah diselaraskan.
- [ ] **Fase 4+ (nanti, di luar MVP)** — modul akademik: nilai dan jadwal pelajaran.

## 10. Hal yang sengaja belum diputuskan

Tandai ini biar tidak lupa dibahas sebelum atau saat coding:

- Perangkat RFID fisik: reader model apa, terhubung sebagai keyboard-emulator (seperti asumsi di prototipe) atau butuh driver/API khusus?
- Kebijakan jam masuk & batas terlambat: statis di `.env`/config atau perlu diatur admin lewat UI (tabel `pengaturan`)?
- Backup/riwayat kartu hilang: apakah cukup overwrite `kode_uid`, atau perlu tabel `kartu_rfid_riwayat` untuk audit — bisa ditambahkan tanpa mengubah struktur inti, jadi aman ditunda.