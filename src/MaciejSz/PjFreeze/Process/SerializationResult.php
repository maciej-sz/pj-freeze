<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\PjFreeze;

class SerializationResult implements \JsonSerializable
{
    /**
     * @var mixed
     */
    private $_root = null;

    /**
     * @var array
     */
    private $_objects = [];

    /**
     * @var \stdClass
     */
    private $_meta;

    /**
     * @param mixed $root
     * @param array $objects
     * @param \stdClass $meta
     */
    public function __construct($root, array $objects, \stdClass $meta)
    {
        $this->_root = $root;
        $this->_objects = $objects;
        $this->_meta = $meta;
    }

    /**
     * @return object
     */
    public function jsonSerialize()
    {
        return (object)[
            "root" => $this->_root,
            "objects" => $this->_objects,
            "meta" => $this->_meta,
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
    public function getRawRoot()
    {
        return $this->_root;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        $ref = PjFreeze::tryExtractReference($this->_root);
        if ($ref) {
            return $this->_objects[$ref];
        }
        return $this->_root;
    }
}