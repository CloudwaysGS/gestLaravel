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

        // Vérifier si le champ client_id est null et s'il n'y a pas de factures en cours
        if (is_null($request->client_id) && $factures->isEmpty()) {
            notify()->error('Choisir un client svp!!!');
            return redirect()->route('facture.liste');
        }

        // Validation des données
        $validatedData = $this->factureValidationService->validate($request->all());
        // Identifier le produit (nom ou nomDetail)
        $produitId = $request->nom ?? $validatedData['nomDetail'];
        $prixField = $request->nom ? 'prixProduit' : 'prixDetail';
        $qteField = $request->nom ? 'qteProduit' : 'qteDetail';

        DB::beginTransaction();

        try {
            // Recherche du produit
            $produit = Produit::findOrFail($produitId);

            // Récupérer le client (soit à partir du champ, soit à partir des factures existantes)
            $client = $request->client_id
                ? Client::findOrFail($request->client_id)
                : ($factures->isNotEmpty() ? $factures->first()->client : null);

            // Vérifier la duplication de facture
            $nomFacture = $request->nom ? $produit->nom : $produit->nomDetail;
            $existingFacture = Facture::where('nom', $nomFacture)->where('etat', 1)->first();

            if ($existingFacture) {
                notify()->error('Une facture avec ce nom existe déjà.');
                return redirect()->route('facture.liste');
            }


            // Calcul du montant
            $montant = $validatedData['quantite'] * $produit->$prixField;

            // Vérification de la quantité disponible
            if ($produit->$qteField < $validatedData['quantite']) {
                notify()->error('Quantité demandée non disponible en stock. Disponible: ' . $produit->$qteField);
                return redirect()->route('facture.liste');
            }

            // Mise à jour du stock détail
            $nombre = $produit->nombre;
            $qte = $validatedData['quantite'];

            if (!is_null($validatedData['nomDetail'])) {
                // Vérification que le nombre est supérieur à zéro
                if ($nombre <= 0) {
                    notify()->error('Le nombre ne peut pas être zéro.');
                    return redirect()->route('facture.liste');
                }

                $vendus = $qte / $nombre;

                if ($produit->qteProduit >= $vendus && $produit->qteDetail >= $qte) {
                    $produit->update([
                        'qteProduit' => $produit->qteProduit - $vendus,
                        'nbreVendu' => $vendus,
                        'qteDetail' => $produit->qteDetail - $qte,
                        'montant' => ($produit->qteProduit - $vendus) * $produit->prixProduit,
                    ]);
                } else {
                    notify()->error('Quantité insuffisante en stock.');
                    return redirect()->route('facture.liste');
                }
            } else {
                // Mise à jour du stock produit sans détail
                $produit->update([
                    'qteProduit' => $produit->qteProduit - $qte,
                    'qteDetail' => $produit->nombre * ($produit->qteProduit - $qte),
                    'montant' => ($produit->qteProduit - $qte) * $produit->prixProduit,
                ]);
            }

            $totalMontants = Facture::where('etat', 1)->sum('montant');

            Facture::create([
                'nom' => $request->nom ? $produit->nom : $produit->nomDetail,
                'quantite' => $validatedData['quantite'],
                'client_id' => $client?->id, // Utiliser le client existant ou null
                'prix' => $produit->$prixField,
                'montant' => $montant,
                'etat' => 1,
                'nomClient' => $client?->nom, // Utiliser le nom du client existant ou null
                'total' => $totalMontants + $montant,
                'produit_id' => $produitId,
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
        $factue = Facture::findOrFail($id);

        // Vérifiez si seulement le prix est mis à jour
        if ($request->has('prix')) {
            $request->validate([
                'prix' => 'required|numeric|min:0',
            ]);

            $factue->prix = $request->input('prix');
            $factue->montant = $factue->prix * $factue->quantite;
            $factue->save();

            // Recalculer le total des montants pour toutes les factures actives
            $totalMontants = Facture::where('etat', 1)->sum('montant');

            return response()->json([
                'success' => true,
                'message' => 'Prix mis à jour avec succès.',
                'newMontant' => $factue->montant, // Nouveau montant individuel
                'totalMontants' => number_format($totalMontants, 2) // Nouveau total global
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Aucune modification effectuée.']);

        // Validation des données
        $validatedData = $this->factureValidationService->validate($request->all());

        $qteInitiale = $factue->quantite;
        $qteNouvelle = $validatedData['quantite'];

        // Récupération du produit lié
        $produit = Produit::findOrFail($factue->produit_id);

        if ($validatedData['nom'] == $produit->nomDetail){
            $factue->nom = $request->input('nom');
            $factue->quantite = $qteNouvelle;
            $factue->prix = $request->prix; // On récupère le prix actuel du produit
            $factue->montant = $factue->prix * $qteNouvelle;

            $diffQte = $qteNouvelle - $qteInitiale;
            if($diffQte > 0){
                $newQteDetail = $produit->qteDetail - $diffQte;
            }elseif ($diffQte < 0){
                $newQteDetail = $produit->qteDetail + abs($diffQte);
            }elseif ($diffQte == 0){

                notify()->success('Facture mise à jour avec succès.');
                return redirect()->route('facture.liste');
            }
            $factue->save();

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
