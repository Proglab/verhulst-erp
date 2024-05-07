<?php
namespace App\Enum;

enum ProjectType: string
{
    case events = 'Event à la carte';
    case package = 'Package VIP';
    case sponsoring = 'Sponsoring';
    case divers = 'Divers';
}