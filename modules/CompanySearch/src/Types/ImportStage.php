<?php

namespace Modules\CompanySearch\Types;

enum ImportStage: string
{
    case IMPORTED = 'imported';
    case SUGGESTED = 'suggested';
    case KVK_PROFILED = 'kvk_profiled';
    case LOCATION_DETAILED = 'location_detailed';
    case META_ADDED = 'meta_added';
    case COMPLETED = 'completed';
}

