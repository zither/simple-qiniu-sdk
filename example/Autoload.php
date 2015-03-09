<?php

class Autoload 
{
    public static $prefixes = array();

    public static function addNamespace($prefix, $baseDir, $prepend = false)
    {
        $prefix = trim($prefix, "\\") . "\\";
        $baseDir = rtrim($baseDir, "/") . DIRECTORY_SEPARATOR;
        if (!isset(self::$prefixes[$prefix])) {
            self::$prefixes[$prefix] = array();
        }
        if ($prepend) {
            array_unshift(self::$prefixes[$prefix], $baseDir);
        } else {
            array_push(self::$prefixes[$prefix], $baseDir);
        }
     }

    public static function autoloader($className)
    {
        $prefix = ltrim($className, "\\");
        $file = null;
        while (($pos = strrpos($prefix, "\\")) !== false) {
            $prefix = substr($className, 0, $pos + 1);
            $relativeClass = substr($className, $pos + 1);
            // 是否设置了对应的 namespace 前缀
            if (isset(self::$prefixes[$prefix])) {
                foreach (self::$prefixes[$prefix] as $baseDir) {
                    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . ".php";
                    if (file_exists($file)) {
                        return require $file;
                    }
                }
            }
            $prefix = rtrim($prefix, "\\");
        }
        return false;
    }

    public static function register()
    {
        spl_autoload_register(array(__CLASS__, "autoloader"));
    }
}
