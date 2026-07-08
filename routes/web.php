<?php

use Illuminate\Support\Facades\Route;

// Serves the SPA shell for every browser-navigable path. Excludes api/*
// (routed separately), storage/build (static assets), and up (health check).
Route::view('/{any}', 'welcome')
    ->where('any', '^(?!api|storage|build|up).*$')
    ->name('spa');
