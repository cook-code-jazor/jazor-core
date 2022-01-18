<?php


namespace Jazor;


class Console
{
    private static function parseFormat($format){
        return preg_replace('/\\{([0-9]+)\\}/', '%s', $format);
    }
    public static function WriteLine($line = null){
        $args = func_get_args();
        if(count($args) > 1){
            $format = array_shift($args);
            echo vsprintf(self::parseFormat($format) . "\r\n", $args);
            return;
        }
        if(empty($line) && is_string($line)){
            echo "\r\n";
            return;
        }
        if(is_bool($line)){
            echo $line ? "true\r\n" : "false\r\n";
            return;
        }
        echo sprintf("%s\r\n", $line);
    }

    public static function Write($str = null){
        $args = func_get_args();
        if(count($args) > 1){
            $format = array_shift($args);
            echo vsprintf(self::parseFormat($format), $args);
            return;
        }
        if(is_bool($str)){
            echo $str ? 'true' : 'false';
            return;
        }
        if(empty($str)){
            echo "";
            return;
        }
        echo sprintf("%s", $str);
    }
}
