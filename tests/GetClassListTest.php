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
            'absFilePath' => "{$p}{$d}app{$d}a.php",
            'relativePath' => "app{$d}a.php",
            'relativePathname' => 'a.php',
            'fileName' => 'a.php',
            'currentNamespace' => 'App',
            'class' => 'a',
            'type' => 'class',
        ], $classList['/']['App\\'][0]->toArray());

        $this->assertEquals([
            'absFilePath' => "{$p}{$d}app{$d}b.php",
            'relativePath' => "app{$d}b.php",
            'relativePathname' => 'b.php',
            'fileName' => 'b.php',
            'currentNamespace' => 'App\g',
            'class' => 'b',
            'type' => 'trait',
        ], $classList['/']['App\\'][1]->toArray());

        $this->assertEquals([
            'absFilePath' => "{$p}{$d}app{$d}c.php",
            'relativePath' => "app{$d}c.php",
            'relativePathname' => 'c.php',
            'fileName' => 'c.php',
            'currentNamespace' => 'App',
            'class' => 'C',
            'type' => 'interface',
        ], $classList['/']['App\\'][2]->toArray());

        $this->assertEquals([
            'absFilePath' => "{$p}{$d}app{$d}x{$d}e.php",
            'relativePath' => "app{$d}x{$d}e.php",
            'relativePathname' => 'x'.$d.'e.php',
            'fileName' => 'e.php',
            'currentNamespace' => '',
            'class' => 'e',
            'type' => 'class',
        ], $classList['/']['App\\'][3]->toArray());

        $this->assertEquals([
            'absFilePath' => "{$p}{$d}app{$d}x{$d}enum.php",
            'relativePath' => "app{$d}x{$d}enum.php",
            'relativePathname' => 'x'.$d.'enum.php',
            'fileName' => 'enum.php',
            'currentNamespace' => 'App\x',
            'class' => 'myEnum',
            'type' => 'enum',
        ], $classList['/']['App\\'][4]->toArray());

        $this->assertEquals([
            'absFilePath' => "{$p}{$d}app{$d}z{$d}app{$d}a.php",
            'relativePath' => "app{$d}z{$d}app{$d}a.php",
            'relativePathname' => 'z'.$d.'app'.$d.'a.php',
            'fileName' => 'a.php',
            'currentNamespace' => '',
            'class' => 'a',
            'type' => 'class',
        ], $classList['/']['App\\'][5]->toArray());

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
            'absFilePath' => "{$p}{$d}app{$d}b.php",
            'relativePath' => "app{$d}b.php",
            'relativePathname' => 'b.php',
            'fileName' => 'b.php',
            'currentNamespace' => 'App\g',
            'class' => 'b',
            'type' => 'trait',
        ], $errors1->entity->toArray());

        /**
         * @var $errors1 \ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError
         */
        $errors1 = $errors['/'][1];
        $this->assertEquals([
            'absFilePath' => "{$p}{$d}app{$d}c.php",
            'relativePath' => "app{$d}c.php",
            'relativePathname' => 'c.php',
            'fileName' => 'c.php',
            'currentNamespace' => 'App',
            'class' => 'C',
            'type' => 'interface',
        ], $errors1->entity->toArray());

        $this->assertEquals('filename', $errors1->errorType());
        $this->assertEquals('C', $errors1->filename);

        /**
         * @var $errors1 \ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError
         */
        $errors1 = $errors['/'][2];
        $this->assertEquals([
            'absFilePath' => "{$p}{$d}app{$d}x{$d}e.php",
            'relativePath' => "app{$d}x{$d}e.php",
            'relativePathname' => "x{$d}e.php",
            'fileName' => 'e.php',
            'currentNamespace' => '',
            'class' => 'e',
            'type' => 'class',
        ], $errors1->entity->toArray());

        $this->assertEquals('namespace', $errors1->errorType());
        $this->assertEquals('Test', $errors1->getShortest());

        /**
         * @var $errors1 \ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError
         */
        $errors1 = $errors['/'][4];
        $this->assertEquals([
            'absFilePath' => "{$p}{$d}app{$d}z{$d}app{$d}a.php",
            'relativePath' => "app{$d}z{$d}app{$d}a.php",
            'relativePathname' => "z{$d}app{$d}a.php",
            'fileName' => 'a.php',
            'currentNamespace' => '',
            'class' => 'a',
            'type' => 'class',
        ], $errors1->entity->toArray());

        $this->assertEquals('namespace', $errors1->errorType());
        $this->assertEquals('App\z\app', $errors1->getShortest());

        /**
         * @var $errors1 \ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError
         */
        $errors1 = $errors['/'][3];
        $this->assertEquals([
            'absFilePath' => 'E:\__coding__\my_github_repos\composer-json\tests\Stubs\a3\app\x\enum.php',
            'relativePath' => 'app\x\enum.php',
            'relativePathname' => 'x\enum.php',
            'fileName' => 'enum.php',
            'currentNamespace' => 'App\x',
            'class' => 'myEnum',
            'type' => 'enum',
        ], $errors1->entity->toArray());

        $this->assertEquals('filename', $errors1->errorType());
        $this->assertEquals('myEnum', $errors1->filename);
    }
}
