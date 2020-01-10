<?php
/**
 * Created by PhpStorm.
 * User: erkin
 * Date: 22.11.2018
 * Time: 23:51
 */

namespace ErkinApp\Response;


abstract class BaseAjaxResponse implements IAjaxResponse
{
    static function Auto($body, $message = '', $count = '')
    {
        if ($body) return static::Success($body, $message, $count);
        else return static::Error('No Data', $body, $count);
    }

    static function Success($body, $message = '', $count = '')
    {
        return new PrettyJsonResponse(static::template('success', $body, $message, $count));
    }

    static function Error($message = '', $body = '', $count = '')
    {
        return new PrettyJsonResponse(static::template('failure', $body, $message, $count));
    }

}