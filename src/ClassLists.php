<?php

namespace ImanGhafoori\ComposerJson;

class ClassLists
{
    /**
     * @var array<string, array<string, array<int, \ImanGhafoori\ComposerJson\Entity>>>
     */
    private $classLists = [];

    public function addList($composerFilePath, $namespace, array $classList)
    {
        $this->classLists[$composerFilePath][$namespace] = $classList;
    }

    public function getAllLists()
    {
        return $this->classLists;
    }

    public function foreachList($callback)
    {
        foreach ($this->classLists as $composerFilePath => $namespaceList) {
            foreach ($namespaceList as $namespace => $classList) {
                foreach ($classList as $entity) {
                    $callback($composerFilePath, $namespace, $entity);
                }
            }
        }
    }
}
