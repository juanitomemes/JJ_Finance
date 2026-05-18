<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'user_id',
    ];

     //Relacion de muchos a uno con el modelo user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     //Relacion de uno a muchos con el modelo movimiento
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}
