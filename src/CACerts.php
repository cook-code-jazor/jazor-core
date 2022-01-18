<?php


namespace Jazor;


/**
 * Class CACerts
 * @package Jazor
 * @link https://curl.se/ca/cacert.pem
 */
class CACerts
{
    const FLAG_NONE = 0;
    const FLAG_NEW_CERT = 1;
    private static string $default_ca_file = __DIR__ . DIRECTORY_SEPARATOR . 'cacert.pem';

    private static ?array $default = null;

    public static function getDefault() : array
    {
        if (self::$default != null) return self::$default;
        return self::$default = (new CACerts())->getCerts();
    }

    private string $ca_file;
    private array $certs;
    public function __construct($ca_file = null)
    {
        $this->ca_file = $ca_file ?? self::$default_ca_file;
        if(!is_file($this->ca_file)) throw new \Exception('can not find \'' . $ca_file . '\'');
        $this->certs = $this->parseCerts();
    }

    private function parseCerts(){
        $ca_file = $this->ca_file;

        $fp = fopen($ca_file, 'r');

        $certs = [];
        $flag = self::FLAG_NONE;
        $certName = '';
        $certContents = '';
        while (!feof($fp)){
            $line = trim(fgets($fp));
            if(empty($line)) continue;
            if(substr($line, 0, 2) === '##' || empty(trim($line, '='))) continue;

            if($flag === self::FLAG_NONE){
                $certName = $line;
                $flag = self::FLAG_NEW_CERT;
                continue;
            }
            $certContents .= $line . "\r\n";
            if($line === '-----END CERTIFICATE-----'){
                $certs[] = [$certName, $certContents];
                $flag = self::FLAG_NONE;
                $certName = '';
                $certContents = '';
            }
        }

        fclose($fp);

        return $certs;
    }

    /**
     * @return array
     */
    public function getCerts(): array
    {
        return $this->certs;
    }
}
