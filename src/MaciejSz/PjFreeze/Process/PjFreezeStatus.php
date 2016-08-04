<?php
namespace MaciejSz\PjFreeze\Process;

class PjFreezeStatus
{
    /**
     * @var PjFreezeProcess
     */
    private $_Process;

    /**
     * @var string[]
     */
    private $_path = [];

    /**
     * @param PjFreezeProcess $Process
     */
    public function __construct(PjFreezeProcess $Process)
    {
        $this->_Process = $Process;
    }

    /**
     * @param string $fragment
     * @param null|string $idx
     * @return PjFreezeStatus
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
     * @return PjFreezeStatus
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
     * @return PjFreezeProcess
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