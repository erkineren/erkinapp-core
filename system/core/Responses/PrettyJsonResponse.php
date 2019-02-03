<?php
/**
 * Created by PhpStorm.
 * User: erkin
 * Date: 23.11.2018
 * Time: 11:46
 */

namespace ErkinApp\Responses;


use Symfony\Component\HttpFoundation\JsonResponse;

class PrettyJsonResponse extends JsonResponse
{
    protected $encodingOptions = parent::DEFAULT_ENCODING_OPTIONS | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
}