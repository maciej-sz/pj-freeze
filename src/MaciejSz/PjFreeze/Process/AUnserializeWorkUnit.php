<?php
namespace MaciejSz\PjFreeze\Process;

abstract class AUnserializeWorkUnit
{
    /**
     * @var PjUnserializer
     */
    protected $_Unserializer;

    /**
     * @var PjUnserializeProcess
     */
    protected $_Process;

    /**
     * @param PjUnserializer $Unserializer
     * @param PjUnserializeProcess $Process
     */
    public function __construct(PjUnserializer $Unserializer, PjUnserializeProcess $Process)
    {
        $this->_Unserializer = $Unserializer;
        $this->_Process = $Process;
    }


}