<?php
/**
 * Created by PhpStorm.
 * User: erkin
 * Date: 23.11.2018
 * Time: 00:49
 */

namespace ErkinApp\Responses;


interface IAjaxResponse
{

    static function template($status, $body, $message = '', $count = '');

    static function Success($body, $message = '', $count = '');

    static function Error($message, $body = '', $count = '');

    static function Auto($body, $message = '', $count = '');

}