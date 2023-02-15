<?php

namespace ImanGhafoori\ComposerJson;

class NamespaceCalculator
{
    public static function calculateCorrectNamespace($relativeClassPath, $composerPath, $rootNamespace)
    {
        $classPath = \explode(DIRECTORY_SEPARATOR, $relativeClassPath);
        // Removes the filename
        array_pop($classPath);

        $classPath = \implode('\\', $classPath);

        // Ensure back slashes in All Operating Systems.
        $composerPath = \str_replace('/', '\\', $composerPath);

        // replace composer base_path with composer namespace
        /**
         *  "psr-4": {
         *      "App\\": "app/"
         *  }.
         */
        return self::replaceFirst(
            \trim($composerPath, '\\'),
            \trim($rootNamespace, '\\/'),
            $classPath
        );
    }

    public static function checkNamespace($relativePath, $psr4Mapping, $currentNamespace, $class, $fileName)
    {
        $correctNamespaces = self::getCorrectNamespaces($psr4Mapping, $relativePath);

        if (! in_array($currentNamespace, $correctNamespaces)) {
            return [
                'type' => 'namespace',
                'correctNamespace' => self::findShortest($correctNamespaces),
            ];
        } elseif (($class.'.php') !== $fileName) {
            return [
                'type' => 'filename',
            ];
        }
    }

    public static function getNamespaceFromFullClass($class)
    {
        $segments = explode('\\', $class);
        array_pop($segments); // removes the last part

        return trim(implode('\\', $segments), '\\');
    }

    public static function haveSameNamespace($class1, $class2)
    {
        return self::getNamespaceFromFullClass($class1) === self::getNamespaceFromFullClass($class2);
    }

    private static function replaceFirst($search, $replace, $subject)
    {
        if ($search == '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    private static function findShortest($correctNamespaces)
    {
        // finds the shortest namespace
        return array_reduce($correctNamespaces, function ($a, $b) {
            if ($a === null) {
                return $b;
            }

            return strlen($a) < strlen($b) ? $a : $b;
        });
    }

    private static function getCorrectNamespaces($psr4Mapping, $relativePath)
    {
        $correctNamespaces = [];
        foreach ($psr4Mapping as $namespacePrefix => $path) {
            if (substr(str_replace('\\', '/', $relativePath), 0, strlen($path)) === $path) {
                $correctNamespaces[] = self::calculateCorrectNamespace($relativePath, $path, $namespacePrefix);
            }
        }

        return $correctNamespaces;
    }
}
