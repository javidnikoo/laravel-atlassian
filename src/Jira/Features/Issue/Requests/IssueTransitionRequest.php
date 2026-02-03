<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests;

use InvalidArgumentException;

class IssueTransitionRequest
{
    protected ?string $idOrKey = null;

    protected ?string $transitionId = null;

    protected ?string $comment = null;

    public static function make(): static
    {
        return new static;
    }

    public function idOrKey(string $idOrKey): static
    {
        $this->idOrKey = $idOrKey;

        return $this;
    }

    public function transitionId(string $id): static
    {
        $this->transitionId = $id;

        return $this;
    }

    public function comment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIdOrKey(): string
    {
        return $this->idOrKey ?? throw new InvalidArgumentException('ID or key not set.');
    }

    protected function validate(): void
    {
        if ($this->idOrKey === null || trim($this->idOrKey) === '') {
            throw new InvalidArgumentException('Issue ID or key is required for transition.');
        }

        if ($this->transitionId === null || trim($this->transitionId) === '') {
            throw new InvalidArgumentException('Transition ID is required.');
        }
    }

    public function toArray(): array
    {
        $this->validate();

        $payload = [
            'transition' => ['id' => $this->transitionId],
        ];

        if ($this->comment !== null && trim($this->comment) !== '') {
            $payload['update'] = [
                'comment' => [['add' => ['body' => $this->comment]]],
            ];
        }

        return $payload;
    }
}
