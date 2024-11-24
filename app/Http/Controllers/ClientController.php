<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ClientValidationService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $client = Client::orderBy('created_at', 'desc')->get();
        return view('client.liste', compact('client'));
    }

    protected $clientValidationService;

    public function __construct(ClientValidationService $clientValidationService)
    {
        $this->clientValidationService = $clientValidationService;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $this->clientValidationService->validate($request->all());
        Client::create($validatedData);
        notify()->success('Client créé avec succès.');
        return redirect()->route('client.liste');
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
