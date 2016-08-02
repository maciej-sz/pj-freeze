<?php
namespace MaciejSz\PjFreeze\Exc;

class EInvalidVersion extends \Exception
{
    public function __construct($class, \Exception $previous)
    {
        parent::__construct("Invalid version in class \"{$class}\"", null, $previous);
    }

}