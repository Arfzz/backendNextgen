<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Pending   = 'pending';
    case Submitted = 'submitted';
    case Reviewed  = 'reviewed';  // mentor replied — student can revise
    case Graded    = 'graded';    // marked complete — no more revisions
}
