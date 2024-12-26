<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'qteProduit',
        'prixProduit',
        'montant',
        'prixAchat',
    ];

    public static function searchByName($search = null)
    {
        return self::query()
            ->when($search, function ($query, $search) {
                $query->where('nom', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
