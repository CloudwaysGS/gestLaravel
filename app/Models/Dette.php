<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dette extends Model
{
    use HasFactory;

    protected $fillable = ['client_id','nom', 'montant', 'reste', 'commentaire', 'etat'];


    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
