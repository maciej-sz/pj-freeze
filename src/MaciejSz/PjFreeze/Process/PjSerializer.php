<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\Exc\EDontKnowHowToSerialize;
use MaciejSz\PjFreeze\PjFreeze;

class PjSerializer
{
    /**
     * @param mixed $mValue
     * @param PjSerializeStatus $Status
     * @throws EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serialize($mValue, PjSerializeStatus $Status)
    {
        if ( null === $mValue || is_scalar($mValue) ) {
            return $Status->getProcess()->makeResult($mValue, $mValue);
        }
        else if ( $mValue instanceof \JsonSerializable ) {
            return $Status->getProcess()->makeResult($mValue, $mValue->jsonSerialize());
        }
        else if ( $mValue instanceof \stdClass ) {
            return $this->serializeTraversable($mValue, $Status);
        }
        else if ( is_object($mValue) ) {
            return $this->serializeObject($mValue, $Status);
        }
        else if ( is_array($mValue) ) {
            return $this->serializeTraversable($mValue, $Status);
        }
        throw EDontKnowHowToSerialize::factory($mValue);
    }

    /**
     * @param object $Object
     * @param PjSerializeStatus $Status
     * @return \stdClass|string
     */
    public function serializeObject($Object, PjSerializeStatus $Status)
    {
        $Process = $Status->getProcess();
        if ( $Process->hasObject($Object) ) {
            $idx = $Process->tryGetObjectReference($Object);
            $key = PjFreeze::buildKey($idx);
            return $Process->makeResult($key, $key);
        }
        $idx = $Process->putObject($Object);
        $item = (object)$this->_serializeReflectionProperties($Object, $Status);
        $Process->putObjectRepresentation($idx, $item);
        return $Process->makeResult($Object, $item);
    }

    /**
     * @param array|\Traversable $mTraversable
     * @param PjSerializeStatus $Status
     * @return array
     */
    public function serializeTraversable($mTraversable, PjSerializeStatus $Status)
    {
        $Process = $Status->getProcess();
        $idx = null;
        if ( is_object($mTraversable) ) {
            if ( $Process->hasObject($mTraversable) ) {
                $idx = $Process->tryGetObjectReference($mTraversable);
                $key = PjFreeze::buildKey($idx);
                return $Process->makeResult($key, $key);
            }
            else {
                $idx = $Process->putObject($mTraversable);
            }
        }

        $WorkUnit = new SerializeTraversable($this, $Status);
        $items = $WorkUnit->serialize($mTraversable);

        if ( null !== $idx ) {
            $Process->putObjectRepresentation($idx, $items);
        }
        return $Process->makeResult($mTraversable, $items);
    }

    /**
     * @param $Object
     * @param PjSerializeStatus $Status
     * @return array
     */
    protected function _serializeReflectionProperties($Object, PjSerializeStatus $Status)
    {
        $SubSerializer = new SerializeReflectionProperties($this, $Status);
        return $SubSerializer->serialize($Object);
    }
}