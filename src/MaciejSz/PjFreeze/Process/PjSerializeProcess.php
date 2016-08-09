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
     * @var bool
     */
    private $_is_greedy = false;

    /**
     * @var \SplObjectStorage
     */
    private $_Instances;

    /**
     * @var \SplObjectStorage
     */
    private $_ToFill;

    /**
     * @var array
     */
    private $_references = [];

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
     * @param null|bool $is_greedy
     */
    public function __construct($is_greedy = null)
    {
        if ( null === $is_greedy ) {
            $is_greedy = false;
        }
        $this->_is_greedy = $is_greedy;
        $this->_Instances = new \SplObjectStorage();
        $this->_ToFill = new \SplObjectStorage();
    }

    /**
     * @param object $Object
     * @return bool
     */
    public function hasObject($Object)
    {
        if ( !is_object($Object) ) {
            return false;
        }
        return $this->_Instances->contains($Object);
    }

    /**
     * @param $Object
     * @return int
     * @throws EInvalidVersion
     */
    public function putObject($Object)
    {
        $idx = count($this->_references);
        $idx = "0x" . dechex($idx);
        $this->_Instances->attach($Object, $idx);
        $this->_references[$idx] = $Object;

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
     * @param string $idx
     * @return mixed
     */
    public function tryGetObjectRepresentation($idx)
    {
        if ( isset($this->_serialized_objects_dict[$idx]) ) {
            return $this->_serialized_objects_dict[$idx];
        }
        return null;
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
        if ( $this->_is_greedy ) {
            $Result = $Result->withPathReferences($this->_path_references);
        }
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
        if ( !$this->hasObject($Object) ) {
            return null;
        }
        return $this->_Instances->offsetGet($Object);
    }

    /**
     * @param SerializationResult $Res
     * @return mixed
     */
    public function extractSerialized(SerializationResult $Res)
    {
        $mRawRoot = $Res->getRawRoot();
        if ( !$this->_is_greedy ) {
            return $mRawRoot;
        }
        $idx = PjFreeze::tryExtractReference($mRawRoot);
        if ( !$idx ) {
            return $mRawRoot;
        }
        return $this->extractSerializedByIdx($idx, $mRawRoot);
//        $mSerialized = $this->tryGetObjectRepresentation($idx);
//        if ( null === $mSerialized ) {
//            return $mRawRoot;
//        }
//        return $mSerialized;
    }

    /**
     * @param string $ref
     * @param null|mixed $mDefault
     * @return mixed|null
     */
    public function extractSerializedByIdx($ref, $mDefault = null)
    {
        if ( !$ref ) {
            return $mDefault;
        }
        $mSerialized = $this->tryGetObjectRepresentation($ref);
        if ( null === $mSerialized ) {
            return $mDefault;
        }
        return $mSerialized;
    }

    /**
     * @param $Object
     * @param array $partial
     */
    public function attachToFill($Object, array $partial)
    {
        $this->_ToFill->attach($Object, $partial);
    }

    /**
     * @return \SplObjectStorage
     */
    public function getToFill()
    {
        return $this->_ToFill;
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
            "versions" => (object)[],
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
        if ( isset($this->_meta->versions->$class) ) {
            if ( $this->_meta->versions->$class !== $version ) {
                throw new EVersionMismatch($this->_meta->versions->$class, $version);
            }
            return $this;
        }
        $this->_meta->versions->$class = $version;
        return $this;
    }
}