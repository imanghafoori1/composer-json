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

    public static function findPsr4Errors($basePath, $psr4Mapping, $classLists, ?\Closure $onCheck)
    {
        $errors = [];

        foreach ($classLists as $list) {
            foreach ($list as $class) {
                $onCheck && $onCheck($class);
                $relativePath = \trim(str_replace($basePath, '', $class['absFilePath']), '/\\');
                $error = self::checkNamespace($relativePath, $psr4Mapping, $class['currentNamespace'], $class['class'], $class['fileName']);

                if ($error) {
                    $error['relativePath'] = $relativePath;
                    $error = $error + $class;
                    $errors[] = $error;
                }
            }
        }

        return $errors;
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

    public static function findShortest($correctNamespaces)
    {
        // finds the shortest namespace
        return array_reduce($correctNamespaces, function ($a, $b) {
            return ($a === null || strlen($a) >= strlen($b)) ? $b : $a;
        });
    }

    public static function getCorrectNamespaces($psr4Mapping, $relativePath)
    {
        $correctNamespaces = [];
        $relativePath = str_replace(['\\', '.php'], ['/', ''], $relativePath);
        foreach ($psr4Mapping as $namespacePrefix => $paths) {
            foreach ((array) $paths as $path) {
                if (0 === strpos($relativePath, $path)) {
                    $correctNamespace = substr_replace($relativePath, $namespacePrefix, 0, strlen($path));
                    $correctNamespace = str_replace('/', '\\', $correctNamespace);
                    $correctNamespaces[] = self::getNamespaceFromFullClass($correctNamespace);
                }
            }
        }

        return $correctNamespaces;
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
}
