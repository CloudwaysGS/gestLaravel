<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Dette;
use App\Models\Facture;
use App\Models\Produit;
use App\Services\FactureValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $facture = Facture::where('etat', 1)->orderBy('created_at', 'desc')->get();
        $produits = Produit::all();
        $clients = Client::all();
        $totalMontants = Facture::where('etat', 1)->sum('montant');
        return view('facture.liste', compact('facture', 'produits', 'clients', 'totalMontants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    protected $factureValidationService;

    public function __construct(FactureValidationService $factureValidationService)
    {
        $this->factureValidationService = $factureValidationService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $factures = Facture::where('etat', 1)->with('client')->get();
        // Vérifier si le champ client_id est null et s'il n'y a pas de factures en cours
        if ($request->client_id == null && $factures->isEmpty()) {
            notify()->error('Choisir un client svp!!!');
            return redirect()->route('facture.liste');
        }


        // Validation des données
        $validatedData = $this->factureValidationService->validate($request->all());

        DB::beginTransaction();

        try {
            // Recherche du produit
            $produit = Produit::findOrFail($validatedData['nom']);

            // Si un client est fourni dans la requête, le récupérer
            if ($request->client_id) {
                $client = Client::findOrFail($request['client_id']);
            }else{
                // Si des factures existent déjà, récupérer le client associé à la première facture
                $client = null;
                if ($factures->isNotEmpty()) {
                    $client = $factures->first()->client; // Récupérer le client de la première facture
                }
            }

            // Calcul du montant
            $montant = $validatedData['quantite'] * $produit->prixProduit;

            // Vérification de la quantité disponible
            if ($produit->qteProduit < $validatedData['quantite']) {
                notify()->error('Quantité demandée non disponible en stock. Disponible: ' . $produit->qteProduit);
                return redirect()->route('facture.liste');
            }

            // Mise à jour du stock
            $produit->decrement('qteProduit', $validatedData['quantite']);

            // Calcul des montants totaux des factures en cours
            $totalMontants = Facture::where('etat', 1)->sum('montant');

            // Création de la facture
            Facture::create([
                'nom' => $produit->nom,
                'quantite' => $validatedData['quantite'],
                'client_id' => $client ? $client->id : null, // Utiliser le client existant ou null
                'prix' => $produit->prixProduit,
                'montant' => $montant,
                'etat' => 1,
                'nomClient' => $client ? $client->nom : null, // Utiliser le nom du client existant ou null
                'total' => $totalMontants + $montant,
                'produit_id' => $validatedData['nom'],
            ]);

            DB::commit();

            notify()->success('Facture créée avec succès.');
            return redirect()->route('facture.liste');
        } catch (\Exception $e) {
            DB::rollBack();
            notify()->error('Une erreur est survenue : ' . $e->getMessage());
            return redirect()->route('facture.liste');
        }
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
        $facture = Facture::find($id);
        return view('facture.modifier', compact('facture'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validation des données
        $validatedData = $this->factureValidationService->validate($request->all());

        $factue = Facture::findOrFail($id);

        $qteInitiale = $factue->quantite;
        $qteNouvelle = $validatedData['quantite'];

        // Récupération du produit lié
        $produit = Produit::findOrFail($factue->produit_id);

        // Mise à jour des informations de la facture
        $factue->nom = $request->input('nom');
        $factue->quantite = $qteNouvelle;
        $factue->prix = $request->prix; // On récupère le prix actuel du produit
        $factue->montant = $factue->prix * $qteNouvelle;

        // Gestion du stock du produit
        $produit->qteProduit += $qteInitiale; // On rétablit la quantité initiale dans le stock
        $produit->qteProduit -= $qteNouvelle; // On retire la nouvelle quantité du stock

        if ($produit->qteProduit < 0) {
            notify()->error('Stock insuffisant pour la quantité demandée.');
            return redirect()->route('facture.liste');
        }

        // Sauvegarde des modifications
        $factue->save();
        $produit->save();

        notify()->success('Facture mise à jour avec succès.');
        return redirect()->route('facture.liste');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Récupérer la facture
        $facture = Facture::find($id);

        if (!$facture) {
            notify()->error('Facture introuvable.');
            return redirect()->route('facture.liste');
        }

        // Récupérer le produit associé
        $produit = Produit::find($facture->produit_id);

        if ($produit) {
            // Rétablir la quantité dans le stock
            $produit->qteProduit += $facture->quantite;
            $produit->save();
        }

        // Supprimer la facture
        $facture->delete();

        notify()->success('Facture supprimée avec succès et stock mis à jour.');
        return redirect()->route('facture.liste');
    }

    public function deleteAll()
    {
        // Récupérer les factures avec etat = 1
        $factures = Facture::where('etat', 1)->get();

        // Vérifier s'il y a des factures à supprimer
        if ($factures->isNotEmpty()) {
            foreach ($factures as $facture) {
                // Récupérer le produit associé
                $produit = Produit::find($facture->produit_id);
                if ($produit) {
                    // Rétablir la quantité dans le stock
                    $produit->qteProduit += $facture->quantite;

                    $produit->save();
                }

                // Supprimer la facture
                $facture->delete();
            }

            // Message de succès
            notify()->success('Toutes les factures ont été supprimées et les stocks ont été rétablis avec succès.');
        } else {
            // Message si aucune facture à supprimer
            notify()->warning('Aucune facture n\'a été trouvée.');
        }

        // Redirige vers la liste des factures
        return redirect()->route('facture.liste');
    }




}
