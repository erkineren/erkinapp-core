<?php

namespace ErkinApp\Helpers {
    function debugPrint($data, $die = true)
    {
        echo '<pre>';

        if (is_bool($data) || is_string($data) || is_null($data))
            var_dump($data);
        else
            print_r($data);

        echo '</pre>';
        if ($die)
            die;
    }

    function debugVarExport($data, $die = true)
    {
        highlight_string("<?php\n " . var_export($data, true) . ";\n ?>");
        echo '<script>document.getElementsByTagName("code")[0].getElementsByTagName("span")[1].remove() ;document.getElementsByTagName("code")[0].getElementsByTagName("span")[document.getElementsByTagName("code")[0].getElementsByTagName("span").length - 1].remove() ; </script>';
        if ($die) {
            die;
        }
    }
}
