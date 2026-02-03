<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests;

use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;

class IssueCreateRequest
{
    protected array $fields = [];

    public static function make(): static
    {
        return new static;
    }

    public function projectKey(string $key): self
    {
        $this->fields['project'] = ['key' => $key];

        return $this;
    }

    public function summary(string $summary): self
    {
        $this->fields['summary'] = $summary;

        return $this;
    }

    public function description(string $description): self
    {
        $this->fields['description'] = $description;

        return $this;
    }

    public function issueType(string $type): self
    {
        $this->fields['issuetype'] = ['name' => $type];

        return $this;
    }

    public function label(string $label): self
    {
        $this->fields['labels'][] = $label;

        return $this;
    }

    public function toArray(): array
    {
        $this->validate();

        return ['fields' => $this->fields];
    }

    protected function validate(): void
    {
        if (empty($this->fields['project']) || empty($this->fields['summary']) || empty($this->fields['issuetype'])) {
            throw new JiraException('Required fields missing.');
        }
    }
}
