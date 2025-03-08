<?php

namespace ImanGhafoori\ComposerJson\Tests;

use ImanGhafoori\ComposerJson\ComposerJson;
use PHPUnit\Framework\TestCase;

class GetClassListTest extends TestCase
{
    public function test_getClasslists()
    {
        $d = DIRECTORY_SEPARATOR;
        $reader = ComposerJson::make($p = __DIR__.$d.'Stubs'.$d.'a3');
        $classList = $reader->getClasslists(null, null)->getAllLists();
        $this->assertEquals([
            'relativePath' => 'app\a.php',
            'relativePathname' => 'a.php',
            'fileName' => 'a.php',
            'currentNamespace' => 'App',
            'absFilePath' => "{$p}{$d}app{$d}a.php",
            'class' => 'a',
            'type' => 'class',
        ], $classList['/']['App\\'][0]->toArray());

        $this->assertEquals([
            'relativePath' => 'app\b.php',
            'relativePathname' => 'b.php',
            'fileName' => 'b.php',
            'currentNamespace' => 'App\g',
            'absFilePath' => "{$p}{$d}app{$d}b.php",
            'class' => 'b',
            'type' => 'trait',
        ], $classList['/']['App\\'][1]->toArray());

        $this->assertEquals([
            'relativePath' => 'app\c.php',
            'relativePathname' => 'c.php',
            'fileName' => 'c.php',
            'currentNamespace' => 'App',
            'absFilePath' => "{$p}{$d}app{$d}c.php",
            'class' => 'C',
            'type' => 'interface',
        ], $classList['/']['App\\'][2]->toArray());

        $this->assertEquals([
            'relativePath' => 'app\x\e.php',
            'relativePathname' => 'x'.$d.'e.php',
            'fileName' => 'e.php',
            'currentNamespace' => '',
            'absFilePath' => "{$p}{$d}app{$d}x{$d}e.php",
            'class' => 'e',
            'type' => 'class',
        ], $classList['/']['App\\'][3]->toArray());

        $this->assertEquals([
            'relativePath' => 'app\z\app\a.php',
            'fileName' => 'a.php',
            'relativePathname' => 'z'.$d.'app'.$d.'a.php',
            'currentNamespace' => '',
            'absFilePath' => "{$p}{$d}app{$d}z{$d}app{$d}a.php",
            'class' => 'a',
            'type' => 'class',
        ], $classList['/']['App\\'][4]->toArray());

        $this->assertEquals([], $classList['/']['Database\\Seeders\\']);

        $errors = $reader->getErrorsLists($classList, function () {
            return '';
        });

        $this->assertArrayHasKey('/', $errors);

        /**
         * @var $errors1 \ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError
         */
        $errors1 = $errors['/'][0];
        $this->assertEquals([
            'type' => 'trait',
            'relativePath' => "app{$d}b.php",
            'relativePathname' => 'b.php',
            'fileName' => 'b.php',
            'currentNamespace' => 'App\g',
            'absFilePath' => "{$p}{$d}app{$d}b.php",
            'class' => 'b',
        ], $errors1->entity->toArray());

        /**
         * @var $errors1 \ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError
         */
        $errors1 = $errors['/'][1];
        $this->assertEquals([
            'type' => 'interface',
            'relativePath' => "app{$d}c.php",
            'relativePathname' => 'c.php',
            'fileName' => 'c.php',
            'currentNamespace' => 'App',
            'absFilePath' => "{$p}{$d}app{$d}c.php",
            'class' => 'C',
        ], $errors1->entity->toArray());

        $this->assertEquals('filename', $errors1->errorType());
        $this->assertEquals('C', $errors1->filename);

        /**
         * @var $errors1 \ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError
         */
        $errors1 = $errors['/'][2];
        $this->assertEquals([
            'type' => 'class',
            'relativePath' => "app{$d}x{$d}e.php",
            'relativePathname' => "x{$d}e.php",
            'fileName' => 'e.php',
            'currentNamespace' => '',
            'absFilePath' => "{$p}{$d}app{$d}x{$d}e.php",
            'class' => 'e',
        ], $errors1->entity->toArray());

        $this->assertEquals('namespace', $errors1->errorType());
        $this->assertEquals('Test', $errors1->getShortest());

        /**
         * @var $errors1 \ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError
         */
        $errors1 = $errors['/'][3];
        $this->assertEquals([
            'type' => 'class',
            'relativePath' => "app{$d}z{$d}app{$d}a.php",
            'relativePathname' => "z{$d}app{$d}a.php",
            'fileName' => 'a.php',
            'currentNamespace' => '',
            'absFilePath' => "{$p}{$d}app{$d}z{$d}app{$d}a.php",
            'class' => 'a',
        ], $errors1->entity->toArray());

        $this->assertEquals('namespace', $errors1->errorType());
        $this->assertEquals('App\z\app', $errors1->getShortest());
    }
}
