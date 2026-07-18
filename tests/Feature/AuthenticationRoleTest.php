<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu ke login dari route terautentikasi', function () {
    $this->get(route('master.siswa.index'))->assertRedirect(route('login'));
});

it('membatasi route master berdasarkan role', function () {
    $waliKelas = User::factory()->create(['role' => 'wali_kelas']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($waliKelas)->get(route('master.siswa.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('master.siswa.index'))->assertOk();
});
