<?php

namespace Database\Seeders;

use DateTimeImmutable;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class MasterDataSeeder extends Seeder
{
    private const FILE_NAME = 'DATA SISWA DAN GURU SMP MUHAMMADIYAH DANAU SIJABUT.xlsx';

    public function run(): void
    {
        $rows = $this->readSheet(base_path(self::FILE_NAME));
        $this->validateRows($rows);

        $studentGenders = $this->genderMap([
            'L' => [5, 6, 7, 13, 16, 17, 18, 19, 20, 21, 22, 23, 24, 26, 27, 29, 31, 33, 34, 36, 37, 39, 40, 43, 45, 46, 47, 48, 49, 50, 51, 53, 54, 58, 59, 60, 61, 62, 64, 68, 69, 70, 72, 74, 77, 79, 81, 83, 84, 87, 88, 92],
            'P' => [8, 9, 10, 11, 12, 14, 15, 25, 28, 30, 32, 35, 38, 41, 42, 44, 52, 55, 56, 57, 63, 65, 66, 67, 71, 73, 75, 76, 78, 80, 82, 85, 86, 89, 90, 91, 93],
        ]);
        $teacherGenders = $this->genderMap([
            'L' => [94, 98, 102, 103, 104, 105, 107],
            'P' => [95, 96, 97, 99, 100, 101, 106],
        ]);

        DB::transaction(function () use ($rows, $studentGenders, $teacherGenders): void {
            DB::table('presensi')->delete();
            DB::table('kartu_rfid')->delete();
            DB::table('rombel_siswa')->delete();
            DB::table('rombel')->delete();
            DB::table('siswa')->delete();
            DB::table('guru')->delete();

            $now = now();
            $tahunAjaran = DB::table('tahun_ajaran')->where('is_aktif', true)->orderByDesc('id')->first();
            if (! $tahunAjaran) {
                $id = DB::table('tahun_ajaran')->insertGetId([
                    'nama' => '2026/2027',
                    'semester' => 1,
                    'is_aktif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $tahunAjaran = DB::table('tahun_ajaran')->find($id);
            }

            $teacherIds = [];
            for ($row = 94; $row <= 107; $row++) {
                $teacherIds[$this->normalizeName($rows[$row]['B'])] = DB::table('guru')->insertGetId([
                    'nip' => $rows[$row]['A'],
                    'nama_lengkap' => $rows[$row]['B'],
                    'jenis_kelamin' => $teacherGenders[$row],
                    'no_hp' => null,
                    'foto' => null,
                    'user_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $waliByClass = [
                '7' => 'NILA SUSWITA',
                '8' => 'WIDIA GUSNIATI, S.Pd',
                '9' => 'MUHAIRANI, S.Pd',
            ];
            $rombelIds = [];
            foreach ($waliByClass as $class => $waliName) {
                $teacherId = $teacherIds[$this->normalizeName($waliName)] ?? null;
                if (! $teacherId) {
                    throw new RuntimeException("Wali kelas {$waliName} untuk kelas {$class} tidak cocok dengan data guru Excel.");
                }
                $rombelIds[$class] = DB::table('rombel')->insertGetId([
                    'nama' => $class,
                    'tingkat' => $class,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'wali_guru_id' => $teacherId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            for ($row = 5; $row <= 93; $row++) {
                $nisn = str_pad($rows[$row]['A'], 10, '0', STR_PAD_LEFT);
                $siswaId = DB::table('siswa')->insertGetId([
                    'nis' => $nisn,
                    'nisn' => $nisn,
                    'nama_lengkap' => $rows[$row]['B'],
                    'jenis_kelamin' => $studentGenders[$row],
                    'tempat_lahir' => null,
                    'tanggal_lahir' => $this->parseDate($rows[$row]['C'], $row),
                    'alamat' => null,
                    'nama_wali' => null,
                    'no_hp_wali' => null,
                    'foto_url' => null,
                    'status' => 'aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('rombel_siswa')->insert([
                    'rombel_id' => $rombelIds[$rows[$row]['D']],
                    'siswa_id' => $siswaId,
                    'tanggal_masuk' => '2026-07-13',
                    'tanggal_keluar' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    private function readSheet(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("File Excel tidak ditemukan: {$path}");
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException("File Excel tidak dapat dibuka: {$path}");
        }

        try {
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($workbookXml === false || $sheetXml === false) {
                throw new RuntimeException('Sheet Sheet1 tidak ditemukan di file Excel.');
            }
            $workbook = $this->loadXml($workbookXml, 'workbook');
            $workbookXPath = new DOMXPath($workbook);
            $sheetNames = [];
            foreach ($workbookXPath->query('//*[local-name()="sheet"]') as $sheet) {
                $sheetNames[] = $sheet->getAttribute('name');
            }
            if (! in_array('Sheet1', $sheetNames, true)) {
                throw new RuntimeException('Sheet Sheet1 tidak ditemukan di file Excel.');
            }

            $sharedStrings = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedXml !== false) {
                $shared = $this->loadXml($sharedXml, 'shared strings');
                $sharedXPath = new DOMXPath($shared);
                foreach ($sharedXPath->query('//*[local-name()="si"]') as $item) {
                    $value = '';
                    foreach ($sharedXPath->query('.//*[local-name()="t"]', $item) as $text) {
                        $value .= $text->textContent;
                    }
                    $sharedStrings[] = $value;
                }
            }

            $sheet = $this->loadXml($sheetXml, 'Sheet1');
            $xpath = new DOMXPath($sheet);
            $rows = [];
            foreach ($xpath->query('//*[local-name()="row"]') as $rowNode) {
                $rowNumber = (int) $rowNode->getAttribute('r');
                $rows[$rowNumber] = [];
                foreach ($xpath->query('./*[local-name()="c"]', $rowNode) as $cell) {
                    $reference = $cell->getAttribute('r');
                    preg_match('/^[A-Z]+/', $reference, $matches);
                    $column = $matches[0] ?? '';
                    $valueNode = $xpath->query('./*[local-name()="v"]', $cell)->item(0);
                    $value = $valueNode?->textContent ?? '';
                    if ($cell->getAttribute('t') === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    } elseif ($cell->getAttribute('t') === 'inlineStr') {
                        $value = '';
                        foreach ($xpath->query('.//*[local-name()="t"]', $cell) as $text) {
                            $value .= $text->textContent;
                        }
                    }
                    $rows[$rowNumber][$column] = trim($value);
                }
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function validateRows(array $rows): void
    {
        $expectedHeaders = ['A' => 'NISN/NIK', 'B' => 'Nama SISWA', 'C' => 'Tanggal Lahir', 'D' => 'KELAS', 'E' => 'WALI KELAS'];
        foreach ($expectedHeaders as $column => $expected) {
            if (($rows[4][$column] ?? null) !== $expected) {
                throw new RuntimeException("Header Excel tidak valid pada {$column}4; diharapkan '{$expected}'.");
            }
        }

        foreach (range(5, 107) as $row) {
            foreach (['A', 'B', 'C', 'D'] as $column) {
                if (($rows[$row][$column] ?? '') === '') {
                    throw new RuntimeException("Data Excel tidak lengkap pada sel {$column}{$row}.");
                }
            }
            if ($row <= 93 && ! in_array($rows[$row]['D'], ['7', '8', '9'], true)) {
                throw new RuntimeException("Kelas siswa pada D{$row} harus 7, 8, atau 9.");
            }
            $this->parseDate($rows[$row]['C'], $row);
        }

        if (count(range(5, 93)) !== 89 || count(range(94, 107)) !== 14) {
            throw new RuntimeException('Jumlah data siswa atau guru Excel tidak sesuai struktur yang diharapkan.');
        }
    }

    private function parseDate(string $value, int $row): string
    {
        $date = DateTimeImmutable::createFromFormat('!d-m-Y', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (! $date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new RuntimeException("Tanggal lahir pada C{$row} tidak valid; format harus d-m-Y.");
        }

        return $date->format('Y-m-d');
    }

    private function genderMap(array $rowsByGender): array
    {
        $map = [];
        foreach ($rowsByGender as $gender => $rows) {
            foreach ($rows as $row) {
                $map[$row] = $gender;
            }
        }

        return $map;
    }

    private function normalizeName(string $name): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/', ' ', $name)));
    }

    private function loadXml(string $xml, string $label): DOMDocument
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (! $loaded) {
            throw new RuntimeException("XML {$label} di file Excel tidak valid.");
        }

        return $document;
    }
}
