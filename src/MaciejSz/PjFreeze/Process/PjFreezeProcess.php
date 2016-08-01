<?php
namespace MaciejSz\PjFreeze\Process;

class PjFreezeProcess
{
    /**
     * @var \SplObjectStorage
     */
    private $_Instances;

    /**
     * @var array
     */
    private $_references = [];

    /**
     * @var array
     */
    private $_serialized_objects_dict = [];

    public function __construct()
    {
        $this->_Instances = new \SplObjectStorage();
    }

    /**
     * @param object $Object
     * @return bool
     */
    public function hasObject($Object)
    {
        return $this->_Instances->contains($Object);
    }

    /**
     * @param $Object
     * @return int
     */
    public function putObject($Object)
    {
        $idx = count($this->_references);
        $this->_Instances->attach($Object, $idx);
        $this->_references[$idx] = $Object;
        return $idx;
    }

    /**
     * @param int $idx
     * @param \stdClass $serializedItem
     */
    public function putObjectRepresentation($idx, \stdClass $serializedItem)
    {
        $this->_serialized_objects_dict[$idx] = $serializedItem;
    }

    /**
     * @param object $Object
     * @return int
     */
    public function getObjectReference($Object)
    {
        return $this->_Instances->offsetGet($Object);
    }

    /**
     * @param mixed $mRoot
     * @return SerializationResult
     */
    public function makeResult($mRoot)
    {
        return new SerializationResult(
            $mRoot,
            $this->_serialized_objects_dict
        );
    }
}