<?php

namespace Modules\CompanySearch\Types;

enum ApiType: string
{
    case GOOGLE_GEOCODING = 'google_geocoding';
    case GOOGLE_PLACE_SEARCH = 'google_place_search';
    case GOOGLE_PLACE_DETAILS = 'google_place_details';

    case KVK_SEARCH = 'kvk_search';

    case KVK_DETAIL = 'kvk_detail';
    case KVK_VESTIGING = 'kvk_vestiging';

    case BRANDING = 'branding';
    case WEBSITE_CONTACT = 'website_contact';
}
