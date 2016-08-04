<?php
namespace MaciejSz\PjFreeze\Process;

/**
 * @internal
 */
class PjSerializeStatus
{
    /**
     * @var PjSerializeProcess
     */
    private $_Process;

    /**
     * @var string[]
     */
    private $_path = [];

    /**
     * @param PjSerializeProcess $Process
     */
    public function __construct(PjSerializeProcess $Process)
    {
        $this->_Process = $Process;
    }

    /**
     * @param string $fragment
     * @param null|string $idx
     * @return PjSerializeStatus
     */
    public function appendPathProperty($fragment, $idx = null)
    {
        $Instance = clone $this;
        $Instance->_path[] = $fragment;
        $Instance->_Process->addPathReference($this->_path, $idx);
        return $Instance;
    }

    /**
     * @param int|string $fragment
     * @param null|string $idx
     * @return PjSerializeStatus
     */
    public function appendPathTraversable($fragment, $idx = null)
    {
        if ( is_int($fragment) ) {
            $fragment = "[{$fragment}]";
        }
        else {
            $fragment = "[\"{$fragment}\"]";
        }
        $Instance = clone $this;
        $Instance->_path[] = $fragment;
        $Instance->_Process->addPathReference($this->_path, $idx);
        return $Instance;
    }

    /**
     * @return PjSerializeProcess
     */
    public function getProcess()
    {
        return $this->_Process;
    }

    /**
     * @return string[]
     */
    public function getPath()
    {
        return $this->_path;
    }
}