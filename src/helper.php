<?php

if(!function_exists('is_email')) {
    /**
     * @param string $contents
     * @return string
     */
    function is_email(string $contents): bool
    {
        $result = preg_match('/^([\w\-.]+)@([\w\-.]+)\.([0-9a-zA-Z]+)$/', $contents);

        return $result !== false && $result >0;
    }
}
if(!function_exists('limit_bin2hex')) {
    /**
     * @param string $contents
     * @return string
     */
    function limit_bin2hex(string $contents): string
    {

        $hex = bin2hex($contents);
        if (strlen($hex) > 64) {
            $hex = substr($hex, 0, 64) . ' ...more data(' . (strlen($hex) - 64) . ') skipped';
        }
        return $hex;
    }
}

if(!function_exists('array_copy')) {
    /**
     * @param array $source
     * @param int $index
     * @param array $dest
     * @param int $offset
     * @param int $count
     */
    function array_copy(array $source, int $index, array &$dest, int $offset, int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $dest[$i + $offset] = $source[$i + $index];
        }
    }
}
if(!function_exists('array_clone')) {
    /**
     * @param array $source
     * @param int $offset
     * @param int $length
     * @return array
     */
    function array_clone(array $source, int $offset, int $length)
    {
        $res = [];
        for ($i = 0; $i < $length; $i++) {
            $res[] = $source[$i + $offset];
        }
        return $res;
    }
}
if(!function_exists('array_clone_all')) {

    /**
     * @param array $source
     * @return array
     */
    function array_clone_all(array $source)
    {
        return array_clone($source, 0, count($source));
    }
}

if(!function_exists('next_bytes')) {

    /**
     * @param array $b
     * @param int $offset
     * @param int $nBytes
     */
    function next_bytes(array &$b, int $offset, int $nBytes)
    {
        for ($i = 0; $i < $nBytes; $i++) {
            $b[$offset + $i] = mt_rand(0, 255);
        }
    }
}

if(!function_exists('next_byte')) {
    /**
     * @return int
     */
    function next_byte()
    {
        return mt_rand(0, 255);
    }
}

if(!function_exists('debug_asset')) {
    /**
     * @param bool $condition
     * @throws Exception
     */
    function debug_asset($condition)
    {
        if (!$condition) throw new \Exception('arguments error');
    }
}
if(!function_exists('array_new')) {

    /**
     * @param int $length
     * @param int $padding
     * @return array
     */
    function array_new(int $length, int $padding = 0): array
    {
        return array_fill(0, $length, $padding);
    }
}
if(!function_exists('array_clear')) {
    /**
     * @param array $source
     * @param int $offset
     * @param int $length
     */
    function array_clear(&$source, $offset, $length)
    {
        for ($i = 0; $i < $length; $i++) {
            $source[$i + $offset] = 0;
        }
    }
}

if(!function_exists('arr2bin')) {
    /**
     * @param array $arr
     * @return string
     */
    function arr2bin(array $arr): string
    {
        array_unshift($arr, 'C*');

        return call_user_func_array('pack', $arr);
    }
}


if(!function_exists('bin2com')) {

    /**
     * @param $binary
     * @throws Exception
     */
    function bin2com(string &$binary)
    {
        $last = -1;
        $len = strlen($binary);
        for ($i = 0; $i < $len; $i++) {
            $ord = ord($binary[$i]);
            $val = $i === 0 ? (0x80 | ~($ord & 0x7f)) : ~$ord;
            $binary[$i] = chr($val);
            if ($val != 0xff) $last = $i;
        }
        if ($last === -1) {
            throw new \Exception('binary data error');
        }
        $binary[$last] = chr((ord($binary[$last]) + 1) & 0xff);
        if ($last < $len - 1) {
            for ($i = $last + 1; $i < $len; $i++) {
                $binary[$i] = "\0";
            }
        }
    }
}

if(!function_exists('base64_encode_chunked')) {
    function base64_encode_chunked($source, $chunkSize = 64)
    {
        return rtrim(chunk_split(base64_encode($source), $chunkSize));
    }
}
if(!function_exists('indent')) {
    function indent($source, $length)
    {
        return preg_replace('/^/m', str_pad('', $length,' ', STR_PAD_LEFT), (string)$source);
    }
}
if (!function_exists('array_is_list'))
{
    function array_is_list(array $a)
    {
        return $a === [] || (array_keys($a) === range(0, count($a) - 1));
    }
}
if(!function_exists('build_query')){
    define('BUILD_QUERY_WITHOUT_NUMBER_INDEX', 4);
    define('BUILD_QUERY_WITHOUT_BRACKETS', 8);
    define('BUILD_QUERY_WITHOUT_EMPTY_VALUE', 16);
    define('BUILD_QUERY_WITHOUT_NULL_VALUE', 32);
    define('BUILD_QUERY_WITHOUT_EMPTY_STRING_VALUE', 64);
    define('BUILD_QUERY_WITHOUT_NULL_OR_EMPTY_STRING', 96);
    define('PHP_QUERY_NONE_ENCODING', 256);

    function query_encoder($array, $prefix, $encoder, &$query, $options){
        $isList = array_is_list($array);
        foreach ($array as $key => $value){
            if(!$prefix) {
                $key = $isList ? $options['numberIndexPrefix'] . $key : $key;
            }else{
                $key = ($isList && ($options['withoutNumberIndex'] || $options['withoutBrackets'])) ? '' : $key;
                if(!$isList || !$options['withoutBrackets']) $key = '[' . $key . ']';
                $key = $prefix . $key;
            }

            if(!is_array($value)){
                if(empty($value) && $options['withoutEmptyValue']) continue;
                if($value === null && $options['withoutNullValue']) continue;
                if($value === '' && $options['withoutEmptyStringValue']) continue;
                $query[] = $encoder($key) . '=' . $encoder((string)$value);
                continue;
            }
            query_encoder($value, $key, $encoder, $query, $options);
        }
    }
    function rawEncoder($a){ return $a;}

    function build_query($array, $options = 0, $numberIndexPrefix = ''){
        if(empty($array)) return '';
        $encoder = null;

        if (($options & PHP_QUERY_NONE_ENCODING) > 0) $encoder = 'rawEncoder';
        else if (($options & PHP_QUERY_RFC1738) > 0) $encoder = 'urlencode';
        else $encoder = 'rawurlencode';

        $options_ = [
            'numberIndexPrefix' => $numberIndexPrefix,
            'withoutNumberIndex' => ($options & BUILD_QUERY_WITHOUT_NUMBER_INDEX) > 0,
            'withoutBrackets' => ($options & BUILD_QUERY_WITHOUT_BRACKETS) > 0,
            'withoutEmptyValue' => ($options & BUILD_QUERY_WITHOUT_EMPTY_VALUE) > 0,
            'withoutNullValue' => ($options & BUILD_QUERY_WITHOUT_NULL_VALUE) > 0,
            'withoutEmptyStringValue' => ($options & BUILD_QUERY_WITHOUT_EMPTY_STRING_VALUE) > 0,
        ];

        query_encoder($array, '', $encoder, $query, $options_);

        return implode('&', $query);
    }
}
