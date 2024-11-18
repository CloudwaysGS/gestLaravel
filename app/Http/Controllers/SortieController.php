<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Sortie;
use App\Services\SortieValidationService;
use Illuminate\Http\Request;

class SortieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sortie = Sortie::all();
        return view('sortie.liste', compact('sortie'));
    }

    protected $entreeValidationService;

    public function __construct(SortieValidationService $sortieValidationService)
    {
        $this->sortieValidationService = $sortieValidationService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $this->sortieValidationService->validate($request->all());

        // Recherche du produit par son ID (assurez-vous que 'nom' est un ID valide)
        $produit = Produit::find($validatedData['nom']); // Utilisation de `find` pour simplifier la recherche
        if (!$produit) {
            return redirect()->back()->withErrors(['nom' => 'Produit introuvable']);
        }

        // Vérifier que la quantité en stock est suffisante pour la sortie
        if ($produit->qteProduit < $validatedData['qteSortie']) {
            return redirect()->back()->withErrors(['qteSortie' => 'La quantité demandée dépasse le stock disponible.']);
        }

        // Mise à jour de la quantité du produit (décroissance)
        $produit->decrement('qteProduit', $validatedData['qteSortie']);

        // Calcul du total et création de la sortie
        Sortie::create([
            'produit_id' => $produit->id,  // Utilisation correcte de l'ID du produit
            'qteSortie' => $validatedData['qteSortie'],
            'prix' => $validatedData['prix'],
            'total' => $validatedData['qteSortie'] * $validatedData['prix'],
        ]);

        // Redirection avec notification de succès
        notify()->success('Sortie créée avec succès.');
        return redirect()->route('sortie.liste');  // Assurez-vous que la route 'sortie.liste' existe
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
