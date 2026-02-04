<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Enums;

enum IssueType: string
{
    case TASK = 'Task';
    case STORY = 'Story';
    case BUG = 'Bug';
    case EPIC = 'Epic';
    case SUB_TASK = 'Sub-task';
}
