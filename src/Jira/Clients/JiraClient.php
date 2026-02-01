<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Clients;

use Illuminate\Http\Client\PendingRequest;
use Javidnikoo\LaravelAtlassian\Atlassian\Http\AtlassianHttpFactory;
use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;

class JiraClient
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        if (empty($this->config['base_url'])) {
            throw new JiraException('Missing config: atlassian.jira.base_url');
        }

        if (empty($this->config['email'])) {
            throw new JiraException('Missing config: atlassian.jira.email');
        }

        if (empty($this->config['api_token'])) {
            throw new JiraException('Missing config: atlassian.jira.api_token');
        }
    }

    protected function http(): PendingRequest
    {
        return AtlassianHttpFactory::make($this->config)
            ->throw(function ($response) {
                $message = $response->json('errorMessages')[0]
                    ?? $response->json('message')
                    ?? $response->body()
                    ?? 'Unknown error';

                $status = $response->status();

                throw new JiraException(
                    message: $message,
                    code: $status,
                    context: [
                        'service' => 'jira',
                        'url' => $response->effectiveUri()?->__toString(),
                        'status' => $status,
                        'body' => $response->json() ?? $response->body(),
                    ]
                );
            });
    }

    public function post(string $endpoint, array $data): array
    {
        return $this->http()->post($endpoint, $data)->json() ?? [];
    }

    public function put(string $endpoint, array $data): array
    {
        return $this->http()->put($endpoint, $data)->json() ?? [];
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->http()->get($endpoint, $query)->json() ?? [];
    }
}
