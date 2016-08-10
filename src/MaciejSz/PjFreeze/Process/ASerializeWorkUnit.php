<?php
namespace MaciejSz\PjFreeze\Process;

abstract class ASerializeWorkUnit
{
    /**
     * @var PjSerializer
     */
    protected $_Serializer;

    /**
     * @var PjSerializeStatus
     */
    protected $_Status;

    /**
     * @param PjSerializer $Serializer
     * @param PjSerializeStatus $Status
     */
    public function __construct(PjSerializer $Serializer, PjSerializeStatus $Status)
    {
        $this->_Serializer = $Serializer;
        $this->_Status = $Status;
    }
}