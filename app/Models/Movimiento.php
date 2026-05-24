<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $fillable = [
        'user_id',
        'categoria_id',
        'cuenta_id',
        'meta_id',
        'tipo',
        'monto',
        'descripcion',
        'foto',
        'fecha',
    ];

    //Relacion de muchos a uno con el modelo usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //Relacion de muchos a uno con el modelo categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    //Relacion de muchos a uno con el modelo cuenta
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function metaAhorro()
    {
        return $this->belongsTo(MetaAhorro::class, 'meta_id');
    }
}
