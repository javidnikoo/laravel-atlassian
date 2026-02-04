<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests;

use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;

class IssueUpdateRequest extends IssueCreateRequest
{
    protected string $idOrKey;

    public function idOrKey(string $idOrKey): self
    {
        $this->idOrKey = $idOrKey;

        return $this;
    }

    public function getIdOrKey(): string
    {
        return $this->idOrKey;
    }

    protected function validate(): void
    {
        if (empty($this->idOrKey)) {
            throw new JiraException('ID or key is required for update.');
        }
    }

    public function toArray(): array
    {
        $this->validate();

        if (! empty($this->descriptionContent)) {
            $this->fields['description'] = [
                'type' => 'doc',
                'version' => 1,
                'content' => $this->descriptionContent,
            ];
        }

        return ['fields' => $this->fields];
    }
}
