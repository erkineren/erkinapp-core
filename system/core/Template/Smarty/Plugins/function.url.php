<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.url.php
 * Type:     function
 * Name:     url
 * Purpose:  outputs a url
 * -------------------------------------------------------------
 */
function smarty_function_url($params, Smarty_Internal_Template $template)
{
    $path = '';

    if (isset($params['path'])) {
        $path = $params['path'];
    } elseif (isset($params['asset'])) {
        $base = ErkinApp()->TemplateManager()->getTemplate()->getThemeAssetsUrlPath();
        $path = "$base/" . $params['asset'];
    } elseif (isset($params['compiledAsset'])) {
        return ErkinApp()->TemplateManager()->getTemplate()->getAssetManager()->getPublicUrl($params['compiledAsset']);
    } elseif (isset($params['lang-path'])) {
        $path = ErkinApp()->AppRoutes()->findLanguagePath('/' . ltrim($params['lang-path'], '/')) ?? $params['lang-path'];
    }

    return \ErkinApp\Helpers\siteUrl($path);
}
