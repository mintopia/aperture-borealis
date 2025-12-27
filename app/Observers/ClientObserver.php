<?php

namespace App\Observers;

use App\Models\Client;
use Ramsey\Uuid\Uuid;

class ClientObserver
{
    public function saving(Client $client): void
    {
        if (!$client->client_id) {
            $client->client_id = Uuid::uuid4()->toString();
        }
        if (!$client->client_secret) {
            $client->client_secret = Uuid::uuid4()->toString();
        }
    }
}
