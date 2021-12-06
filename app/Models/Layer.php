<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layer extends Model
{
    use HasFactory;

    public function mixer()
    {
        return $this->belongsTo(Mixer::class, 'mixerId');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class, 'layerId');
    }
}
