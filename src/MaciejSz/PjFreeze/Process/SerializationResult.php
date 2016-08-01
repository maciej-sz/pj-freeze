<?php
namespace MaciejSz\PjFreeze\Process;

class SerializationResult implements \JsonSerializable
{
    /**
     * @var array
     */
    private $_objects = [];

    /**
     * @var mixed
     */
    private $_root = null;

    /**
     * @param mixed $root
     * @param array $objects
     */
    public function __construct($root, array $objects)
    {
        $this->_root = $root;
        $this->_objects = $objects;
    }

    public function jsonSerialize()
    {
        return (object)[
            "__pj_freeze_objects" => $this->_objects,
            "__pj_freeze_root" => $this->_root,
        ];
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        return $this->_objects;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->_root;
    }
}