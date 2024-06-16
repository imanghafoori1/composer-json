<?php

namespace ImanGhafoori\ComposerJson\Tests;

use ImanGhafoori\ComposerJson\GetClassProperties;
use PHPUnit\Framework\TestCase;

class GetClassPropertiesTest extends TestCase
{
    public function test_can_detect_method_visibility()
    {
        [$namespace, $name, $type, $parent, $interfaces] = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/HomeController.stub');

        $this->assertEquals("App\Http\Controllers", $namespace);
        $this->assertEquals('HomeController', $name);
        $this->assertEquals(T_CLASS, $type);
        $this->assertEquals('Controller', $parent);
        $this->assertEquals('Countable|MyInterface', $interfaces);
    }

    public function test_can_detect_multi_extend()
    {
        [$namespace, $name, $type, $parent, $interfaces] = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/multi_extend_interface.stub');

        $this->assertEquals("App\Models\Support", $namespace);
        $this->assertEquals('BaseInterface', $name);
        $this->assertEquals(T_INTERFACE, $type);
        $this->assertEquals('AnotherBaseInterface|Arrayable|Jsonable|JsonSerializable', $parent);
    }

    public function test_can_detect_multi_extend_1()
    {
        [$namespace, $name, $type, $parent, $interfaces] = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/interface_sample.stub');

        $this->assertEquals('', $namespace);
        $this->assertEquals('interface_sample', $name);
        $this->assertEquals(T_INTERFACE, $type);
        $this->assertEquals('IncompleteTest', $parent);
    }

    public function test_can_detect_simple_classes()
    {
        [$namespace, $name, $type, $parent, $interfaces] = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/I_am_simple.stub');

        $this->assertEquals('', $namespace);
        $this->assertEquals('I_am_simple', $name);
        $this->assertEquals(T_CLASS, $type);
        $this->assertEquals('', $parent);
    }

    public function test_non_php_file()
    {
        [$namespace, $name, $type, $parent, $interfaces] = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/non_php_opening_tag.stub');

        $this->assertEquals(null, $namespace);
        $this->assertEquals(null, $name);
        $this->assertEquals(null, $type);
        $this->assertEquals(null, $parent);
        $this->assertEquals(null, $interfaces);
    }
}
