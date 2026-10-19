<?php

use App\Http\Controllers\BrandingLogoController;
use App\Livewire\Dashboard;
use App\Support\Branding;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Halaman Publik (Landing Page Profil Sekolah & Pesantren)
Route::view('/', 'public.index')->name('home');

// Logo dari Pengaturan Perusahaan; publik karena dipakai juga di halaman login dan favicon.
Route::get('branding/logo', BrandingLogoController::class)->name('branding.logo');

// PWA webmanifest dan service worker
Route::get('manifest.webmanifest', function () {
    $appName = Branding::appName();
    $icon = Branding::iconUrl();

    return response()->json([
        'name' => $appName,
        'short_name' => Str::words($appName, 2, ''),
        'description' => Branding::tagline() ?? $appName,
        'start_url' => '/dashboard',
        'scope' => '/',
        'display' => 'standalone',
        'orientation' => 'portrait-primary',
        'background_color' => '#020617',
        'theme_color' => '#059669',
        'icons' => Branding::logoUrl()
            ? [['src' => $icon, 'sizes' => 'any', 'purpose' => 'any']]
            : [
                ['src' => asset('icons/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('icons/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
    ], 200, [], JSON_UNESCAPED_SLASHES)->header('Content-Type', 'application/manifest+json');
})->name('pwa.manifest');
Route::get('sw.js', fn () => response(file_get_contents(public_path('sw.js')), 200, [
    'Content-Type' => 'application/javascript',
    'Cache-Control' => 'no-cache',
]))->name('pwa.sw');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
require __DIR__.'/master-data.php';
require __DIR__.'/attendance.php';
require __DIR__.'/academic.php';
require __DIR__.'/finance.php';
require __DIR__.'/islamic.php';
require __DIR__.'/reports.php';
require __DIR__.'/settings.php';
require __DIR__.'/portal.php';
require __DIR__.'/boarding.php';
require __DIR__.'/admissions.php';
require __DIR__.'/inventory.php';
require __DIR__.'/library.php';
require __DIR__.'/cashless.php';
