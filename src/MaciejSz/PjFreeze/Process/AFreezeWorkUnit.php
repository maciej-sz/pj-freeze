<?php
namespace MaciejSz\PjFreeze\Process;

abstract class AFreezeWorkUnit
{
    /**
     * @var bool
     */
    protected $_is_greedy = false;

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
}