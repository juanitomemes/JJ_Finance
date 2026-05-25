<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/crear-admin', function () {
    $user = \App\Models\User::updateOrCreate(
        ['email' => 'admin@admin.com'],
        [
            'name' => 'Administrador',
            'password' => bcrypt('password123'),
        ]
    );

    return "✅ Usuario administrador creado exitosamente.<br><br>Email: admin@admin.com<br>Contraseña: password123<br><br><a href='/admin'>Ir al Login</a>";
});
