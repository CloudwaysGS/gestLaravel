<?php

namespace App\Http\Controllers;

use App\Models\Entree;
use App\Models\Produit;
use App\Services\EntreeValidationService;
use Illuminate\Http\Request;

class EntreeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entree = Entree::orderBy('created_at', 'desc')->get();
        return view('entree.liste', compact('entree'));
    }

    protected $entreeValidationService;

    public function __construct(EntreeValidationService $entreeValidationService)
    {
        $this->entreeValidationService = $entreeValidationService;
    }
        /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $this->entreeValidationService->validate($request->all());

        // Recherche du produit
        $produit = Produit::find($validatedData['nom']); // Utilisation de `find` pour simplifier la recherche
        if (!$produit) {
            return redirect()->back()->withErrors(['nom' => 'Produit introuvable']);
        }

        // Mise à jour de la quantité du produit
        $produit->increment('qteProduit', $validatedData['qteEntree']);

        // Calcul du total et création de l'entrée
        Entree::create([
            'produit_id' => $produit->id, // Utilisation directe de l'ID du produit
            'qteEntree' => $validatedData['qteEntree'],
            'prix' => $validatedData['prix'],
            'total' => $validatedData['qteEntree'] * $validatedData['prix'],
        ]);

        // Redirection avec notification de succès
        notify()->success('Entrée créée avec succès.');
        return redirect()->route('entree.liste');
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
        $entree = Entree::find($id);
        return view('entree.modifier', compact('entree'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $produit = Entree::find($id);
        $this->entreeValidationService->validate($request->all());

        //$produit->nom = $request->input('produit_id');
        $produit->qteEntree = $request->input('qteEntree');
        $produit->prix = $request->input('prix');

        $produit->save();

        notify()->success('Entree modifié avec succès.');
        return redirect()->route('entree.liste');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $delete = Entree::find($id);
        $delete->delete();
        notify()->success('entree supprimé avec succès.');
        return redirect()->route('entree.liste');
    }
}
