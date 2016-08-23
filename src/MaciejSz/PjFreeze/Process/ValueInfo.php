<?php
namespace MaciejSz\PjFreeze\Process;

class ValueInfo
{
    /**
     * @param mixed $mValue
     * @return string
     */
    public static function make($mValue)
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
        return $info;
    }
}