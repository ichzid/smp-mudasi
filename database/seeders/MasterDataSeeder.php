<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Tahun Ajaran
        $tahunAjarans = [
            [
                'nama' => '2023/2024',
                'semester' => 1,
                'is_aktif' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => '2023/2024',
                'semester' => 2,
                'is_aktif' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => '2024/2025',
                'semester' => 1,
                'is_aktif' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => '2024/2025',
                'semester' => 2,
                'is_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        DB::table('tahun_ajaran')->insert($tahunAjarans);

        // 2. Guru
        $gurus = [];
        for ($i = 1; $i <= 15; $i++) {
            $gurus[] = [
                'nip' => '19' . $faker->numberBetween(70, 95) . $faker->numberBetween(10, 12) . $faker->numberBetween(10, 31) . '20' . $faker->numberBetween(1, 2) . '100' . $i,
                'nama_lengkap' => $faker->name,
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'no_hp' => $faker->phoneNumber,
                'foto' => null,
                'user_id' => null, // Optional, could link to a user account
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('guru')->insert($gurus);
        
        $guruIds = DB::table('guru')->pluck('id')->toArray();

        // 3. Siswa
        $siswas = [];
        for ($i = 1; $i <= 150; $i++) {
            $siswas[] = [
                'nis' => '2024' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nisn' => $faker->unique()->numerify('00######' . $i),
                'nama_lengkap' => $faker->name,
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->dateTimeBetween('-15 years', '-12 years')->format('Y-m-d'),
                'alamat' => $faker->address,
                'foto_url' => null,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('siswa')->insert($siswas);

        // 4. Rombel (untuk Tahun Ajaran Aktif)
        $tahunAjaranAktif = DB::table('tahun_ajaran')->where('is_aktif', true)->first();
        
        if ($tahunAjaranAktif && count($guruIds) >= 9) {
            $rombels = [
                ['tingkat' => 'VII', 'nama' => 'VII A', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[0]],
                ['tingkat' => 'VII', 'nama' => 'VII B', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[1]],
                ['tingkat' => 'VII', 'nama' => 'VII C', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[2]],
                ['tingkat' => 'VIII', 'nama' => 'VIII A', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[3]],
                ['tingkat' => 'VIII', 'nama' => 'VIII B', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[4]],
                ['tingkat' => 'VIII', 'nama' => 'VIII C', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[5]],
                ['tingkat' => 'IX', 'nama' => 'IX A', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[6]],
                ['tingkat' => 'IX', 'nama' => 'IX B', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[7]],
                ['tingkat' => 'IX', 'nama' => 'IX C', 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'wali_guru_id' => $guruIds[8]],
            ];

            foreach ($rombels as $rombel) {
                $rombel['created_at'] = now();
                $rombel['updated_at'] = now();
                DB::table('rombel')->insert($rombel);
            }
        }
        
        // 5. Rombel Siswa (Memasukkan siswa ke rombel)
        if ($tahunAjaranAktif) {
            $rombelIds = DB::table('rombel')->pluck('id')->toArray();
            $siswaIds = DB::table('siswa')->pluck('id')->toArray();
            
            $rombelSiswas = [];
            // Distribusikan siswa ke rombel secara merata
            foreach ($siswaIds as $index => $siswaId) {
                // Pilih rombel berdasarkan index siswa (berputar dari 0 ke jumlah rombel)
                $rombelId = $rombelIds[$index % count($rombelIds)];
                
                $rombelSiswas[] = [
                    'rombel_id' => $rombelId,
                    'siswa_id' => $siswaId,
                    'tanggal_masuk' => $tahunAjaranAktif->created_at,
                    'tanggal_keluar' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Chunk insert untuk data besar
            $chunks = array_chunk($rombelSiswas, 50);
            foreach ($chunks as $chunk) {
                DB::table('rombel_siswa')->insert($chunk);
            }
        }

        // 6. Kartu RFID
        $kartuRfids = [];
        // Berikan kartu RFID hanya untuk setengah dari jumlah siswa sebagai simulasi
        $siswaRfids = array_slice($siswaIds, 0, 75); 
        
        foreach ($siswaRfids as $index => $siswaId) {
            $kartuRfids[] = [
                'siswa_id' => $siswaId,
                'kode_uid' => strtoupper($faker->unique()->bothify('??##??##??')),
                'status' => 'aktif',
                'diterbitkan_pada' => now()->subDays(rand(1, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('kartu_rfid')->insert($kartuRfids);
    }
}
