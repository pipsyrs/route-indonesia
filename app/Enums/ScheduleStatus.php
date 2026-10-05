<?php

namespace App\Enums;

enum ScheduleStatus: string
{
    case Scheduled = 'scheduled';
    case Departed = 'departed';
    case Cancelled = 'cancelled';
}
