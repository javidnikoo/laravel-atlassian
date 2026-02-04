<?php

namespace Javidnikoo\LaravelAtlassian\Tests\Feature\Jira\Issue;

use Illuminate\Support\Facades\Http;
use Javidnikoo\LaravelAtlassian\Jira\Facades\Jira;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests\IssueCreateRequest;
use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;
use Javidnikoo\LaravelAtlassian\Tests\TestCase;

class IssueCreateTest extends TestCase
{
    public function test_it_creates_issue_successfully_with_minimal_fields(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([
                'id' => '10001',
                'key' => 'PROJ-123',
                'self' => 'https://test.atlassian.net/rest/api/3/issue/10001',
                'fields' => [
                    'summary' => 'Minimal Test',
                    'issuetype' => ['name' => 'Task'],
                ],
            ], 201),
        ]);

        $issue = Jira::issues()->create(
            IssueCreateRequest::make()
                ->projectKey('PROJ')
                ->summary('Minimal Test')
                ->issueType('Task')
        );

        $this->assertEquals('PROJ-123', $issue->key);
        $this->assertEquals('Minimal Test', $issue->fields['summary']);
        $this->assertEquals('Task', $issue->fields['issuetype']['name']);
        Http::assertSentCount(1);
    }

    public function test_it_creates_issue_with_full_fields(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([
                'id' => '10002',
                'key' => 'PROJ-124',
                'fields' => [
                    'summary' => 'Full Test Ticket',
                    'description' => ['type' => 'doc', 'version' => 1, 'content' => []],
                    'issuetype' => ['name' => 'Story'],
                    'labels' => ['urgent', 'frontend'],
                    'priority' => ['name' => 'High'],
                ],
            ], 201),
        ]);

        $issue = Jira::issues()->create(
            IssueCreateRequest::make()
                ->projectKey('PROJ')
                ->summary('Full Test Ticket')
                ->issueType('Story')
                ->label('urgent')
                ->label('frontend')
                ->description('This is a detailed description')
        );

        $this->assertEquals('PROJ-124', $issue->key);
        $this->assertEquals('Full Test Ticket', $issue->fields['summary']);
        $this->assertContains('urgent', $issue->fields['labels']);
        $this->assertContains('frontend', $issue->fields['labels']);
        Http::assertSentCount(1);
    }

    public function test_it_throws_validation_exception_when_required_fields_are_missing(): void
    {
        $this->expectException(JiraException::class);
        $this->expectExceptionMessageMatches('/(project key|summary|issue type) is required/i');

        Jira::issues()->create(
            IssueCreateRequest::make()
        );
    }

    public function test_it_throws_jira_exception_on_400_bad_request(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([
                'errorMessages' => ['Project is invalid'],
                'errors' => ['project' => 'The project key does not exist'],
            ], 400),
        ]);

        $this->expectException(JiraException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Project is invalid');

        Jira::issues()->create(
            IssueCreateRequest::make()
                ->projectKey('INVALID')
                ->summary('Test')
                ->issueType('Task')
        );
    }

    public function test_it_throws_jira_exception_on_401_unauthorized(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([], 401),
        ]);

        $this->expectException(JiraException::class);
        $this->expectExceptionCode(401);

        Jira::issues()->create(
            IssueCreateRequest::make()
                ->projectKey('PROJ')
                ->summary('Test')
                ->issueType('Task')
        );
    }

    public function test_it_throws_on_500_server_error_after_retries(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([], 500),
        ]);

        $this->expectException(JiraException::class);
        $this->expectExceptionCode(500);

        Jira::issues()->create(
            IssueCreateRequest::make()
                ->projectKey('PROJ')
                ->summary('Server error test')
                ->issueType('Task')
        );
    }

    public function test_it_throws_400_when_description_is_plain_string_not_adf()
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([
                'errorMessages' => ['description: Operation value must be an Atlassian Document'],
            ], 400),
        ]);

        $this->expectException(JiraException::class);
        $this->expectExceptionCode(400);

        Jira::issues()->create(
            IssueCreateRequest::make()
                ->projectKey('PROJ')
                ->summary('Test')
                ->issueType('Task')
                // Plain string instead of ADF → should fail
                ->description('Plain text not allowed')
        );
    }

    public function test_it_creates_issue_with_valid_adf_description()
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([
                'id' => '10004',
                'key' => 'PROJ-126',
                'fields' => [
                    'summary' => 'ADF Test',
                    'description' => ['type' => 'doc', 'version' => 1, 'content' => []],
                ],
            ], 201),
        ]);

        $issue = Jira::issues()->create(
            IssueCreateRequest::make()
                ->projectKey('PROJ')
                ->summary('ADF Test')
                ->issueType('Task')
                ->description('This should be wrapped in ADF by the request object')
        );

        $this->assertEquals('PROJ-126', $issue->key);
        Http::assertSent(function ($request) {
            return isset($request->data()['fields']['description']['type'])
                && $request->data()['fields']['description']['type'] === 'doc';
        });
    }
}