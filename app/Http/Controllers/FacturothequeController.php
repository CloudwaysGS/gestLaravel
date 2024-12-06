<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Dette;
use App\Models\Facture;
use App\Models\Facturotheque;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturothequeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Récupère toutes les factures
        $mesfactures = Facturotheque::searchByName($request->search ?? '');
        // Passe les données à la vue
        return view('facturotheque.liste', compact('mesfactures'));
    }

    public function searchAjax(Request $request)
    {
        $query = $request->query('query', '');
        $page = $request->query('page', 1);
        $size = $request->query('size', 5);

        $facturesQuery = Facturotheque::query();

        if ($query) {
            $facturesQuery->where('nomCient', 'like', '%' . $query . '%');
        }

        $total = $facturesQuery->count();
        $items = $facturesQuery->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $size)
            ->take($size)
            ->get();

        return response()->json([
            'items' => $items,
            'total' => $total,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Récupérer toutes les factures en cours
        $factures = Facture::where('etat', 1)->with('client')->get();
        if ($factures->isEmpty()) {
            notify()->info('Aucune facture à traiter.');
            return redirect()->route('facture.liste');
        }

        $count = $factures->count();
        $totalMontants = $factures->sum('montant');

        // Récupérer les informations du premier client pour l'affichage global
        $firstClient = $factures->first()->client;

        $clientNom = $firstClient ? $firstClient->nom : 'Inconnu';
        $adresse = $firstClient ? $firstClient->adresse : 'Non spécifiée';
        $telephone = $firstClient ? $firstClient->telephone : 'Non spécifié';

        // Générer une référence de facture unique
        $nextId = Facture::max('id') + 1;
        $reference = 'FACT-' . now()->format('Ymd') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            // Créer une seule entrée dans Facturotheque
            $facturotheque = Facturotheque::create([
                'nbreLigne' => $count,
                'nomCient' => $clientNom,
                'total' => $totalMontants,
                'reste' => $totalMontants,
                'adresse' => $adresse,
                'telephone' => $telephone,
                'numFacture' => $reference,
                'etat' => 'en cours',
            ]);

            // Assurez-vous de récupérer l'ID de la nouvelle entrée
            $facturothequeId = $facturotheque->id;

            // Ajouter l'ID à chaque facture et mettre à jour leur état
            foreach ($factures as $facture) {
                $facture->update([
                    'etat' => 0,
                    'facturotheque_id' => $facturothequeId,
                ]);
            }

            DB::commit();

            notify()->success('Succès : Factures traitées.');
            return redirect()->route('facturotheque.index');
        } catch (\Exception $e) {
            DB::rollBack();
            notify()->error('Une erreur est survenue : ' . $e->getMessage());
            return back()->withErrors('Une erreur est survenue : ' . $e->getMessage());
        }
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

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
        // Vérifier si des factures sont actuellement verrouillées (état à 1)
        $facturesActives = Facture::where('etat', 1)->exists();

        if ($facturesActives) {
            notify()->info('Une ou plusieurs factures sont actuellement verrouillées.');
            return redirect()->route('facturotheque.index');
        }

        // Charger la Facturothèque uniquement si aucune facture active n'existe
        $facturotheque = Facturotheque::find($id);

        // Vérifier si la Facturothèque existe
        if (!$facturotheque) {
            notify()->error('La Facturothèque demandée est introuvable.');
            return redirect()->route('facturotheque.index');
        }

        // Mettre à jour l'état des factures associées
        $facturotheque->factures()->update(['etat' => 1]);

        // Supprimer la Facturothèque
        $facturotheque->delete();

        // Notification
        notify()->success('Les factures associées ont été mises à jour et la facturothèque a été supprimée avec succès.');

        return redirect()->route('facture.liste');
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
        $facture = Facturotheque::find($id);

        if (!$facture) {
            notify()->error('Facture introuvable.');
            return redirect()->route('facturotheque.index');
        }

        // Supprimer les factures associées
        $facture->factures()->delete();

        // Supprimer la facturothèque
        $facture->delete();

        notify()->success('Facture et données associées supprimées avec succès.');
        return redirect()->route('facturotheque.index');
    }

    public function exportPdf(string $id)
    {
        $facturotheque = Facturotheque::findOrFail($id);
        $reference = $facturotheque->numFacture;
        $date = now();
        $factures = $facturotheque->factures()->get();
        $vendeur = auth()->user();

        // Informations du client
        $client = $factures->first(); // Assurez-vous que la relation client existe
        // Calculer le total des montants
        $totalMontants = $factures->sum('montant');

        // Générer la vue
        $pdf = Pdf::loadView('facture.pdf', [
            'facture' => $factures,
            'totalMontants' => $totalMontants,
            'client' => $client,
            'vendeur' => $vendeur,
            'reference' =>$reference,
            'date' => $date,
        ]);

        // Télécharger le PDF
        return $pdf->stream('facture.pdf');
    }

    public function avance(string $id)
    {
        $facture = Facturotheque::find($id);
        return view('facturotheque.avance', compact('facture'));
    }

    public function showAcompte(Request $request, $id)
    {
        $facture = Facturotheque::findOrFail($id);

        $request->validate([
            'avance' => 'required|numeric|min:0',
        ]);

        $acompte = $request->avance;
        $resteAPayer = $facture->reste - $acompte;

        $facture->update([
            'avance' => $acompte,
            'reste' => $resteAPayer,
            'etat' => $resteAPayer <= 0 ? 'payée' : 'avance',
        ]);

        if ($resteAPayer <= 0) {
            Dette::where('nom', $facture->nomCient)
                ->where('montant', $facture->total)
                ->update([
                    'etat' => 'payée',
                    'reste' => 0,
                    'depot' => abs($resteAPayer),
                ]);

            notify()->success('Facture payée avec succès.');
            return redirect()->route('facturotheque.index');
        }

        $client = Client::where('nom', $facture->nomCient)->first();
        if (!$client) {
            return redirect()->back()->with('error', 'Client introuvable.');
        }

        // Création de la dette si elle n'existe pas déjà
        Dette::updateOrCreate(
            [
                'client_id' => $client->id,
                'nom' => $facture->nomCient,
                'montant' => $facture->total,
            ],
            [
                'reste' => $resteAPayer,
                'commentaire' => 'Avance de la facture',
                'etat' => 'impayée',
            ]
        );

        notify()->success('Avance enregistrée avec succès.');
        return redirect()->route('facturotheque.index');
    }





}
