<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        $params['page'] = $request->input('page', 1);
        $params['perPage'] = $request->input('perPage', 20);

        $clients = $query->paginate($params['perPage'])->appends($params);

        return view('clients.index', [
            'clients' => $clients,
            'params' => $params,
        ]);
    }

    public function show(Client $client)
    {
        return view('clients.show', [
            'client' => $client,
        ]);
    }

    public function create()
    {
        $client = new Client();
        $client->interval = config('borealis.client.defaults.interval');
        $client->expires_in = config('borealis.client.defaults.expires_in');
        return view('clients.create', [
            'client' => $client,
        ]);
    }

    public function store(ClientRequest $request)
    {
        $client = new Client();
        $this->updateClient($request, $client);
        return response()
            ->redirectToRoute('clients.show', $client->id)
            ->with('successMessage', 'The client has been added');
    }

    protected function updateClient(Request $request, Client $client): void
    {
        $client->name = $request->input('name');
        $client->enabled = (bool)$request->input('enabled');
        $client->interval = $request->input('interval');
        $client->expires_in = $request->input('expires_in');
        $client->save();
    }
}
