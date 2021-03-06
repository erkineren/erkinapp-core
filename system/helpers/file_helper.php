<?php

namespace ErkinApp\Helpers {

    use ZipArchive;

    /**
     * Remove directory and its contents recursively
     *
     * @param $dir
     */
    function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * @param $zipfile
     * @param string $destination
     * @return bool
     */
    function unzipFile($zipfile, $destination = './')
    {
        $zip = new ZipArchive;
        $res = $zip->open($zipfile);
        if ($res === TRUE) {
            $zip->extractTo($destination);
            $zip->close();
            return true;
        }
        return false;
    }
}