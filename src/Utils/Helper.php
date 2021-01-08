<?php


namespace Shirokovnv\DomainReflection\Utils;

/**
 * Class Helper
 * @package Shirokovnv\DomainReflection\Utils
 */
class Helper
{

    /**
     * Scans for .php laravel model files in specified path and returns collection of class names
     * @param string $domain_path
     * @param string $namespace
     * @return \Illuminate\Support\Collection
     */
    public static function collectClassInfo(string $domain_path, string $namespace)
    {
        $path = app_path($domain_path);

        $class_list = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path
            ), \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            /**
             * @var \SplFileInfo $item
             */
            if ($item->isReadable() &&
                $item->isFile() &&
                mb_strtolower($item->getExtension()) === 'php') {
                $name_from_root = str_replace(
                    "/",
                    "\\", mb_substr($item->getRealPath(),
                    mb_strlen($path), -4));

                $full_class_name = $namespace . "\\" . $name_from_root;

                $class_list[] = $full_class_name;

            }
        }
        return collect($class_list);
    }
}
