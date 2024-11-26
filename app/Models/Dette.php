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

    public static function searchByName($search = null)
    {
        $query = self::query();

        if ($search) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('nom', 'like', '%' . $search . '%');
            });
        }

        return $query->with('client')->orderBy('etat', 'asc')->orderBy('created_at', 'desc')->get();
    }


}
