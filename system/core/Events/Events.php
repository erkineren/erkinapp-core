<?php


namespace ErkinApp\Events;


class Events
{
    const REQUEST = 'onRequest';
    const RESPONSE = 'onResponse';
    const API_BASIC_AUTHENTICATION = 'onApiBasicAuthentication';
    const CHECK_LOGGED_IN_STATUS = 'onCheckLoggedInStatus';
    const ROUTING = 'onRouting';
    const CONTROLLER_NOT_FOUND = 'onControllerNotFound';
    const ACTION_NOT_FOUND = 'onActionNotFound';
    const ERROR = 'onError';
}