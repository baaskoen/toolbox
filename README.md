## Toolbox

This Laravel application is meant to serve as an API offering functionality 
that could be reusable in different projects.

For example, converting documents (e.g. `.docx` to `pdf`) requires external software 
like LibreOffice, which is software which you don't want to install on every server.

## Using this API

Every endpoint requires you to supply an `api_key`. This key can be either be supplied in
the request body, or in the query params. You can get this key by contacting me.

## Converting documents
You can convert documents by doing a POST request to the following route and parameters:

Endpoint: `/api/libre/convert-file`

Arguments:
- `api_key`: API key
- `to`: The new format, for example: pdf,html,txt,webp
- `document`: The contents of your file to convert


```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'where-the-api-is-hosted', 
    // 'verify' => false
]);

$response = $client->post('/api/libre/convert-file', [
    'headers' => [
        'Accept' => 'application/json'
    ],
    'multipart' => [
        [
            'name' => 'api_key',
            'contents' => config('auth.api_key')
        ],
        [
            'name' => 'to',
            'contents' => 'html'
        ],
        [
            'name' => 'document',
            'contents' => fopen(storage_path('test_doc.docx'), 'r'),
        ]
    ]
]);

$saveLocation = storage_path('converted');
file_put_contents($saveLocation, $response->getBody()->getContents());
```

## Searching companies

### Suggestions
You can search Dutch companies by doing a GET request to the following route:

Endpoint: `/api/companies`

Arguments:
- `api_key`: API key
- `query`: Keyword, for example "Qlic Groningen"

### Details
You can retrieve details for a company by doing a GET request:

Endpoint: `/api/companies/{slug}`

Where `slug` contains either a KVK number, Company ID or company slug.

Arguments:
- `api_key`: API key

## Installing toolbox on your own

This application can be installed like any other Laravel installation.
Make sure you supply the following `.env` variables:

```dotenv
API_KEY=T00LB0X1!
```

You are required to install LibreOffice on your system.
