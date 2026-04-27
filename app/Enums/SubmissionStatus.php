<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Pending   = 'pending';
    case Submitted = 'submitted';
    case Graded    = 'graded';
}
