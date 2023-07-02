<?php

namespace Modules\CompanySearch\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CompanySearch\Models\SearchQuery;
use Modules\CompanySearch\Types\ApiType;
use Modules\CompanySearch\Types\ResponseType;
use Modules\CompanySearch\Types\SearchQueryType;

class StoreCompanyKvkSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    private SearchQuery $searchQuery;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SearchQuery $query)
    {
        $this->searchQuery = $query;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $key = config('company.kvk.api_key');

        $client = new Client([
            'base_uri' => 'https://api.kvk.nl',
            'headers' => [
                'apikey' => $key
            ],
            'verify' => false
        ]);

        $success = true;

        try {
            $response = $client->get('/api/v1/zoeken', [
                'query' => [
                    'handelsnaam' => $this->searchQuery->query
                ]
            ]);
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() !== 404) {
                logger()->critical($exception->getMessage());
            }

            $response = $exception->getResponse();
            $success = false;
        }

        $this->searchQuery->apiResponses()
            ->updateOrCreate([
                'api_name' => ApiType::KVK_SEARCH->value,
                'search_query_type' => SearchQueryType::COMPANY->value
            ], [
                'response' => $response->getBody()->getContents(),
                'data_type' => ResponseType::JSON->value,
                'headers' => $response->getHeaders(),
                'was_successful' => $success
            ]);
    }
}
