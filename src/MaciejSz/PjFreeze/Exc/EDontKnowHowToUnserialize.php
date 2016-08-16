<?php
namespace MaciejSz\PjFreeze\Exc;

use MaciejSz\PjFreeze\Process\ValueInfo;

class EDontKnowHowToUnserialize extends \RuntimeException
{
    public function __construct($info)
    {
        parent::__construct("Don't know how to unserialize: {$info}");
    }

    /**
     * @param $mValue
     * @return EDontKnowHowToUnserialize
     */
    public static function factory($mValue)
    {
        $info = ValueInfo::make($mValue);
        return new self($info);
    }

    public static function factoryReference($reference)
    {
        return new self("reference = {$reference}");
    }
}