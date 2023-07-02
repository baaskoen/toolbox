<?php

namespace Modules\CompanySearch\Types;

enum CompanyType: string
{
    case RECHTSPERSOON = 'rechtspersoon';
    case HOOFDVESTIGING = 'hoofdvestiging';
    case NEVENVESTIGING = 'nevenvestiging';

    case UNKNOWN = 'unknown';
}
