<?php

function smarty_function_langcode($params, Smarty_Internal_Template $template)
{
    return ErkinApp()->Localization()->getCurrentLanguage();
}
