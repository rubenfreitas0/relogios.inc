<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Servir ficheiros do disco "public" sem depender do symlink storage:link
|--------------------------------------------------------------------------
|
| Usa um prefixo próprio (/media) — nunca colide com o symlink public/storage
| (que dá problemas de permissões no Windows). O ficheiro é lido diretamente
| de storage/app/public e devolvido pelo Laravel. Funciona em qualquer SO.
|
*/
Route::get('/media/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('media');
