<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->convert([
            'VII' => '7',
            'VIII' => '8',
            'IX' => '9',
        ]);
    }

    public function down(): void
    {
        $this->convert([
            '7' => 'VII',
            '8' => 'VIII',
            '9' => 'IX',
        ]);
    }

    private function convert(array $map): void
    {
        DB::table('rombel')->select('id', 'nama', 'tingkat')->orderBy('id')->chunkById(100, function ($rombels) use ($map): void {
            foreach ($rombels as $rombel) {
                $tingkat = $map[$rombel->tingkat] ?? $rombel->tingkat;
                $nama = $map[$rombel->nama] ?? $rombel->nama;

                foreach ($map as $from => $to) {
                    if (str_starts_with($nama, $from.' ')) {
                        $nama = $to.substr($nama, strlen($from));
                        break;
                    }
                }

                if ($tingkat !== $rombel->tingkat || $nama !== $rombel->nama) {
                    DB::table('rombel')->where('id', $rombel->id)->update([
                        'tingkat' => $tingkat,
                        'nama' => $nama,
                    ]);
                }
            }
        });
    }
};
