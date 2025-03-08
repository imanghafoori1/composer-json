<?php

namespace ImanGhafoori\ComposerJson\Tests;

use ImanGhafoori\ComposerJson\GetClassProperties;
use PHPUnit\Framework\TestCase;

class GetClassPropertiesTest extends TestCase
{
    public function test_can_detect_method_visibility()
    {
        $definition = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/HomeController.stub');

        $this->assertEquals("App\Http\Controllers", $definition->getNamespace());
        $this->assertEquals('HomeController', $definition->getEntityName());
        $this->assertEquals('class', $definition->getType());
        $this->assertEquals('Controller', $definition->getParent());
        $this->assertEquals(['Countable', 'MyInterface'], $definition->getInterfaces());
    }

    public function test_can_detect_multi_extend()
    {
        $definition = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/multi_extend_interface.stub');

        $this->assertEquals("App\Models\Support", $definition->getNamespace());
        $this->assertEquals('BaseInterface', $definition->getEntityName());
        $this->assertEquals('interface', $definition->getType());
        $this->assertEquals('AnotherBaseInterface|Arrayable|Jsonable|JsonSerializable', $definition->getParent());
        $this->assertEquals([], $definition->getInterfaces());
    }

    public function test_can_detect_multi_extend_1()
    {
        $definition = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/interface_sample.stub');

        $this->assertEquals('', $definition->getNamespace());
        $this->assertEquals('interface_sample', $definition->getEntityName());
        $this->assertEquals('interface', $definition->getType());
        $this->assertEquals('IncompleteTest', $definition->getParent());
        $this->assertEquals([], $definition->getInterfaces());
    }

    public function test_can_detect_simple_classes()
    {
        $definition = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/I_am_simple.stub');

        $this->assertEquals('', $definition->getNamespace());
        $this->assertEquals('I_am_simple', $definition->getEntityName());
        $this->assertEquals('class', $definition->getType());
        $this->assertEquals('', $definition->getParent());
        $this->assertEquals([], $definition->getInterfaces());
    }

    public function test_non_php_file()
    {
        $definition = GetClassProperties::fromFilePath(__DIR__.'/Stubs/stubs/non_php_opening_tag.stub');

        $this->assertEquals(null, $definition->getNamespace());
        $this->assertEquals(null, $definition->getEntityName());
        $this->assertEquals(null, $definition->getType());
        $this->assertEquals(null, $definition->getParent());
        $this->assertEquals([], $definition->getInterfaces());
    }
}
