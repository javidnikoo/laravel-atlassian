<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Clients;

use Illuminate\Http\Client\PendingRequest;
use Javidnikoo\LaravelAtlassian\Atlassian\Http\AtlassianHttpFactory;
use Javidnikoo\LaravelAtlassian\Jira\Contracts\JiraClientInterface;
use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Resource\IssueResource;

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
                .'Did you forget to set ATLASSIAN_JIRA_EMAIL in your .env?'
            );
        }

        if (empty($this->config['api_token'])) {
            throw new JiraException('Missing config: atlassian.jira.api_token. '
                .'Did you forget to set ATLASSIAN_API_TOKEN in your .env?'
            );

        }
    }

    protected function http(): PendingRequest
    {
        return AtlassianHttpFactory::make($this->config);
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

    public function issues(): IssueResource
    {
        return new IssueResource($this);
    }
}
