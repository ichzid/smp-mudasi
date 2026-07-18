<?php

use Illuminate\Support\Facades\Route;

it('melindungi seluruh route laporan dengan auth dan role laporan', function () {
    foreach (['laporan.index', 'laporan.csv', 'laporan.print'] as $name) {
        $route = Route::getRoutes()->getByName($name);

        expect($route)->not->toBeNull()
            ->and($route->gatherMiddleware())->toContain('auth', 'role:admin,tu,wali_kelas');
    }
});
