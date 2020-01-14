<?php

function smarty_function_lang($params, Smarty_Internal_Template $template)
{
    $name = $params['name'] ?? null;
    $lang = $params['lang'] ?? null;
    $default = $params['default'] ?? null;
    $data = ErkinApp()->Localization()->getTranslation($name, $lang, $default);

    if (isset($params['assign'])) {
        $template->assign($params['assign'], $data);
    } else {
        return $data;
    }
}
