<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\PjFreeze;

class RootSanitizer
{
    /**
     * @var mixed
     */
    private $_mOriginalRoot;

    /**
     * @param mixed $mOriginalRoot
     */
    public function __construct($mOriginalRoot)
    {
        $this->_mOriginalRoot = $mOriginalRoot;
    }

    /**
     * @param $mOriginalRoot
     * @return RootSanitizer
     */
    public static function makeFor($mOriginalRoot)
    {
        return new self($mOriginalRoot);
    }

    /**
     * @param mixed $mSerializedRoot
     * @param PjFreezeProcess $Process
     * @return mixed
     */
    public function sanitize($mSerializedRoot, PjFreezeProcess $Process)
    {
        if ( !is_object($this->_mOriginalRoot) ) {
            return $mSerializedRoot;
        }
        $idx = $Process->tryGetObjectReference($this->_mOriginalRoot);
        return PjFreeze::buildKey($idx);
    }
}