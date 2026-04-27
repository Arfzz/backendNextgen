<?php

namespace App\Enums;

enum ClassMemberStatus: string
{
    case Ongoing   = 'ongoing';
    case Graduated = 'graduated';
    case Dropped   = 'dropped';
}
