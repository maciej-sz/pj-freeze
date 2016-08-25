<?php
use MaciejSz\PjFreeze\PjFreeze;

require_once __DIR__ . "/../../vendor/autoload.php";

$Freeze = new PjFreeze();

$data = new \stdClass();
$data->recursion = $data; // circular reference

// using only json_encode():
$raw_encoded = json_encode($data);
echo json_last_error_msg(); // Recursion detected

// using PjFreeze:
$serializedObj = $Freeze->serialize($data)->jsonSerialize();
$jp_freeze_encoded = json_encode($serializedObj);
echo json_last_error_msg();

$unserializedObj = json_decode($jp_freeze_encoded);
$unserialized = $Freeze->unserialize($unserializedObj);
assert($unserialized->recursion === $unserialized);
