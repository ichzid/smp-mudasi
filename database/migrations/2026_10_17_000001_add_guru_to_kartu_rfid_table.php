<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kartu_rfid', function (Blueprint $table) {
            $table->foreignId('siswa_id')->nullable()->change();
            $table->foreignId('guru_id')->nullable()->unique()->constrained('guru')->cascadeOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE kartu_rfid ADD CONSTRAINT kartu_rfid_one_owner CHECK ((siswa_id IS NOT NULL AND guru_id IS NULL) OR (siswa_id IS NULL AND guru_id IS NOT NULL))');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TRIGGER kartu_rfid_one_owner_insert BEFORE INSERT ON kartu_rfid WHEN (NEW.siswa_id IS NULL) = (NEW.guru_id IS NULL) BEGIN SELECT RAISE(ABORT, \'Kartu RFID harus memiliki tepat satu pemilik\'); END');
            DB::statement('CREATE TRIGGER kartu_rfid_one_owner_update BEFORE UPDATE ON kartu_rfid WHEN (NEW.siswa_id IS NULL) = (NEW.guru_id IS NULL) BEGIN SELECT RAISE(ABORT, \'Kartu RFID harus memiliki tepat satu pemilik\'); END');
        }
    }

    public function down(): void
    {
        if (DB::table('kartu_rfid')->whereNotNull('guru_id')->exists()) {
            throw new RuntimeException('Hapus atau pindahkan kartu RFID guru sebelum rollback migrasi ini.');
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE kartu_rfid DROP CHECK kartu_rfid_one_owner');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TRIGGER kartu_rfid_one_owner_insert');
            DB::statement('DROP TRIGGER kartu_rfid_one_owner_update');
        }

        Schema::table('kartu_rfid', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->dropUnique(['guru_id']);
            $table->dropColumn('guru_id');
            $table->foreignId('siswa_id')->nullable(false)->change();
        });
    }
};
