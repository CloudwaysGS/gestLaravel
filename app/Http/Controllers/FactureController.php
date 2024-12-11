<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Dette;
use App\Models\Facture;
use App\Models\Produit;
use App\Services\FactureValidationService;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $details = Produit::select('id', 'nomDetail')
            ->distinct()
            ->where('nomDetail', '!=', '') // Élimine les chaînes vides
            ->get();

        $totalMontants = Facture::where('etat', 1)->sum('montant');
        return view('facture.liste', compact('facture', 'produits', 'clients', 'details', 'totalMontants'));
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

        if (is_null($request->client_id) && $factures->isEmpty()) {
            $message = 'Choisir un client svp!!!';
            return $request->ajax()
                ? response()->json(['error' => $message], 400)
                : redirect()->route('facture.liste')->withErrors($message);
        }

        $validatedData = $this->factureValidationService->validate($request->all());
        $produitId = $request->nom ?? $validatedData['nomDetail'];
        $prixField = $request->nom ? 'prixProduit' : 'prixDetail';
        $qteField = $request->nom ? 'qteProduit' : 'qteDetail';

        DB::beginTransaction();

        try {
            $produit = Produit::findOrFail($produitId);
            $client = $request->client_id
                ? Client::findOrFail($request->client_id)
                : ($factures->isNotEmpty() ? $factures->first()->client : null);

            $nomFacture = $request->nom ? $produit->nom : $produit->nomDetail;
            $existingFacture = Facture::where('nom', $nomFacture)->where('etat', 1)->first();

            if ($existingFacture) {
                $message = 'Une facture avec ce nom existe déjà.';
                return $request->ajax()
                    ? response()->json(['error' => $message], 400)
                    : redirect()->route('facture.liste')->withErrors($message);
            }

            $montant = $validatedData['quantite'] * $produit->$prixField;

            if ($produit->$qteField < $validatedData['quantite']) {
                $message = 'Quantité demandée non disponible en stock.';
                return $request->ajax()
                    ? response()->json(['error' => $message], 400)
                    : redirect()->route('facture.liste')->withErrors($message);
            }

            $totalMontants = Facture::where('etat', 1)->sum('montant');

            $facture = Facture::create([
                'nom' => $request->nom ? $produit->nom : $produit->nomDetail,
                'quantite' => $validatedData['quantite'],
                'client_id' => $client?->id,
                'prix' => $produit->$prixField,
                'montant' => $montant,
                'etat' => 1,
                'nomClient' => $client?->nom,
                'total' => $totalMontants + $montant,
                'produit_id' => $produitId,
            ]);

            DB::commit();

            return $request->ajax()
                ? response()->json(['success' => 'Facture créée avec succès.', 'facture' => $facture])
                : redirect()->route('facture.liste')->with('success', 'Facture créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            $message = 'Une erreur est survenue : ' . $e->getMessage();
            return $request->ajax()
                ? response()->json(['error' => $message], 500)
                : redirect()->route('facture.liste')->withErrors($message);
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
        $facture = Facture::findOrFail($id);

        // Vérifiez si seulement le prix est mis à jour
        if ($request->has('prix') || $request->has('quantite')) {
            $fields = $request->only(['prix', 'quantite']);

            // Validation des champs reçus
            $request->validate([
                'prix' => 'nullable|numeric|min:0',
                'quantite' => 'nullable|numeric|min:0',
            ]);

            // Mettre à jour le prix ou la quantité
            if (isset($fields['prix'])) {
                $facture->prix = $fields['prix'];
            }
            if (isset($fields['quantite'])) {
                $facture->quantite = $fields['quantite'];
            }

            // Recalculer le montant pour cette facture
            $facture->montant = $facture->prix * $facture->quantite;
            $facture->save();

            // Recalculer le total des montants pour toutes les factures actives
            $totalMontants = Facture::where('etat', 1)->sum('montant');

            return response()->json([
                'success' => true,
                'message' => 'Mise à jour effectuée avec succès.',
                'newMontant' => $facture->montant, // Nouveau montant individuel
                'totalMontants' => number_format($totalMontants, 2) // Nouveau total global
            ]);
        }

        // Validation des données
        $validatedData = $this->factureValidationService->validate($request->all());

        $qteInitiale = $facture->quantite;
        $qteNouvelle = $validatedData['quantite'];

        // Récupération du produit lié
        $produit = Produit::findOrFail($facture->produit_id);

        if ($validatedData['nom'] == $produit->nomDetail){
            $facture->nom = $request->input('nom');
            $facture->quantite = $qteNouvelle;
            $facture->prix = $request->prix; // On récupère le prix actuel du produit
            $facture->montant = $facture->prix * $qteNouvelle;

            $diffQte = $qteNouvelle - $qteInitiale;
            if($diffQte > 0){
                $newQteDetail = $produit->qteDetail - $diffQte;
            }elseif ($diffQte < 0){
                $newQteDetail = $produit->qteDetail + abs($diffQte);
            }elseif ($diffQte == 0){

                notify()->success('Facture mise à jour avec succès.');
                return redirect()->route('facture.liste');
            }
            $facture->save();

            //Mise à jour produit
            $produit->qteDetail = $newQteDetail;
            $produit->qteProduit = $newQteDetail / $produit->nombre;
            $produit->nbreVendu = abs($diffQte) / $produit->nombre;
            $produit->montant = $produit->qteProduit * $produit->prixProduit;

            $produit->save();

            notify()->success('Facture mise à jour avec succès.');
            return redirect()->route('facture.liste');
        }

        // Mise à jour des informations de la facture
        $facture->nom = $request->input('nom');
        $facture->quantite = $qteNouvelle;
        $facture->prix = $request->prix; // On récupère le prix actuel du produit
        $facture->montant = $facture->prix * $qteNouvelle;

        // Gestion du stock du produit
        $produit->qteProduit += $qteInitiale; // On rétablit la quantité initiale dans le stock
        $produit->qteProduit -= $qteNouvelle; // On retire la nouvelle quantité du stock

        if ($produit->qteProduit < 0) {
            notify()->error('Stock insuffisant pour la quantité demandée.');
            return redirect()->route('facture.liste');
        }

        // Sauvegarde des modifications
        $facture->save();
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

        if($produit->nomDetail == $facture->nom){
            $restoreQteProduit = $facture->quantite / $produit->nombre;
            $produit->qteProduit = $restoreQteProduit + $produit->qteProduit;
            $produit->qteDetail = $produit->qteProduit * $produit->nombre;
            $produit->montant = $produit->qteProduit * $produit->prixProduit;

            $produit->save();

        }
        if ($produit->nom == $facture->nom) {
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
