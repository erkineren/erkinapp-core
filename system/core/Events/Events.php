<?php
/**
 * Created by PhpStorm.
 * User: erkin
 * Date: 21.11.2018
 * Time: 00:54
 */

namespace ErkinApp\Events;


class Events
{
    const REQUEST = 'onRequest';
    const RESPONSE = 'onResponse';
    const API_BASIC_AUTHENTICATION = 'onApiBasicAuthentication';
    const CHECK_LOGGED_IN_STATUS = 'onCheckLoggedInStatus';
    const REQUEST_BEFORE_ROUTING = 'onRequestBeforeRouting';
}