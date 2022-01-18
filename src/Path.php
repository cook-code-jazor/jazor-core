<?php
namespace Jazor;

class Path
{
    public const FORWARD_SLASH = '/';
    public const BACK_SLASH = '\\';
    public const WIN_VOLUME_SEPARATOR_CHAR = ':';
    public const PLATFORM_WIN = 'win';
    public const PLATFORM_UNIX = 'unix';

    public const INVALID_PATH_CHARS = ['"', '<', '>', '|'];
    public const INVALID_PATH_CHARS_WITH_ADDITIONAL_CHECKS = [...self::INVALID_PATH_CHARS, '*', '?'];
    public const INVALID_FILE_NAME_CHARS = [...self::INVALID_PATH_CHARS_WITH_ADDITIONAL_CHECKS, ':', '/', '\\'];


    public static function isValidPath($path){
        for ($i = 0; $i < strlen($path); $i++){
            if(ord($path[$i]) < 0x20 || in_array($path[$i], self::INVALID_PATH_CHARS_WITH_ADDITIONAL_CHECKS)){
                return false;
            }
        }
        return true;
    }

    public static function format($path){
        for ($i = 0; $i < strlen($path); $i++) {
            $chr = $path[$i];
            if (($chr === self::BACK_SLASH || $chr === self::FORWARD_SLASH) && $chr !== DIRECTORY_SEPARATOR) {
                $path[$i] = DIRECTORY_SEPARATOR;
            }
        }
        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $path
     * @param mixed ...$paths
     * @return mixed|string
     */
    public static function combine(string $path, ...$paths): string
    {
        foreach ($paths as $p) {
            if (empty($p)) continue;
            if (empty($path)) {
                $path = $p;
                continue;
            }
            $chr = $p[0];
            if ((($chr === self::BACK_SLASH || $chr === self::FORWARD_SLASH) && DIRECTORY_SEPARATOR === self::FORWARD_SLASH)
                || (strlen($p) > 1 && $p[1] === self::WIN_VOLUME_SEPARATOR_CHAR && DIRECTORY_SEPARATOR === self::BACK_SLASH)) {
                $path = $p;
                continue;
            }

            $chr = $path[strlen($path) - 1];
            if ($chr === self::BACK_SLASH || $chr === self::FORWARD_SLASH) {
                $path .= $p;
                continue;
            }
            $path .= DIRECTORY_SEPARATOR . $p;
        }

        return self::getFullPath($path);
    }

    /**
     * @param string $path
     * @return array
     */
    private static function splitPath(string $path): array
    {
        $parts = [];
        $startIndex = 0;
        for ($i = 0; $i < strlen($path); $i++) {
            $chr = $path[$i];
            if (($chr === self::BACK_SLASH || $chr === self::FORWARD_SLASH) && $chr !== DIRECTORY_SEPARATOR) {
                $path[$i] = $chr = DIRECTORY_SEPARATOR;
            }
            if ($chr === DIRECTORY_SEPARATOR) {
                if ($i > 1 && $path[$i - 1] === DIRECTORY_SEPARATOR) {
                    $startIndex = $i + 1;
                    continue;
                }
                $parts[] = substr($path, $startIndex, $i - $startIndex);
                $startIndex = $i + 1;
            }
        }
        if ($startIndex < strlen($path)) {
            $parts[] = substr($path, $startIndex);
        }
        return $parts;
    }

    /**
     * @param string $path
     * @param string|null $platform PLATFORM_WIN|PLATFORM_UNIX
     * @return string
     */
    public static function getFullPath(string $path, ?string $platform = null): string
    {
        if (empty($path)) return '';
        $separator = $platform === null ? DIRECTORY_SEPARATOR : ($platform === self::PLATFORM_WIN ? self::BACK_SLASH : self::FORWARD_SLASH);
        $parts = self::splitPath($path);

        $prefix = '';
        $firstPart = $parts[0];
        if ($firstPart === '')
            $prefix = $separator;
        else if (strlen($firstPart) > 1 && $firstPart[1] === self::WIN_VOLUME_SEPARATOR_CHAR) {
            $prefix = $firstPart . $separator;
            array_shift($parts);
        }

        $lastReal = -1;
        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
            if ($part != '.' && $part != '..') {
                $lastReal = $i;
                continue;
            }
            $parts[$i] = null;
            if ($part === '.' || $lastReal === -1) {
                continue;
            }
            while ($lastReal > -1) {
                if ($parts[$lastReal] === null) {
                    $lastReal--;
                    continue;
                }
                $parts[$lastReal] = null;
                $lastReal--;
                break;
            }
        }
        $parts = array_filter($parts, function ($p) {
            return !empty($p);
        });


        return $prefix . implode($separator, $parts);
    }
}
