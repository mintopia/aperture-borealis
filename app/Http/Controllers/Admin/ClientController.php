<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->get()->map(fn ($client) => [
            'id' => $client->id,
            'name' => $client->name,
            'client_id' => $client->client_id,
            'enabled' => $client->enabled,
            'created_at' => $client->created_at->diffForHumans(),
        ]);

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Clients/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'enabled' => 'boolean',
        ]);

        Client::create([
            'name' => $request->name,
            'enabled' => $request->boolean('enabled', true),
        ]);

        return redirect('/admin/clients')->with('success', 'Client created.');
    }

    public function show(Client $client)
    {
        return Inertia::render('Admin/Clients/Show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'client_id' => $client->client_id,
                'client_secret' => $client->client_secret,
                'enabled' => $client->enabled,
                'created_at' => $client->created_at->toIso8601String(),
                'updated_at' => $client->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'enabled' => 'sometimes|boolean',
        ]);

        $client->update($request->only(['name', 'enabled']));

        return back()->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect('/admin/clients')->with('success', 'Client deleted.');
    }
}
