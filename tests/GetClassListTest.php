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
        $classList = $reader->getClasslists(null, null);
        $expected = [
            '/' => [
                'App\\' => [
                    0 => [
                        'relativePath' => '',
                        'relativePathname' => 'a.php',
                        'fileName' => 'a.php',
                        'currentNamespace' => 'App',
                        'absFilePath' => "{$p}{$d}app{$d}a.php",
                        'class' => 'a',
                        'type' => T_CLASS,
                    ],
                    1 => [
                        'relativePath' => '',
                        'relativePathname' => 'b.php',
                        'fileName' => 'b.php',
                        'currentNamespace' => 'App\g',
                        'absFilePath' => "{$p}{$d}app{$d}b.php",
                        'class' => 'b',
                        'type' => T_TRAIT,
                    ],
                    2 => [
                        'relativePath' => '',
                        'relativePathname' => 'c.php',
                        'fileName' => 'c.php',
                        'currentNamespace' => 'App',
                        'absFilePath' => "{$p}{$d}app{$d}c.php",
                        'class' => 'C',
                        'type' => T_INTERFACE,
                    ],
                    3 => [
                        'relativePath' => 'x',
                        'relativePathname' => 'x'.$d.'e.php',
                        'fileName' => 'e.php',
                        'currentNamespace' => '',
                        'absFilePath' => "{$p}{$d}app{$d}x{$d}e.php",
                        'class' => 'e',
                        'type' => T_CLASS,
                    ],
                    4 => [
                        'relativePath' => 'z'.$d.'app',
                        'fileName' => 'a.php',
                        'relativePathname' => 'z'.$d.'app'.$d.'a.php',
                        'currentNamespace' => '',
                        'absFilePath' => "{$p}{$d}app{$d}z{$d}app{$d}a.php",
                        'class' => 'a',
                        'type' => T_CLASS,
                    ],
                ],
                'Database\\Seeders\\' => [],
            ],
        ];

        $this->assertEquals($expected, $classList);

        $errors = $reader->getErrorsLists($classList, function () {
            return '';
        });

        $this->assertEquals([
            '/' => [
                0 => [
                    'type' => 'namespace',
                    'correctNamespace' => 'App',
                    'relativePath' => "app{$d}b.php",
                    'relativePathname' => 'b.php',
                    'fileName' => 'b.php',
                    'currentNamespace' => 'App\g',
                    'absFilePath' => "{$p}{$d}app{$d}b.php",
                    'class' => 'b',
                ],
                1 => [
                    'type' => 'filename',
                    'relativePath' => "app{$d}c.php",
                    'relativePathname' => 'c.php',
                    'fileName' => 'c.php',
                    'currentNamespace' => 'App',
                    'absFilePath' => "{$p}{$d}app{$d}c.php",
                    'class' => 'C',
                ],
                2 => [
                    'type' => 'namespace',
                    'relativePath' => "app{$d}x{$d}e.php",
                    'relativePathname' => "x{$d}e.php",
                    'fileName' => 'e.php',
                    'currentNamespace' => '',
                    'absFilePath' => "{$p}{$d}app{$d}x{$d}e.php",
                    'class' => 'e',
                    'correctNamespace' => 'Test',
                ],
                3 => [
                    'type' => 'namespace',
                    'relativePath' => "app{$d}z{$d}app{$d}a.php",
                    'relativePathname' => "z{$d}app{$d}a.php",
                    'fileName' => 'a.php',
                    'currentNamespace' => '',
                    'absFilePath' => "{$p}{$d}app{$d}z{$d}app{$d}a.php",
                    'class' => 'a',
                    'correctNamespace' => 'App\z\app',
                ]
            ],
        ], $errors);
    }
}
