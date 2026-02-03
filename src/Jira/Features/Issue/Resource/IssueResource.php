<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Resource;

use Javidnikoo\LaravelAtlassian\Jira\Clients\JiraClient;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Models\Issue;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests\IssueCreateRequest;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests\IssueTransitionRequest;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests\IssueUpdateRequest;

class IssueResource
{
    public function __construct(protected JiraClient $client) {}

    public function create(IssueCreateRequest $request): Issue
    {
        $endpoint = 'rest/api/3/issue';
        $response = $this->client->post($endpoint, $request->toArray());

        return Issue::fromArray($response);
    }

    public function update(IssueUpdateRequest $request): void
    {
        $endpoint = 'rest/api/3/issue/'.$request->getIdOrKey();
        $this->client->put($endpoint, $request->toArray());
    }

    public function get(string $idOrKey): Issue
    {
        $endpoint = 'rest/api/3/issue/'.$idOrKey;
        $response = $this->client->get($endpoint);

        return Issue::fromArray($response);
    }

    public function transition(IssueTransitionRequest $request): void
    {
        $endpoint = 'rest/api/3/issue/'.$request->getIdOrKey().'/transitions';
        $this->client->post($endpoint, $request->toArray());
    }
}
