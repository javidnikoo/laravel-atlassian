<?php

namespace Javidnikoo\LaravelAtlassian\Tests\Feature\Jira;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;
use Javidnikoo\LaravelAtlassian\Jira\Facades\Jira;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests\IssueCreateRequest;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests\IssueTransitionRequest;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests\IssueUpdateRequest;
use Javidnikoo\LaravelAtlassian\Tests\TestCase;

class IssueResourceTest extends TestCase
{
    public function testItCreatesIssueSuccessfully()
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([
                'id' => '10001',
                'key' => 'PROJ-123',
                'self' => 'https://test.atlassian.net/rest/api/3/issue/10001',
                'fields' => [
                    'summary' => 'Test Ticket',
                    'issuetype' => ['name' => 'Task'],
                    'labels' => ['urgent'],
                ],
            ], 201),
        ]);

        $issue = Jira::issues()->create(
            IssueCreateRequest::make()
                ->projectKey('PROJ')
                ->summary('Test Ticket')
                ->issueType('Task')
                ->label('urgent')
        );

        $this->assertEquals('PROJ-123', $issue->key);
        $this->assertEquals('Test Ticket', $issue->fields['summary']);
        Http::assertSentCount(1);
    }

    public function testUpdatesExistingIssue()
    {
        Http::fake([
            '*/rest/api/3/issue/PROJ-456' => Http::response([], 204),
        ]);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-456')
                ->summary('Updated title')
                ->description('New description')
        );

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && $request->url() === 'https://test.atlassian.net/rest/api/3/issue/PROJ-456'
                && $request->data()['fields']['summary'] === 'Updated title'
                && $request->data()['fields']['description'] === 'New description';
        });
    }
}
