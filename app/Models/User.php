<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Movimiento;
use App\Models\Presupuesto;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        // En producción, puedes limitar el acceso por email o rol.
        // Aquí permitiremos el acceso al panel a todos los usuarios registrados.
        return true;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación uno a muchos con movimientos
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }

    // Relación uno a muchos con presupuestos
    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class);
    }

    // Relación uno a muchos con categorías personalizadas
    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }

    // Relación uno a muchos con cuentas
    public function cuentas()
    {
        return $this->hasMany(Cuenta::class);
    }
}
