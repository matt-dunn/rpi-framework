<?php

namespace RPI\Framework\Helpers;

/**
 * FileUtils
 * @author Matt Dunn
 */
class FileUtils
{
    private function __construct()
    {
    }

    public static function realPath($path)
    {
        if (($absolutePath = realpath($path)) !== false) {
            return $absolutePath;
        }

        $relativePath = $_SERVER["PHP_SELF"];
        
        $pharPath = implode(
            '/',
            array_reduce(
                explode('/', substr($path, strlen("phar://".$_SERVER["PHP_SELF"]))),
                function ($parts, $value) {
                    if ($value == '..') {
                        array_pop($parts);
                    } elseif ($value != '.') {
                        $parts[] = $value;
                    }
                    return $parts;
                }
            )
        );

        if (($resolvedPath = realpath($relativePath)) !== false) {
            if (file_exists($absolutePath = "phar://{$resolvedPath}{$pharPath}")) {
                return $absolutePath;
            }
        }
    }

    /**
     * 
     * @param string $basePath
     * @param string $includes      Pipe delimited list of patterns
     * @param string $excludes      Pipe delimited list of patterns
     * @param boolean $recursive
     * 
     * @return array
     */
    public static function find(
        $basePath,
        $includes,
        $excludes = null,
        $recursive = true
    ) {
        $files = array();

        if (file_exists($basePath)) {
            $dir = dir($basePath);
            while (false !== ($entry = $dir->read())) {
                $fullname = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$entry;
                if ($recursive && $entry != '.' && $entry != '..' && substr($entry, 0, 1) != "." && is_dir($fullname)) {
                    $files += self::find($fullname, $includes, $excludes, $recursive);
                } elseif (is_file($fullname)
                    && self::isMatch(explode("|", $includes), $fullname)
                    && (!isset($excludes) || (isset($excludes) && !self::isMatch(explode("|", $excludes), $fullname)))
                ) {
                    $files[$fullname] = filemtime($fullname);
                }
            }
            $dir->close();
        }

        return $files;
    }

    private static function isMatch(array $patterns, $file)
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the mime type of a file
     * @param  string $filename file name
     * @return string mime type of the file or false if unable to determine
     */
    public static function getMimeType($filename)
    {
        if (class_exists("finfo")) {
            $mimeMagicFile = get_cfg_var("mime_magic.magicfile");
            if ($mimeMagicFile !== false) {
                $finfo = new \finfo(FILEINFO_MIME, $mimeMagicFile);
            } else {
                $finfo = new \finfo(FILEINFO_MIME);
            }

            if ($finfo !== false) {
                // Hack to fix issue with some mime magic databases which return
                // mimtypes such as "application/msword application/msword"
                $mimeTypeParts = explode(";", $finfo->file($filename));
                $mimeType = explode(" ", $mimeTypeParts[0]);
                $mimeTypeParts[0] = $mimeType[0];

                return implode(";", $mimeTypeParts);
            } else {
                return false;
            }
        }

        return false;
    }

    public static function getImageFileExtension($filename, $includeDot = true)
    {
        $image_info = getimagesize($filename);
        if (!$image_info || empty($image_info[2])) {
            return false;
        }

        return image_type_to_extension($image_info[2], $includeDot);
    }

    /**
     * Copy files recursivly
     * @param  string  $source Source file path
     * @param  string  $dest   Destination file path
     * @return boolean True if successful
     */
    public static function copyr($source, $dest)
    {
        // Simple copy for a file
        if (is_file($source)) {
            $c = copy($source, $dest);
            chmod($dest, 0777);

            return $c;
        }
        // Make destination directory
        if (!is_dir($dest)) {
            $oldumask = umask(0);
            mkdir($dest, 0777);
            umask($oldumask);
        }
        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == "." || $entry == ".." || substr($entry, 0, 1) == ".") {
                continue;
            }
            // Deep copy directories
            if ($dest !== "$source/$entry") {
                self::copyr("$source/$entry", "$dest/$entry");
            }
        }
        // Clean up
        $dir->close();

        return true;
    }

    public static function exists($filename)
    {
        if (parse_url($filename, PHP_URL_SCHEME) == "ftp") {
            // *** CAUTION: FTP actions are *very* expensive ***
            // *** NOTE: ftp_mdtm is not supported by all ftp servers!
            $fileExists = false;
            $parts = parse_url($filename);
            $connect = ftp_connect($parts["host"]);
            if ($connect !== false) {
                $result = ftp_login($connect, $parts["user"], $parts["pass"]);
                if ($result !== false) {
                    if (ftp_mdtm($connect, $parts["path"]) !== -1) {
                        $fileExists = true;
                    } else {
                        try {
                            if (ftp_chdir($connect, $parts["path"])) {
                                $fileExists = true;
                            }
                        } catch (\Exception $ex) {
                            // Ignore
                        }
                    }
                } else {
                    ftp_close($connect);
                    throw new \RPI\Framework\Exceptions\RuntimeException(
                        "Unable to connect to ftp server '".$parts["host"]."' (invalid credentials)"
                    );
                }
                ftp_close($connect);
            } else {
                throw new \RPI\Framework\Exceptions\RuntimeException(
                    "Unable to connect to ftp server '".$parts["host"]."'"
                );
            }

            return $fileExists;
        } else {
            return file_exists($filename);
        }
    }

    public static function deleteFiles($path, $match, $recursive = false)
    {
        static $deld = 0;
        $dirs = false;

        if ($recursive) {
            $dirs = glob($path."*");
        }
        $files = glob($path.$match);

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $deld++;
            }
        }

        if (isset($dirs) && $dirs !== false) {
            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    $dir = basename($dir) . "/";
                    self::deleteFiles($path.$dir, $match, $recursive);
                }
            }
        }

        return $deld;
    }

    // TODO: incorporate this into the above function?
    public static function delTree($dir, &$files = array())
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        self::delTree($dir."/".$object, $files);
                    } else {
                        $files[] = $dir."/".$object;
                        unlink($dir."/".$object);
                    }
                }
            }

            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * join path components into a string
     *
     * e.g. joinPath("a", "b/c", "/d/e/f") => "a/b/c/d/e/f"
     *
     * @return a string containing a path
     * @author Jon Evans
     */
    public static function joinPath()
    {
        $args = func_get_args();
        $paths = array();
        foreach ($args as $arg) {
            $paths = array_merge($paths, (array) $arg);
        }
        foreach ($paths as &$path) {
            $path = trim($path, '/');
        }
        if (substr($args[0], 0, 1) == '/') {
            $paths[0] = '/' . $paths[0];
        }

        return join('/', $paths);
    }

    /**
     * Remove slashes from beginning and end of a string
     *
     * e.g. /test/path/ => test/path
     *
     * @param string $path
     */
    public static function trimSlashes($path)
    {
        return trim($path, DIRECTORY_SEPARATOR);
    }
}
