<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\Exc\EInvalidVersion;
use MaciejSz\PjFreeze\Exc\EVersionMismatch;
use MaciejSz\PjFreeze\PjFreeze;

/**
 * @internal
 */
class PjSerializeProcess
{
    /**
     * @var \SplObjectStorage
     */
    private $_Seen;

    /**
     * @var array
     */
    private $_serialized_objects_dict = [];

    /**
     * @var array
     */
    private $_path_references = [];

    /**
     * @var null|\stdClass
     */
    private $_meta = null;

    /**
     */
    public function __construct()
    {
        $this->_Seen = new \SplObjectStorage();
    }

    /**
     * @param object $Object
     * @return bool
     */
    public function hasSeen($Object)
    {
        if ( !is_object($Object) ) {
            return false;
        }
        return $this->_Seen->contains($Object);
    }

    /**
     * @param $Object
     * @return int
     * @throws EInvalidVersion
     */
    public function putSeen($Object)
    {
        $idx = count($this->_Seen);
        $idx = "0x" . dechex($idx);
        $this->_Seen->attach($Object, $idx);

        $this->_ensureMeta();

        $class = get_class($Object);
        $version = PjFreeze::getSerialVersionUid($class);
        $this->_meta->classes[$idx] = $class;
        try {
            $this->_setVersion($class, $version);
        }
        catch ( EVersionMismatch $Exc ) {
            throw new EInvalidVersion($class, $Exc);
        }

        return $idx;
    }

    /**
     * @param int $idx
     * @param $serialized
     */
    public function putObjectRepresentation($idx, $serialized)
    {
        $this->_serialized_objects_dict[$idx] = $serialized;
    }

    /**
     * @param array $path
     * @param null|string $idx
     * @return $this
     */
    public function addPathReference(array $path, $idx = null)
    {
        if ( null === $idx ) {
            return $this;
        }
        if ( empty($path) ) {
            return $this;
        }
        $key = implode(".", $path);
        $this->_path_references[$key] = $idx;
        return $this;
    }

    /**
     * @param mixed $mOriginalRoot
     * @param mixed $mSerializedRoot
     * @return SerializationResult
     */
    public function makeResult($mOriginalRoot, $mSerializedRoot)
    {
        $this->_ensureMeta();
        $Sanitizer = RootSanitizer::makeFor($mOriginalRoot);
        $mSerializedRoot = $Sanitizer->sanitize($mSerializedRoot, $this);
        $Result = new SerializationResult(
            $mSerializedRoot,
            $this->_serialized_objects_dict,
            $this->_meta
        );
        return $Result;
    }

    /**
     * @param object $Object
     * @return int
     */
    public function tryGetObjectReference($Object)
    {
        if ( !is_object($Object) ) {
            return null;
        }
        if ( !$this->hasSeen($Object) ) {
            return null;
        }
        return $this->_Seen->offsetGet($Object);
    }

    /**
     * @param SerializationResult $Res
     * @return mixed
     */
    public function extractSerialized(SerializationResult $Res)
    {
        return $Res->getRawRoot();
    }

    /**
     * @return $this
     */
    protected function _ensureMeta()
    {
        if ( null !== $this->_meta ) {
            return $this;
        }
        $this->_meta = (object)[
            "classes" => [],
            "versions" => [],
        ];
        return $this;
    }

    /**
     * @param string $class
     * @param mixed $version
     * @return $this
     * @throws EVersionMismatch
     */
    protected function _setVersion($class, $version)
    {
        if ( isset($this->_meta->versions[$class]) ) {
            if ( $this->_meta->versions[$class] !== $version ) {
                throw new EVersionMismatch($this->_meta->versions[$class], $version);
            }
            return $this;
        }
        $this->_meta->versions[$class] = $version;
        return $this;
    }
}