<?php
namespace MaciejSz\PjFreeze;

use MaciejSz\PjFreeze\Process\PjFreezeProcess;
use MaciejSz\PjFreeze\Process\PjFreezeStatus;
use MaciejSz\PjFreeze\Process\SerializationResult;

class PjFreeze
{
    const REFERENCE_PREFIX = "##ref##";

    /**
     * @var bool
     */
    private $_is_greedy = false;

    /**
     * @param null|bool $greedy
     */
    public function __construct($greedy = null)
    {
        if ( null === $greedy ) {
            $greedy = false;
        }
        $this->_is_greedy = (bool)$greedy;
    }

    /**
     * @return PjFreeze
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * @return PjFreeze
     */
    public static function greedy()
    {
        return new self(true);
    }

    /**
     * @param string $class
     * @return int
     */
    public static function getSerialVersionUid($class)
    {
        $version = 1;
        $name = $class . "::SERIAL_VERSION_UID";
        if ( defined($name) ) {
            $version = constant($name);
        }
        return $version;
    }

    /**
     * @param mixed $mValue
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serialize($mValue)
    {
        $Process = new PjFreezeProcess($this->_is_greedy);
        $Status = new PjFreezeStatus($Process);
        return $this->_doSerialize($mValue, $Status);
    }

    /**
     * @param object $Object
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serializeObject($Object)
    {
        $Process = new PjFreezeProcess($this->_is_greedy);
        $Status = new PjFreezeStatus($Process);
        return $this->_doSerializeObject($Object, $Status);
    }

    /**
     * @param array|\Traversable $mTraversable
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serializeTraversable($mTraversable)
    {
        $Process = new PjFreezeProcess($this->_is_greedy);
        $Status = new PjFreezeStatus($Process);
        return $this->_doSerializeTraversable($mTraversable, $Status);
    }

    /**
     * @param mixed $mValue
     * @param PjFreezeStatus $Status
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    protected function _doSerialize($mValue, PjFreezeStatus $Status)
    {
        if ( null === $mValue || is_scalar($mValue) ) {
            return $Status->getProcess()->makeResult($mValue, $mValue);
        }
        else if ( $mValue instanceof \JsonSerializable ) {
            return $Status->getProcess()->makeResult($mValue, $mValue->jsonSerialize());
        }
        else if ( $mValue instanceof \stdClass ) {
            return $this->_doSerializeTraversable($mValue, $Status);
        }
        else if ( is_object($mValue) ) {
            return $this->_doSerializeObject($mValue, $Status);
        }
        else if ( is_array($mValue) ) {
            return $this->_doSerializeTraversable($mValue, $Status);
        }
        throw Exc\EDontKnowHowToSerialize::factory($mValue);
    }

    /**
     * @param object $Object
     * @param PjFreezeStatus $Status
     * @return \stdClass|string
     */
    protected function _doSerializeObject($Object, PjFreezeStatus $Status)
    {
        $Process = $Status->getProcess();
        if ( $Process->hasObject($Object) ) {
            $idx = $Process->tryGetObjectReference($Object);
            $key = self::buildKey($idx);
            return $Process->makeResult($key, $key);
        }
        $idx = $Process->putObject($Object);
        $item = (object)$this->_serializeReflectionProperties(
            new \ReflectionObject($Object),
            $Object,
            $Status
        );
        $Process->putObjectRepresentation($idx, $item);
        return $Process->makeResult($Object, $item);
    }

    /**
     * @param \ReflectionClass $Reflection
     * @param $Object
     * @param PjFreezeStatus $Status
     * @return array
     */
    protected function _serializeReflectionProperties(
        \ReflectionClass $Reflection,
        $Object,
        PjFreezeStatus $Status
    )
    {
        $items = [];
        $properties = $Reflection->getProperties();
        foreach ( $properties as $Property ) {
            if ( $Property->isStatic() ) {
                continue;
            }
            $name = $Property->getName();
            $Property->setAccessible(true);
            $mValue = $Property->getValue($Object);
            $idx = $Status->getProcess()->tryGetObjectReference($Object);
            $SubStatus = $Status->appendPathProperty($name, $idx);
            $Res = $this->_doSerialize($mValue, $SubStatus);
            $items[$name] = $this->_extractSerialized($Res, $SubStatus);
        }
        $ParentReflection = $Reflection->getParentClass();
        if ($ParentReflection) {
            $parent_items = $this->_serializeReflectionProperties(
                $ParentReflection,
                $Object,
                $Status
            );
            $items = array_merge($parent_items, $items);
        }
        return $items;
    }

    /**
     * @param array|\Traversable $mTraversable
     * @param PjFreezeStatus $Status
     * @return array
     */
    protected function _doSerializeTraversable($mTraversable, PjFreezeStatus $Status)
    {
        $Process = $Status->getProcess();
        $idx = null;
        if ( is_object($mTraversable) ) {
            if ( $Process->hasObject($mTraversable) ) {
                $idx = $Process->tryGetObjectReference($mTraversable);
                $key = self::buildKey($idx);
                return $Process->makeResult($key, $key);
            }
            else {
                $idx = $Process->putObject($mTraversable);
            }
        }
        $items = [];
        foreach ( $mTraversable as $sub_key => $mValue ) {
            $sub_idx = $Process->tryGetObjectReference($mValue);
            if ( $sub_idx ) {
                $items[$sub_key] = self::buildKey($sub_idx);
            }
            else {
                $SubStatus = $Status->appendPathTraversable($sub_key, $sub_idx);
                $Res = $this->_doSerialize($mValue, $SubStatus);
                $items[$sub_key] = $this->_extractSerialized($Res, $SubStatus);
            }
        }
        if ( null !== $idx ) {
            $Process->putObjectRepresentation($idx, (object)$items);
        }
        return $Process->makeResult($mTraversable, $items);
    }

    /**
     * @param string $idx
     * @return string
     */
    public static function buildKey($idx)
    {
        return self::REFERENCE_PREFIX . $idx;
    }

    /**
     * @param mixed $mValue
     * @return null
     */
    public static function tryExtractReference($mValue)
    {
        if ( !is_string($mValue) ) {
            return null;
        }
        $prefix = self::REFERENCE_PREFIX;
        $prefix_length = strlen($prefix);
        if ( $prefix != substr($mValue, 0, $prefix_length) ) {
            return null;
        }
        return substr($mValue, $prefix_length);
    }

    /**
     * @param SerializationResult $Res
     * @param PjFreezeStatus $Status
     * @return mixed
     */
    protected function _extractSerialized(
        SerializationResult $Res,
        PjFreezeStatus $Status
    )
    {
        $mRawRoot = $Res->getRawRoot();
        if ( !$this->_is_greedy ) {
            return $mRawRoot;
        }
        $ref = self::tryExtractReference($mRawRoot);
        if ( !$ref ) {
            return $mRawRoot;
        }
        $mSerialized = $Status->getProcess()->tryGetObjectRepresentation($ref);
        if ( null === $mSerialized ) {
            return $mRawRoot;
        }
        return $mSerialized;
    }
}