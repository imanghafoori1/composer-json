<?php

namespace ImanGhafoori\ComposerJson;

class ClassLists
{
    /**
     * @var array<string, array<string, \ImanGhafoori\ComposerJson\Entity[]>>
     */
    private $classLists = [];

    /**
     * @param  string  $composerFilePath
     * @param  string  $namespace
     * @param  \ImanGhafoori\ComposerJson\Entity[]  $classList
     * @return void
     */
    public function addList($composerFilePath, $namespace, array $classList)
    {
        $this->classLists[$composerFilePath][$namespace] = $classList;
    }

    /**
     * @return array<string, array<string, \ImanGhafoori\ComposerJson\Entity[]>>
     */
    public function getAllLists()
    {
        return $this->classLists;
    }

    /**
     * @param  \Closure  $callback
     * @return void
     */
    public function foreachEntity($callback)
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
