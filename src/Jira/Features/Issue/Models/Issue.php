<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Models;

class Issue
{
    public readonly string $id;

    public readonly string $key;

    public readonly array $fields;

    public readonly array $links;

    public static function fromArray(array $data): self
    {
        $instance = new self;
        $instance->id = $data['id'] ?? '';
        $instance->key = $data['key'] ?? '';
        $instance->fields = $data['fields'] ?? [];
        $instance->links = $data['fields']['issuelinks'] ?? [];

        return $instance;
    }
}
