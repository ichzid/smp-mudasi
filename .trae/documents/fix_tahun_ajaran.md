# Plan: Fix Tahun Ajaran Data Display Issues

## Summary
The codebase contains a discrepancy between the `tahun_ajaran` database schema and how it is referenced in multiple view files. The database uses a single `nama` column (e.g., "2025/2026") and a `semester` integer column (1 or 2). However, several Blade templates are incorrectly trying to access `$ta->tahun_mulai` and `$ta->tahun_selesai`, which do not exist, resulting in empty strings or formatting artifacts like `/ - 1` being displayed in comboboxes and tables.

## Current State Analysis
Based on the codebase exploration:
- **Migration & Model:** `TahunAjaran` has columns `id`, `nama`, `semester`, `is_aktif`.
- **Issues in Views:** Multiple `.blade.php` files are using `{{ $ta->tahun_mulai }}/{{ $ta->tahun_selesai }} - {{ ucfirst($ta->semester) }}`. Because `tahun_mulai` and `tahun_selesai` are null/undefined, this renders as ` / - 1` (assuming `semester` is 1).
- **Target Files:**
  1. `resources/views/pages/master/rombel/create.blade.php`
  2. `resources/views/pages/master/rombel/edit.blade.php`
  3. `resources/views/pages/master/rombel/show.blade.php`
  4. `resources/views/pages/rombel-siswa/index.blade.php`
  5. `resources/views/pages/rombel-siswa/create.blade.php`
  6. `resources/views/pages/presensi/index.blade.php`
  7. `resources/views/pages/master/guru/show.blade.php`

## Proposed Changes

1. **Update `resources/views/pages/master/rombel/create.blade.php` & `edit.blade.php`**
   - **What:** Fix the `<option>` label in the `tahun_ajaran_id` select dropdown.
   - **How:** Replace `{{ $ta->tahun_mulai }}/{{ $ta->tahun_selesai }} - {{ ucfirst($ta->semester) }}` with `{{ $ta->nama }} - Semester {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}`.

2. **Update `resources/views/pages/master/rombel/show.blade.php`**
   - **What:** Fix the display of Tahun Ajaran in the Rombel detail view.
   - **How:** Replace `{{ $rombel->tahunAjaran->tahun_mulai }}/{{ $rombel->tahunAjaran->tahun_selesai }} - {{ ucfirst($rombel->tahunAjaran->semester) }}` with `{{ $rombel->tahunAjaran->nama }} - Semester {{ $rombel->tahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }}`.

3. **Update `resources/views/pages/rombel-siswa/index.blade.php` & `create.blade.php`**
   - **What:** Fix the display of Tahun Ajaran in filters and context headers.
   - **How:** Apply the same replacement: `{{ $ta->nama }} - Semester {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}`.

4. **Update `resources/views/pages/presensi/index.blade.php`**
   - **What:** Fix the display of Tahun Ajaran in the filter dropdown.
   - **How:** Apply the same replacement: `{{ $ta->nama }} - Semester {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}`.

5. **Update `resources/views/pages/master/guru/show.blade.php`**
   - **What:** Fix the display of Tahun Ajaran in the "Riwayat Wali Kelas" section.
   - **How:** Apply the same replacement using `$rombel->tahunAjaran->nama`.

## Assumptions & Decisions
- It is assumed that `semester` in the database is stored as an integer (1 for Ganjil/Odd, 2 for Genap/Even). Therefore, the display will format this into user-friendly text ("Semester Ganjil" / "Semester Genap").
- Using search and replace (or editing specific files) will be the most efficient method since the pattern is highly predictable.

## Verification Steps
1. Navigate to "Tambah Rombel" and ensure the "Tahun Ajaran" dropdown displays correctly (e.g., "2025/2026 - Semester Ganjil").
2. Navigate to "Rombel Siswa" and verify the filter dropdown.
3. Check the "Guru" detail page to ensure history displays correctly if the teacher is assigned to a rombel.
