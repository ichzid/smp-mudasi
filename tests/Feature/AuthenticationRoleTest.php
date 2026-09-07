<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu ke login dari route terautentikasi', function () {
    $this->get(route('master.siswa.index'))->assertRedirect(route('login'));
});

it('dapat login hanya menggunakan username dan password', function () {
    $password = fake()->password(12);
    $user = User::factory()->create([
        'username' => 'admin',
        'email' => null,
        'password' => bcrypt($password),
        'role' => 'admin',
    ]);

    $this->post(route('login'), [
        'username' => 'admin',
        'password' => $password,
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('tidak dapat login menggunakan email', function () {
    $password = fake()->password(12);
    User::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt($password),
    ]);

    $this->post(route('login'), [
        'email' => 'admin@example.com',
        'password' => $password,
    ])->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('membatasi route master berdasarkan role', function () {
    $waliKelas = User::factory()->create(['role' => 'wali_kelas']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($waliKelas)->get(route('master.siswa.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('master.siswa.index'))->assertOk();
});
