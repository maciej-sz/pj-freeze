<?php
namespace MaciejSz\PjFreeze;

use MaciejSz\PjFreeze\Process\PjFreezeProcess;
use MaciejSz\PjFreeze\Process\SerializationResult;

class PjFreeze
{
    const REFERENCE_PREFIX = "##ref##";

    /**
     * @var bool
     */
    private $_greedy = false;

    /**
     * @param null|bool $greedy
     */
    public function __construct($greedy = null)
    {
        if ( null === $greedy ) {
            $greedy = false;
        }
        $this->_greedy = (bool)$greedy;
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
        $Process = new PjFreezeProcess();
        return $this->_doSerialize($mValue, $Process);
    }

    /**
     * @param object $Object
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serializeObject($Object)
    {
        $Process = new PjFreezeProcess();
        return $this->_doSerializeObject($Object, $Process);
    }

    /**
     * @param array|\Traversable $mTraversable
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serializeTraversable($mTraversable)
    {
        $Process = new PjFreezeProcess();
        return $this->_doSerializeTraversable($mTraversable, $Process);
    }

    /**
     * @param mixed $mValue
     * @param PjFreezeProcess $Process
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    protected function _doSerialize($mValue, PjFreezeProcess $Process)
    {
        if ( null === $mValue || is_scalar($mValue) ) {
            return $Process->makeResult($mValue, $mValue);
        }
        else if ( $mValue instanceof \JsonSerializable ) {
            return $Process->makeResult($mValue, $mValue->jsonSerialize());
        }
        else if ( $mValue instanceof \stdClass ) {
            return $this->_doSerializeTraversable($mValue, $Process);
        }
        else if ( is_object($mValue) ) {
            return $this->_doSerializeObject($mValue, $Process);
        }
        else if ( is_array($mValue) ) {
            return $this->_doSerializeTraversable($mValue, $Process);
        }
        throw Exc\EDontKnowHowToSerialize::factory($mValue);
    }

    /**
     * @param object $Object
     * @param PjFreezeProcess $Process
     * @return \stdClass|string
     */
    protected function _doSerializeObject($Object, PjFreezeProcess $Process)
    {
        if ( $Process->hasObject($Object) ) {
            $idx = $Process->tryGetObjectReference($Object);
            $key = self::buildKey($idx);
            return $Process->makeResult($key, $key);
        }
        $item = new \stdClass();
        $idx = $Process->putObject($Object);
        $Reflection = new \ReflectionObject($Object);
        $properties = $Reflection->getProperties();
        foreach ( $properties as $Property ) {
            if ( $Property->isStatic() ) {
                continue;
            }
            $name = $Property->getName();
            $Property->setAccessible(true);
            $mValue = $Property->getValue($Object);
            $Res = $this->_doSerialize($mValue, $Process);
            $item->$name = $this->_extractSerialized($Res, $Process);
        }
        $Process->putObjectRepresentation($idx, $item);
        return $Process->makeResult($Object, $item);
    }

    /**
     * @param array|\Traversable $mTraversable
     * @param PjFreezeProcess $Process
     * @return array
     */
    protected function _doSerializeTraversable($mTraversable, PjFreezeProcess $Process)
    {
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
                $Res = $this->_doSerialize($mValue, $Process);
                $items[$sub_key] = $this->_extractSerialized($Res, $Process);
            }
        }
        if ( null !== $idx ) {
            $Process->putObjectRepresentation($idx, (object)$items);
        }
        return $Process->makeResult($mTraversable, $items);
    }

    /**
     * @param mixed $mRoot
     * @param PjFreezeProcess $Process
     * @return mixed
     */
    protected function _processRoot($mRoot, PjFreezeProcess $Process)
    {
        if ( !is_object($mRoot) ) {
            return $mRoot;
        }
        return $Process->tryGetObjectReference($mRoot);
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
     * @param PjFreezeProcess $Process
     * @return mixed
     */
    protected function _extractSerialized(
        SerializationResult $Res,
        PjFreezeProcess $Process
    )
    {
        $mRawRoot = $Res->getRawRoot();
        if ( !$this->_greedy ) {
            return $mRawRoot;
        }
        $ref = self::tryExtractReference($mRawRoot);
        if ( !$ref ) {
            return $mRawRoot;
        }
        $mSerialized = $Process->tryGetObjectRepresentation($ref);
        if ( null === $mSerialized ) {
            return $mRawRoot;
        }
        return $mSerialized;
    }
}