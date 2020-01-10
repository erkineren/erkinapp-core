<?php


namespace ErkinApp\Event;


class Events
{
    const REQUEST = 'onRequest';
    const RESPONSE = 'onResponse';
    const API_BASIC_AUTHENTICATION = 'onApiBasicAuthentication';
    const CHECK_LOGGED_IN_STATUS = 'onCheckLoggedInStatus';
    const ROUTING = 'onRouting';
    const ERROR = 'onError';
    const NOT_FOUND = 'onNotFound';

}