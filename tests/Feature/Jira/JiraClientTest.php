<?php

namespace Javidnikoo\LaravelAtlassian\Tests\Feature\Jira;

use Illuminate\Support\Facades\Http;
use Javidnikoo\LaravelAtlassian\Jira\Clients\JiraClient;
use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;
use Javidnikoo\LaravelAtlassian\Tests\TestCase;

class JiraClientTest extends TestCase
{
    private function makeClient(array $overrides = []): JiraClient
    {
        $config = array_merge(config('atlassian.jira'), $overrides);

        return new JiraClient($config);
    }

    public function test_it_requires_base_url(): void
    {
        $this->expectException(JiraException::class);
        $this->expectExceptionMessage('Missing config: atlassian.jira.base_url');

        $this->makeClient(['base_url' => null]);
    }

    public function test_it_requires_email(): void
    {
        $this->expectException(JiraException::class);
        $this->expectExceptionMessage('Missing config: atlassian.jira.email');

        $this->makeClient(['email' => null]);
    }

    public function test_it_requires_api_token(): void
    {
        $this->expectException(JiraException::class);
        $this->expectExceptionMessage('Missing config: atlassian.jira.api_token');

        $this->makeClient(['api_token' => null]);
    }

    public function test_it_sends_get_requests_to_base_url_plus_endpoint(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/myself' => Http::response(['accountId' => 'abc'], 200),
        ]);

        $client = $this->makeClient();
        $me = $client->get('rest/api/3/myself');

        $this->assertSame('abc', $me['accountId']);

        Http::assertSent(function ($request) {
            return $request->method() === 'GET'
                && $request->url() === 'https://test.atlassian.net/rest/api/3/myself';
        });
    }

    public function test_it_throws_jira_exception_on_400_and_sets_context()
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue' => Http::response([
                'errorMessages' => ['Project is invalid'],
                'errors' => ['project' => 'The project key does not exist'],
            ], 400),
        ]);

        $client = $this->makeClient();

        try {

            $client->post('rest/api/3/issue', ['fields' => []]);
            $this->fail('Expected JiraException to be thrown.');
        } catch (JiraException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame('Project is invalid', $e->getMessage());

            $this->assertIsArray($e->context);
            $this->assertSame('jira', $e->context['service']);
            $this->assertSame(400, $e->context['status']);
            $this->assertStringContainsString('/rest/api/3/issue', $e->context['url']);
            $this->assertArrayHasKey('body', $e->context);
            $this->assertArrayHasKey('errorMessages', $e->context['body']);
        }
    }

    public function test_it_returns_empty_array_when_put_response_has_no_json(): void
    {
        Http::fake([
            'https://test.atlassian.net/rest/api/3/issue/PROJ-1' => Http::response(null, 204),
        ]);

        $client = new JiraClient(config('atlassian.jira'));

        $result = $client->put('rest/api/3/issue/PROJ-1', ['fields' => ['summary' => 'Updated']]);

        $this->assertSame([], $result);

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && $request->url() === 'https://test.atlassian.net/rest/api/3/issue/PROJ-1';
        });
    }
}
