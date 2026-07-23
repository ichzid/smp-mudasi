<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu ke login dari route terautentikasi', function () {
    $this->get(route('master.siswa.index'))->assertRedirect(route('login'));
});

it('dapat login menggunakan email dan password', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password-rahasia'),
        'role' => 'admin',
    ]);

    $this->post(route('login'), [
        'email' => 'admin@example.com',
        'password' => 'password-rahasia',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('membatasi route master berdasarkan role', function () {
    $waliKelas = User::factory()->create(['role' => 'wali_kelas']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($waliKelas)->get(route('master.siswa.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('master.siswa.index'))->assertOk();
});
