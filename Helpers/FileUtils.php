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

    public static $cacheEnabled = false;

    //static class files
    /**
     * Recursively find files based on a search pattern
     * @param string $path       Start path
     * @param string $pattern    File match pattern
     * @param array  $files      List of matched files
     * @param bool   $recursive  (optional) Search $path recursively
     * @param bool   $allowCache (optional)
     *
     * @author Matt Dunn
     */
    public static function find(
        $path,
        $pattern,
        array &$files = null,
        $recursive = true,
        $allowCache = true
    ) {
        $cacheId = false;
        
        if (!isset($files)) {
            $files = array();
        }
        
        if (self::$cacheEnabled && $allowCache) {
            $args = func_get_args();
            $cacheId = \RPI\Framework\Cache\File::getCacheId(__CLASS__, __FUNCTION__, $args);
            $buffer = \RPI\Framework\Cache\File::getContent($cacheId);
            if ($buffer !== false) {
                $files += unserialize($buffer);

                return;
            }
        }

        $searchArray = array();
        if (parse_url($path, PHP_URL_SCHEME) == "ftp") {
            $parts = parse_url($path);
            $connect = ftp_connect($parts["host"]);
            if ($connect !== false) {
                $result = ftp_login($connect, $parts["user"], $parts["pass"]);
                if ($result !== false) {
                    self::findFilesFTP(
                        "ftp://".$parts["user"].":".$parts["pass"]."@".$parts["host"],
                        $connect,
                        $parts["path"]."/",
                        $pattern,
                        $searchArray,
                        $recursive
                    );
                } else {
                    ftp_close($connect);
                    throw new Exception("Unable to connect to ftp server '".$parts["host"]."' (invalid credentials)");
                }
                ftp_close($connect);
            } else {
                throw new Exception("Unable to connect to ftp server '".$parts["host"]."'");
            }
        } else {
            self::findFiles(realpath($path), $pattern, $searchArray, $recursive);
        }
        $files += $searchArray;

        if (self::$cacheEnabled && $allowCache && $cacheId !== false) {
            \RPI\Framework\Cache\File::setContent($cacheId, serialize($searchArray));
        }
    }

    private static function findFiles($path, $pattern, array &$files, $recursive)
    {
        if (file_exists($path)) {
            $path = rtrim(str_replace("\\", "/", $path), '/') . '/';
            $dir = dir($path);
            while (false !== ($entry = $dir->read())) {
                $fullname = $path . $entry;
                if ($recursive && $entry != '.' && $entry != '..' && substr($entry, 0, 1) != "." && is_dir($fullname)) {
                    self::findFiles($fullname, $pattern, $files, $recursive);
                } elseif (is_file($fullname) && preg_match($pattern, $entry)) {
                    $files[$fullname] = filemtime($fullname);
                }
            }
            $dir->close();
        }
    }

    private static function findFilesFTP($base, $connect, $path, $pattern, array &$files, $recursive)
    {
        ftp_chdir($connect, $path);
        $remoteFiles = ftp_nlist($connect, ".");
        if ($remoteFiles) {
            foreach ($remoteFiles as $entry) {
                $fullname = $path . $entry;
                if ($recursive
                        && $entry != '.'
                        && $entry != '..'
                        && substr($entry, 0, 1) != "."
                        && self::ftpIsDir($connect, $fullname)) {
                    self::findFilesFTP($base, $connect, $fullname."/", $pattern, $files, $recursive);
                } elseif (preg_match($pattern, $entry)) {
                    $files[$base.$fullname] = $entry;
                }
            }
        }
    }

    private static function ftpIsDir($connect, $dir)
    {
        try {
            if (ftp_chdir($connect, $dir)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Recursively find directories based on a search pattern
     * @param string $path        Start path
     * @param string $pattern     File match pattern
     * @param array  $directories List of matched directories
     * @param bool   $recursive   (optional) Search $path recursively
     *
     * @author Matt Dunn
     */
    public static function findDirectories($path, $pattern, array &$directories, $recursive = true)
    {
        if (file_exists($path)) {
            $path = rtrim(str_replace("\\", "/", $path), '/') . '/';
            $dir = dir($path);
            while (false !== ($entry = $dir->read())) {
                if ($entry != '.' && $entry != '..' && substr($entry, 0, 1) != "." ) {
                    $fullname = $path . $entry;
                    if ($recursive && is_dir($fullname)) {
                        if (((isset($pattern) && preg_match($pattern, $entry)) || !isset($pattern))) {
                            $directories[$fullname] = pathinfo($fullname, PATHINFO_FILENAME);
                        }
                        self::findDirectories($fullname, $pattern, $directories, $recursive);
                    }
                }
            }
            $dir->close();
        }
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
                        } catch (Exception $ex) {
                            // Ignore
                        }
                    }
                } else {
                    ftp_close($connect);
                    throw new Exception("Unable to connect to ftp server '".$parts["host"]."' (invalid credentials)");
                }
                ftp_close($connect);
            } else {
                throw new Exception("Unable to connect to ftp server '".$parts["host"]."'");
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
        if (substr($path, 0, 1) == "/") {
            $path = substr($path, 1);
        }
        if (substr($path, strlen($path) - 1, 1) == "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }

        return $path;
    }
}
