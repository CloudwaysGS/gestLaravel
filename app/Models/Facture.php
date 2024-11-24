<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'montant_total', 'reste_a_payer', 'etat', 'date_emission'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    /*public function ajouterPaiement($montant)
    {
        $this->reste_a_payer -= $montant;

        if ($this->reste_a_payer <= 0) {
            $this->etat = 'payée';
            $this->reste_a_payer = 0;
        } else {
            $this->etat = 'partiellement payée';
        }

        $this->save();
    }*/
}
