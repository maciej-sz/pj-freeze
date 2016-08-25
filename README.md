# pj-freeze
PHP to JSON Serializer/Deserializer

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![No dependencies][ico-no-deps]][link-packagist]
[![MIT License][ico-license]][link-license]

## Features
* Serialization to JSON
* Unserialization from JSON
* Circular-reference handling
* [Corresponding JavaScript library][link-jp-freeze] is available to read serialized data on the browser side

## Usage
This package can be used to serialize and unserialize data objects that contain circular references.

#### Example: basic round-trip
```php
use MaciejSz\PjFreeze\PjFreeze;

$Freeze = new PjFreeze();

$data = ["foo", "bar", "baz"];

$SerializationResult = $Freeze->serialize($data);
$serializedObj = $SerializationResult->jsonSerialize();

$unserialized = $Freeze->unserialize($serializedObj);
assert($data == $unserialized);
```
#### Example: persisting round-trip
```php
use MaciejSz\PjFreeze\PjFreeze;

$Freeze = new PjFreeze();

$data = ["foo", "bar", "baz"];
$serializedObj = $Freeze->serialize($data)->jsonSerialize();
$serialized_str = json_encode($serializedObj);

file_put_contents("/tmp/data.json", $serialized_str);
// ...
$contents_str = file_get_contents("/tmp/data.json");
$unserialized = $Freeze->unserializeJson($contents_str);
assert($data == $unserialized);
```

#### Example: circular reference
```php
use MaciejSz\PjFreeze\PjFreeze;

$Freeze = new PjFreeze();

$std = new \stdClass();
$std->data = $std; // circular reference

$serializedObj = $Freeze->serialize($std)->jsonSerialize();
$unserialized = $Freeze->unserialize($serializedObj);
assert($unserialized === $unserialized->data);
```

## Installation

Via composer:
```
composer require maciej-sz/pj-freeze
```

[ico-version]:https://img.shields.io/packagist/v/maciej-sz/pj-freeze.svg?style=plastic
[ico-travis]:https://img.shields.io/travis/maciej-sz/pj-freeze/master.svg?style=plastic
[ico-no-deps]:https://img.shields.io/badge/dependencies-none-brightgreen.svg?style=plastic
[ico-license]:https://img.shields.io/badge/license-MIT-blue.svg?style=plastic

[link-packagist]:https://packagist.org/packages/maciej-sz/pj-freeze
[link-travis]:https://travis-ci.org/maciej-sz/pj-freeze
[link-license]:https://github.com/maciej-sz/pj-freeze/blob/master/LICENSE
[link-jp-freeze]:https://github.com/maciej-sz/jp-freeze
