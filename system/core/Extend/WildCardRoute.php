<?php

namespace ErkinApp\Extend;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Annotation class for @WildCardRoute().
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class WildCardRoute extends Route
{
    public function __construct(array $data)
    {
        if (isset($data['value']))
            $data['value'] = str_replace('{*}', '{wildcard}', $data['value']);
        if (isset($data['value']))
            $data['path'] = str_replace('{*}', '{wildcard}', $data['path']);
        $data['requirements']['wildcard'] = ".*";
        parent::__construct($data);
    }

}