<?php
/**
 * Returns a tax value in local specific format.
 *
 * @param int|float $value
 * @param string $locale
 * @return mixed
 */
function smarty_block_l($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    // only output on the closing tag
    if (!$repeat) {
        $name = $params['name'] ?? null;
        $lang = $params['lang'] ?? null;

        if (empty(trim($content))) $content = null;

        return ErkinApp()->Localization()->getTranslation($name, $lang, $content);
    }
}
