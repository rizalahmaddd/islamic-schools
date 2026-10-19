<?php

use App\Support\Branding;

test('public landing page is accessible at root URL', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee(Branding::appName())
        ->assertSee('PPDB')
        ->assertSee('Tahfidz')
        ->assertSee('Keasramaan')
        ->assertSee('Program Pendidikan')
        ->assertSee('Fasilitas Lengkap')
        ->assertSee(route('ppdb.register'))
        ->assertSee(route('login'));
});

test('public landing page has meta tags and correct title', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('<meta name="description"', false)
        ->assertSee('viewport', false);
});

test('ppdb page has return navigation to landing page and consistent ui', function () {
    $response = $this->get('/ppdb');

    $response->assertOk()
        ->assertSee('Kembali ke Beranda')
        ->assertSee('Kembali ke Beranda Utama')
        ->assertSee(route('home'))
        ->assertSee('Pendaftaran Santri Baru (PPDB)')
        ->assertSee('bg-islamic-pattern')
        ->assertSee('Navigasi Seluler PPDB');
});
