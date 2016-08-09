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
     * @var bool
     */
    private $_only_scalars = false;

    /**
     * @var null|array
     */
    private $_fill_items = null;

    /**
     * @param PjSerializeProcess $Process
     */
    public function __construct(PjSerializeProcess $Process)
    {
        $this->_Process = $Process;
    }

    /**
     * @param string $fragment
     * @return $this New instance.
     */
    public function appendPath($fragment)
    {
        $Instance = clone $this;
        $Instance->_path[] = $fragment;
        return $Instance;
    }

    /**
     * @param $idx
     * @return $this New instance.
     */
    public function savePathIdx($idx)
    {
        $this->_Process->addPathReference($this->_path, $idx);
        return $this;
    }

    /**
     * @param int|string $fragment
     * @param null|string $idx
     * @return $this New instance.
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
     * @return $this New instance.
     */
    public function reset()
    {
        if ( null === $this->_fill_items ) {
            return $this;
        }
        $Instance = clone $this;
        $Instance->_fill_items = null;
        return $Instance;
    }

    /**
     * @param bool $flag
     * @return $this New instance.
     */
    public function onlyScalars($flag = true)
    {
        $Instance = clone $this;
        $Instance->_only_scalars = $flag;
        return $Instance;
    }

    /**
     * @return bool
     */
    public function getOnlyScalars()
    {
        return $this->_only_scalars;
    }

    /**
     * @param array|null $items
     * @return $this New instance.
     */
    public function withFillItems(array $items = null)
    {
        $Instance = clone $this;
        $Instance->_fill_items = $items;
        return $Instance;
    }

    /**
     * @return array|null
     */
    public function tryGetFillItems()
    {
        return $this->_fill_items;
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