<?php
/**
 * Created by PhpStorm.
 * User: erkin
 * Date: 22.11.2018
 * Time: 23:51
 */

namespace ErkinApp\Responses;


abstract class AjaxResponse extends BaseAjaxResponse
{

    static function template($status, $body, $message = '', $count = '')
    {
        return [
            'head' => [
                'status' => $status,
                'message' => $message,
                'count' => $count !== '' ? $count : (is_array($body) ? count($body) : ''),
            ],
            'body' => $body
        ];
    }

}