<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    public function layer()
    {
        return $this->belongsTo(Layer::class, 'layerId');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'nodeId');
    }
}
