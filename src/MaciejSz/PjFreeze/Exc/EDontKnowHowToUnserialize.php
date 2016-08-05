<?php
namespace MaciejSz\PjFreeze\Exc;

class EDontKnowHowToUnserialize extends \RuntimeException
{
    public function __construct($reference)
    {
        parent::__construct("Don't know how to unserialize. Reference: {$reference}");
    }

}