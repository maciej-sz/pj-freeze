<?php
namespace MaciejSz\PjFreeze\Process;

class PjUnserializeProcess
{
    /**
     * @var \stdClass
     */
    private $_serialized;

    /**
     * @var array
     */
    private $_object_references;

    /**
     * @param \stdClass $serialized
     */
    private function __construct(\stdClass $serialized)
    {
        $this->_serialized = $serialized;
        $this->_object_references = [];
    }

    /**
     * @param \stdClass $serialized
     * @return PjUnserializeProcess
     */
    public static function factory(\stdClass $serialized)
    {
        $sanitized = new \stdClass();
        $sanitized->root = $serialized->root;
        $sanitized->objects = (array)$serialized->objects;
        $sanitized->meta = new \stdClass();
        $sanitized->meta->classes = (array)$serialized->meta->classes;
        $sanitized->meta->versions = (array)$serialized->meta->versions;
        return new self($sanitized);
    }

    /**
     * @return \stdClass
     */
    public function getSerialized()
    {
        return $this->_serialized;
    }

    /**
     * @param string $idx
     * @return object|null
     */
    public function tryGetObject($idx)
    {
        if ( isset($this->_object_references[$idx]) ) {
            return $this->_object_references[$idx];
        }
        return null;
    }

    /**
     * @param string $idx
     * @param object $Object
     * @return $this
     */
    public function putObject($idx, $Object)
    {
        $this->_object_references[$idx] = $Object;
        return $this;
    }
}