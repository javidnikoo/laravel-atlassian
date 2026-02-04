<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Requests;

use Javidnikoo\LaravelAtlassian\Jira\Enums\IssueType;
use Javidnikoo\LaravelAtlassian\Jira\Exceptions\JiraException;

class IssueCreateRequest
{
    protected array $fields = [];

    protected array $descriptionContent = [];

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

    /**
     * Add a simple paragraph of plain text
     */
    public function description(string $text): self
    {
        $this->descriptionContent[] = [
            'type' => 'paragraph',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $text,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add multiple paragraphs at once
     */
    public function descriptionWithParagraphs(array $paragraphs): self
    {
        foreach ($paragraphs as $text) {
            $this->descriptionContent[] = [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => $text],
                ],
            ];
        }

        return $this;
    }

    /**
     * Add a horizontal divider (visual line / <hr>)
     */
    public function descriptionDivider(): self
    {
        $this->descriptionContent[] = [
            'type' => 'rule',
        ];

        return $this;
    }

    /**
     * Add a quote block
     */
    public function descriptionQuote(string $text): self
    {
        $this->descriptionContent[] = [
            'type' => 'blockquote',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $text]],
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add an info panel (blue box)
     */
    public function descriptionInfoPanel(string $text): self
    {
        $this->descriptionContent[] = [
            'type' => 'panel',
            'attrs' => ['panelType' => 'info'],
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $text]],
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add a warning panel (yellow box)
     */
    public function descriptionWarningPanel(string $text): self
    {
        $this->descriptionContent[] = [
            'type' => 'panel',
            'attrs' => ['panelType' => 'warning'],
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $text]],
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add a success panel (green box) - bonus
     */
    public function descriptionSuccessPanel(string $text): self
    {
        $this->descriptionContent[] = [
            'type' => 'panel',
            'attrs' => ['panelType' => 'success'],
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $text]],
                ],
            ],
        ];

        return $this;
    }

    public function issueType(IssueType|string $type): self
    {
        $this->fields['issuetype'] = ['name' => $type instanceof IssueType ? $type->value : $type];

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

        if (! empty($this->descriptionContent)) {
            $this->fields['description'] = [
                'type' => 'doc',
                'version' => 1,
                'content' => $this->descriptionContent,
            ];
        }

        return ['fields' => $this->fields];
    }

    protected function validate(): void
    {
        if (empty($this->fields['project']['key'])) {
            throw new JiraException('Project key is required.');
        }

        if (empty($this->fields['summary'])) {
            throw new JiraException('Summary is required.');
        }

        if (empty($this->fields['issuetype']['name'])) {
            throw new JiraException('Issue type is required.');
        }
    }
}
