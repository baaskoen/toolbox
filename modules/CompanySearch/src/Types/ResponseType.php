<?php

namespace Modules\CompanySearch\Types;

enum ResponseType: string
{
    case JSON = 'json';
    case HTML = 'html';
}
