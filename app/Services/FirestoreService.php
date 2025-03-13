<?php
namespace App\Services;

use GuzzleHttp\Client;
use Google\Auth\CredentialsLoader;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\HandlerStack;

class FirestoreService
{
    protected $client;
    protected $projectId;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID');
        $serviceAccountKey = json_decode(file_get_contents(storage_path('app/firebase-credentials.json')), true);

        $credentials = CredentialsLoader::makeCredentials([], $serviceAccountKey);
        $authTokenMiddleware = new AuthTokenMiddleware($credentials);

        $stack = HandlerStack::create();
        $stack->push($authTokenMiddleware);

        $this->client = new Client([
            'handler' => $stack,
            'base_uri' => "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/",
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getDocuments($collection)
    {
        try {
            $response = $this->client->get($collection);
            $data = json_decode($response->getBody(), true);
    
            if (isset($data['documents'])) {
                return $data['documents'];
            }
    
            return [];
        } catch (\Exception $e) {
            \Log::error('Firestore error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            throw $e;
        }
    }
    public function addDocument($path, $data)
{
    // Générer un jeton JWT pour l'authentification
    $serviceAccount = json_decode(file_get_contents(storage_path('app/firebase-credentials.json')), true);
    $jwt = $this->generateJWT($serviceAccount);

    // URL de l'API Firestore
    $projectId = $serviceAccount['project_id'];
    $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/$path";

    // Formater les données pour Firestore
    $firestoreData = [
        'fields' => $this->formatData($data),
    ];

    // Faire la requête HTTP
    $client = new \GuzzleHttp\Client();
    $response = $client->post($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $jwt,
            'Content-Type' => 'application/json',
        ],
        'json' => $firestoreData,
    ]);

    return json_decode($response->getBody(), true);
}

    
protected function formatData($data)
{
    $formatted = [];
    foreach ($data as $key => $value) {
        if (is_int($value)) {
            $formatted[$key] = ['integerValue' => $value];
        } elseif (is_string($value)) {
            $formatted[$key] = ['stringValue' => $value];
        } elseif (is_bool($value)) {
            $formatted[$key] = ['booleanValue' => $value];
        } elseif (is_float($value)) {
            $formatted[$key] = ['doubleValue' => $value];
        } elseif (is_array($value)) {
            $formatted[$key] = ['arrayValue' => ['values' => $this->formatData($value)]];
        } elseif (is_null($value)) {
            $formatted[$key] = ['nullValue' => null];
        } else {
            throw new \Exception("Type de données non pris en charge pour Firestore : " . gettype($value));
        }
    }
    return $formatted;
}

    
protected function generateJWT($serviceAccount)
{
    $privateKey = $serviceAccount['private_key'];
    $clientEmail = $serviceAccount['client_email'];

    $now = time();
    $exp = $now + 3600; // Token expires in 1 hour

    $payload = [
        'iss' => $clientEmail,
        'sub' => $clientEmail,
        'aud' => 'https://firestore.googleapis.com/',
        'iat' => $now,
        'exp' => $exp,
    ];

    return \Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');
}
        public function updateDocument($collection, $documentId, $data)
    {
        $response = $this->client->patch("$collection/$documentId", [
            'json' => ['fields' => $this->formatDataForFirestore($data)],
        ]);
        return json_decode($response->getBody(), true);
    }

    public function deleteDocument($collection, $documentId)
    {
        $response = $this->client->delete("$collection/$documentId");
        return $response->getStatusCode() === 204;
    }

    protected function formatDataForFirestore($data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $fields[$key] = ['stringValue' => $value];
            } elseif (is_int($value) || is_float($value)) {
                $fields[$key] = ['integerValue' => $value];
            } elseif (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif (is_array($value)) {
                $fields[$key] = ['arrayValue' => ['values' => $this->formatDataForFirestore($value)]];
            } elseif (is_null($value)) {
                $fields[$key] = ['nullValue' => null];
            } else {
                throw new \InvalidArgumentException("Unsupported data type for key: $key");
            }
        }
        return $fields;
    }
}