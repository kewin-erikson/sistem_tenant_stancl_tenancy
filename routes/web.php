<?php

use Illuminate\Support\Facades\Route;

// Livewire::setScriptRoute(function ($handle) {
//     // Obtiene la URL base de la aplicación desde el archivo .env
//     $baseUrl = config('app.url');
    
//     // Analiza la URL para extraer el segmento de la ruta si existe
//     $parsedUrl = parse_url($baseUrl);
//     $subfolder = isset($parsedUrl['path']) ? rtrim($parsedUrl['path'], '/') : '';

//     // Construye la ruta del script Livewire basándose en la presencia del segmento
//     $scriptRoute = $subfolder ? $subfolder . '/public/livewire/livewire.js' : '/public/livewire/livewire.js';

//     // Devuelve la ruta con el manejador de ruta proporcionado (Route::get)
//     return Route::get($scriptRoute, $handle);
// });

// Livewire::setUpdateRoute(function ($handle) {
//     // Obtiene la URL base de la aplicación desde el archivo .env
//     $baseUrl = config('app.url');
    
//     // Analiza la URL para extraer el segmento de la ruta si existe
//     $parsedUrl = parse_url($baseUrl);
//     $subfolder = isset($parsedUrl['path']) ? rtrim($parsedUrl['path'], '/') : '';

//     // Construye la ruta de actualización de Livewire basándose en la presencia del segmento
//     $updateRoute = $subfolder ? $subfolder . '/public/livewire/update' : '/public/livewire/update';

//     // Devuelve la ruta con el manejador de ruta proporcionado (Route::post)
//     return Route::post($updateRoute, $handle)->name('livewire.update.custom');
// });

Route::get('/', function () {
    return view('welcome');
});
Route::get('/tenant-manager', \App\Livewire\TenantManager::class);