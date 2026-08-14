<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza;

enum Expiry: string
{
    case ThirtyMinutes = '30m';
    case OneHour = '1h';
    case TwoHours = '2h';
    case SixHours = '6h';
    case OneDay = '1d';
    case ThreeDays = '3d';
    case OneWeek = '1w';
    case Never = 'never';
}
