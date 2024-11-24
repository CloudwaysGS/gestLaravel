<?php

namespace App\Http\Controllers;

use App\Models\Dette;
use App\Models\Paiement;
use App\Models\Produit;
use App\Services\PaiementValidationService;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paiement = Paiement::orderBy('created_at', 'desc')->get();
        return view('paiement.liste', compact('paiement'));
    }

    protected $paiementValidationService;

    public function __construct(PaiementValidationService $paiementValidationService)
    {
        $this->paiementValidationService = $paiementValidationService;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $this->paiementValidationService->validate($request->all());

        // Récupération de la dette
        $dette = Dette::findOrFail($validatedData['id']); // Utilisez `findOrFail` pour éviter les erreurs silencieuses

        // Validation des montants
        if (!isset($validatedData['montant']) || $validatedData['montant'] <= 0) {
            return back()->withErrors('Le montant est invalide.')->withInput();
        }

        if ($dette->reste <= 0) {
            notify()->error('Dette déjà payée.');
            return redirect()->route('dette.liste');
        }
        $reste = $dette->reste - $validatedData['montant'];

        if ($reste < 0) {
            notify()->error('Le montant ne peut pas dépasser le reste dû.');
            return redirect()->route('dette.liste');
        }
        if ($reste == 0) {
            $dette->etat = 'payée';
        }
        // Mise à jour de la dette
        $dette->update([
            'reste' => $reste,
            'etat' => $dette->etat,
        ]);

        // Création du paiement
        Paiement::create([
            'montant' => $validatedData['montant'],
            'reste' => $reste,
            'dette_id' => $dette->id,
        ]);

        // Notification et redirection
        notify()->success('Paiement effectué avec succès.');
        return redirect()->route('dette.liste');
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function paiement(string $id)
    {
        $paiement = Dette::find($id);
        return view('paiement.ajout', compact('paiement','id'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
