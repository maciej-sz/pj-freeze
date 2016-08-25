<?php
use MaciejSz\PjFreeze\PjFreeze;

require_once __DIR__ . "/../../vendor/autoload.php";

$Freeze = new PjFreeze();

$data = ["foo", "bar", "baz"];
$serializedObj = $Freeze->serialize($data)->jsonSerialize();
$serialized_str = json_encode($serializedObj);

file_put_contents("/tmp/data.json", $serialized_str);
// ...
$contents_str = file_get_contents("/tmp/data.json");
$unserialized = $Freeze->unserializeJson($contents_str);
assert($data == $unserialized);