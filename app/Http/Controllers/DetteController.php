<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Dette;
use App\Services\DetteValidationService;
use Illuminate\Http\Request;

class DetteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Utilisation de la méthode statique pour rechercher et trier les données
        $dette = Dette::searchByName($request->search);

        return view('dette.liste', compact('dette'));
    }

    public function searchAjax(Request $request)
    {
        $query = $request->query('query');

        $dette = Dette::searchByName($query);

        return response()->json($dette);
    }



    protected $detteValidationService;

    public function __construct(DetteValidationService $detteValidationService)
    {
        $this->detteValidationService = $detteValidationService;
    }

    public function store(Request $request)
    {

            $validatedData = $this->detteValidationService->validate($request->all());
            $client = Client::findOrFail($validatedData['client_id']);

            Dette::create([
                'client_id' => $validatedData['client_id'],
                'nom' => $client['nom'],
                'montant' => $validatedData['montant'],
                'reste' => $validatedData['montant'],
                'commentaire' => $validatedData['commentaire'],
                'etat' => 'impayée',
            ]);

            notify()->success('Dette créée avec succès.');
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
