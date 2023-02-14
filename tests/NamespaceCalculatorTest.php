<?php

namespace ImanGhafoori\ComposerJson\Tests;

use ImanGhafoori\ComposerJson\NamespaceCalculator;
use PHPUnit\Framework\TestCase;

class NamespaceCalculatorTest extends TestCase
{
    /** @test */
    public function calculate_correct_namespace()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = "app{$ds}Hello{$ds}Name.php";
        $r = NamespaceCalculator::calculateCorrectNamespace($path, 'app/', 'App\\');
        $this->assertEquals("App\Hello", $r);

        $r = NamespaceCalculator::calculateCorrectNamespace($path, 'app', 'App\\');
        $this->assertEquals("App\Hello", $r);

        $r = NamespaceCalculator::calculateCorrectNamespace($path, 'app/Hello/', 'Foo\\');
        $this->assertEquals('Foo', $r);

        $r = NamespaceCalculator::calculateCorrectNamespace($path, 'app/Hello', 'Foo\\');
        $this->assertEquals('Foo', $r);

        $path = "app{$ds}Hello{$ds}Hello{$ds}Name.php";
        $r = NamespaceCalculator::calculateCorrectNamespace($path, 'app/Hello', 'Foo\\');
        $this->assertEquals("Foo\Hello", $r);
    }

    /** @test */
    public function check_namespace()
    {
        $fileName = 'Hello.php';
        $class = 'Hello';
        $psr4Mapping = [
            'Models\\' => 'app/Models/',
            'App\\' => 'app/',
        ];
        $ds = DIRECTORY_SEPARATOR;
        $relativePath = 'app'.$ds.'Models'.$ds.'Hello.php';

        $currentNamespace = 'App\Models';
        $result = NamespaceCalculator::checkNamespace($relativePath, $psr4Mapping, $currentNamespace, $class, $fileName);
        $this->assertNull($result);

        $currentNamespace = 'Models';
        $result = NamespaceCalculator::checkNamespace($relativePath, $psr4Mapping, $currentNamespace, $class, $fileName);
        $this->assertNull($result);

        $fileName = 'hello.php';
        $result = NamespaceCalculator::checkNamespace($relativePath, $psr4Mapping, $currentNamespace, $class, $fileName);
        $this->assertEquals(['type' => 'filename'], $result);

        $fileName = 'Hello.php';
        $currentNamespace = 'App\Models\K';
        $result = NamespaceCalculator::checkNamespace($relativePath, $psr4Mapping, $currentNamespace, $class, $fileName);
        $this->assertEquals([
            'type' => 'namespace',
            'correctNamespace' => 'Models'
        ], $result);
    }
}
