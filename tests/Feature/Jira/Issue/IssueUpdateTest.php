<?php

namespace Javidnikoo\LaravelAtlassian\Tests\Feature\Jira\Issue;

use Illuminate\Support\Facades\Http;
use Javidnikoo\LaravelAtlassian\Jira\Facades\Jira;
use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests\IssueUpdateRequest;
use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;
use Javidnikoo\LaravelAtlassian\Tests\TestCase;

class IssueUpdateTest extends TestCase
{
    public function test_it_updates_existing_issue_successfully(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-456' => Http::response([], 204),
        ]);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-456')
                ->summary('Updated title')
                ->description('New description')
        );

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/rest/api/3/issue/PROJ-456')
                && ($request->data()['fields']['summary'] ?? '') === 'Updated title'
                && isset($request->data()['fields']['description']);
        });
    }

    public function test_it_updates_issue_with_partial_fields(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-456' => Http::response([], 204),
        ]);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-456')
                ->summary('Only summary updated')
        // No description, labels, etc. → partial update
        );

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/rest/api/3/issue/PROJ-456')
                && ($data['fields']['summary'] ?? '') === 'Only summary updated'
                && !isset($data['fields']['description']);
        });
    }

    public function test_it_updates_issue_with_adf_description(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-456' => Http::response([], 204),
        ]);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-456')
                ->summary('ADF Update')
                ->description('This description should be ADF wrapped')
        );

        Http::assertSent(function ($request) {
            $data = $request->data();
            return isset($data['fields']['description']['type'])
                && $data['fields']['description']['type'] === 'doc'
                && $data['fields']['description']['version'] === 1;
        });
    }

    public function test_it_updates_issue_with_labels(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-456' => Http::response([], 204),
        ]);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-456')
                ->label('urgent')
                ->label('updated')
        );

        Http::assertSent(function ($request) {
            $labels = $request->data()['fields']['labels'] ?? [];
            return $request->method() === 'PUT'
                && in_array('urgent', $labels)
                && in_array('updated', $labels);
        });
    }

    public function test_it_throws_jira_exception_on_400_invalid_field(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-456' => Http::response([
                'errorMessages' => ['Invalid field value'],
                'errors' => ['summary' => 'Summary is too long'],
            ], 400),
        ]);

        $this->expectException(JiraException::class);
        $this->expectExceptionCode(400);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-456')
                ->summary(str_repeat('a', 10000)) // simulate invalid length
        );
    }

    public function test_it_throws_jira_exception_on_401_unauthorized(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-456' => Http::response([], 401),
        ]);

        $this->expectException(JiraException::class);
        $this->expectExceptionCode(401);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-456')
                ->summary('Test')
        );
    }

    public function test_it_throws_jira_exception_on_403_forbidden(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-456' => Http::response([], 403),
        ]);

        $this->expectException(JiraException::class);
        $this->expectExceptionCode(403);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-456')
                ->summary('No permission')
        );
    }


    public function test_it_throws_jira_exception_on_404_issue_not_found(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-999' => Http::response([
                'errorMessages' => ['Issue does not exist or you do not have permission to see it.'],
            ], 404),
        ]);

        $this->expectException(JiraException::class);
        $this->expectExceptionCode(404);

        Jira::issues()->update(
            IssueUpdateRequest::make()
                ->idOrKey('PROJ-999')
                ->summary('Not found')
        );
    }
}