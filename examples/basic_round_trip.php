<?php
use MaciejSz\PjFreeze\PjFreeze;

require_once __DIR__ . "/../../vendor/autoload.php";

$Freeze = new PjFreeze();

$data = ["foo", "bar", "baz"];

$SerializationResult = $Freeze->serialize($data);
$serializedObj = $SerializationResult->jsonSerialize();

$unserialized = $Freeze->unserialize($serializedObj);
assert($data == $unserialized);