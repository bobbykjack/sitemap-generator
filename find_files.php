<?php

declare(strict_types=1);

/**
 * Scan a directory, recursively, looking for files, optionally filtered by
 * extension.
 *
 * Note that, although the action is recursive, this function is iterative, not
 * resursive.
 *
 * @param $dir
 * @param $ext
 */
function find_files($dir, $ext = "", $hidden = true)
{
    if ($ext == "") {
        $ext = array();
    } else if (!is_array($ext)) {
        $ext = array($ext);
    }

    $stack = array($dir);
    $files = array();

    while ($dir = array_pop($stack)) {
        if (is_link($dir)) {
            continue;
        }

        if (($handle = @opendir($dir)) === false) {
            continue;
        }

        while (($entry = readdir($handle)) !== false) {
            if ($entry == "." || $entry == "..") {
                continue;
            }

            if (!$hidden && $entry[0] == ".") {
                continue;
            }

            $file = "$dir/$entry";

            if (is_link($file)) {
                continue;
            }

            if (is_file($file)) {
                $pi = pathinfo($file);

                $ext_matches = count($ext) == 0 ||
                    (array_key_exists("extension", $pi)
                        && in_array($pi["extension"], $ext));

                if ($ext_matches) {
                    $files[] = $file;
                }
            }

            if (is_dir($file)) {
                $stack[] = $file;
            }
        }

        closedir($handle);
    }

    return $files;
}
