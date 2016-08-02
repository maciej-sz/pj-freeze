<?php
namespace MaciejSzUtFix\PjFreeze;

class FixtureHelper
{
    /** @var string */
    private $_dir;

    /** @var string */
    private $_sub_dir;

    /**
     * @param null|string $sub_dir
     * @param null|string $dir
     */
    public function __construct($sub_dir = null, $dir = null)
    {
        if ( null === $sub_dir ) {
            $sub_dir = "";
        }
        if ( null === $dir ) {
            $dir = APP_DIR . "/tests/php-unit/fixtures/data";
        }
        $this->_sub_dir = $sub_dir;
        $this->_dir = $dir;
    }

    /**
     * @param null|string $sub_dir
     * @param null|string $dir
     * @return FixtureHelper
     */
    public static function factory($sub_dir = null, $dir = null)
    {
        return new self($sub_dir, $dir);
    }

    /**
     * @param \JsonSerializable $Object
     * @return string
     */
    public static function encodeJson(\JsonSerializable $Object)
    {
        return json_encode($Object, JSON_PRETTY_PRINT);
    }

    /**
     * @param string $fixture_file
     * @param null|string $file_ext
     * @return string
     */
    public function getContents($fixture_file, $file_ext = null)
    {
        if ( null === $file_ext ) {
            $file_ext = "txt";
        }
        $trim = "\\/";
        $path = rtrim($this->_dir, $trim);
        $path .= DIRECTORY_SEPARATOR . trim($this->_sub_dir, $trim);
        $path .= DIRECTORY_SEPARATOR . $fixture_file;
        $path .= ".{$file_ext}";
        return file_get_contents($path);
    }
}