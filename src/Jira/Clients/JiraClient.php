<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Javidnikoo\LaravelAtlassian\Atlassian\Http\AtlassianHttpFactory;
use Javidnikoo\LaravelAtlassian\Jira\Contracts\JiraClientInterface;
use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Resource\IssueResource;
use Symfony\Component\HttpFoundation\Request;

class JiraClient implements JiraClientInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        if (empty($this->config['base_url'])) {
            throw new JiraException(
                'Missing config: atlassian.jira.base_url. '
                .'Did you forget to set ATLASSIAN_JIRA_BASE_URL in your .env?'
            );
        }

        if (empty($this->config['email'])) {
            throw new JiraException(
                'Missing config: atlassian.jira.email. '
                .'Did you forget to set ATLASSIAN_EMAIL in your .env?'
            );
        }

        if (empty($this->config['api_token'])) {
            throw new JiraException(
                'Missing config: atlassian.jira.api_token. '
                .'Did you forget to set ATLASSIAN_API_TOKEN in your .env?'
            );
        }
    }

    protected function http(): PendingRequest
    {
        return AtlassianHttpFactory::make($this->config);
    }

    private function sendRequest(string $method, string $endpoint, array $data = [], array $query = []): array
    {
        try {
            $request = $this->http();

            if ($method === Request::METHOD_GET) {
                $response = $request->get($endpoint, $query);
            } elseif ($method === Request::METHOD_POST) {
                $response = $request->post($endpoint, $data);
            } elseif ($method === Request::METHOD_PUT) {
                $response = $request->put($endpoint, $data);
            } else {
                throw new \InvalidArgumentException('Unsupported HTTP method');
            }
        } catch (RequestException $e) {
            $response = $e->response;
        }

        return $this->handleResponse($response, $endpoint, $method);
    }

    public function post(string $endpoint, array $data): array
    {
        return $this->sendRequest(Request::METHOD_POST, $endpoint, $data);
    }

    public function put(string $endpoint, array $data): array
    {
        return $this->sendRequest(Request::METHOD_PUT, $endpoint, $data);
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->sendRequest(Request::METHOD_GET, $endpoint, [], $query);
    }

    private function handleResponse(Response $response, string $endpoint, string $method): array
    {
        if ($response->failed()) {
            $status = $response->status();

            $message = $response->json('errorMessages')[0]
                ?? ($response->json('errors') ? implode(', ', $response->json('errors')) : null)
                ?? $response->json('message')
                ?? $response->body()
                ?? 'Unknown Jira API error';

            throw new JiraException(
                message: $message,
                code: $status,
                context: [
                    'service' => 'jira',
                    'method' => $method,
                    'url' => $response->effectiveUri()?->__toString() ?? $endpoint,
                    'status' => $status,
                    'body' => $response->json() ?? $response->body(),
                ]
            );
        }

        return $response->json() ?? [];
    }

    public function issues(): IssueResource
    {
        return new IssueResource($this);
    }
}
