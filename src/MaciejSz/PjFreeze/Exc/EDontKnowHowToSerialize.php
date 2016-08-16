<?php
namespace MaciejSz\PjFreeze\Exc;

use MaciejSz\PjFreeze\Process\ValueInfo;

class EDontKnowHowToSerialize extends \RuntimeException
{
    /**
     * @param string $value_info
     */
    public function __construct($value_info)
    {
        parent::__construct("Don't know how to serialize: {$value_info}");
    }

    /**
     * @param mixed $mValue
     * @return EDontKnowHowToSerialize
     */
    public static function factory($mValue)
    {
        $info = ValueInfo::make($mValue);
        return new self($info);
    }
}