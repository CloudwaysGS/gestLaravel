<?php

use App\Http\Controllers\EntreeController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\SortieController;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    notify()->success('Bienvenue dans l\'accueille.');

    return view('accueille');
});

Route::get('/produits', [ProduitController::class, 'index'])->name('produit.liste');
Route::get('/ajout', function() {
    return view('produit.ajout');
});

Route::get('/entree/ajout', function() {
    $produits = Produit::all(); // Charger les produits
    return view('entree.ajout', compact('produits'));
});

Route::get('/sortie/ajout', function() {
    $produits = Produit::all(); // Charger les produits
    return view('sortie.ajout', compact('produits'));
});

Route::post('/produit/ajout', [ProduitController::class, 'store']);
Route::post('/entree/ajout', [EntreeController::class, 'store']);
Route::post('/ajout', [SortieController::class, 'store']);
Route::get('/produit/delete/{id}', [ProduitController::class, 'destroy']);
Route::get('/produit/restore/{id}', [ProduitController::class, 'restore']);
Route::get('/produit/{id}/modifier', [ProduitController::class, 'edit'])->name('produit.modifier'); // To display the edit form
Route::get('/entree/{id}/modifier', [EntreeController::class, 'edit'])->name('entree.modifier'); // Correction ici
Route::get('/sortie/{id}/modifier', [SortieController::class, 'edit'])->name('sortie.modifier'); // Correction ici
Route::put('/produit/{id}', [ProduitController::class, 'update'])->name('produit.update'); // To update the product
Route::put('/entree/{id}', [EntreeController::class, 'update'])->name('entree.update'); // To update the entre
Route::put('/sortie/{id}', [EntreeController::class, 'update'])->name('sortie.update'); // To update the entre
Route::get('/sortie', [SortieController::class, 'index'])->name('sortie.liste');
Route::get('/entree', [EntreeController::class, 'index'])->name('entree.liste');
Route::get('/entree/delete/{id}', [EntreeController::class, 'destroy']);
Route::get('/sortie/delete/{id}', [SortieController::class, 'destroy']);




