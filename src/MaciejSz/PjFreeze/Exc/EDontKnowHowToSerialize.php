<?php
namespace MaciejSz\PjFreeze\Exc;

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
        $info = null;
        if ( is_object($mValue) ) {
            $info = "object (" . get_class($mValue) . ")";
        }
        else if ( is_array($mValue) ) {
            $info = "array (" . count($mValue) . ")";
        }
        else if ( is_resource($mValue) ) {
            $info = "resource";
        }
        else {
            $info = "{$mValue}";
        }

        return new self($info);
    }
}