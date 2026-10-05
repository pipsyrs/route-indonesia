<?php

namespace App\Enums;

enum SeatStatus: string
{
    case Held = 'held';
    case Booked = 'booked';
}
