<?php

namespace ImanGhafoori\ComposerJson\NamespaceErrors;

use ImanGhafoori\ComposerJson\Entity;
use ImanGhafoori\ComposerJson\NamespaceCalculator;
use JetBrains\PhpStorm\ExpectedValues;

class NamespaceError
{
    /**
     * @var string[]
     */
    private $correctNamespaces;

    /**
     * @var \ImanGhafoori\ComposerJson\Entity
     */
    public $entity;

    public function __construct($correctNamespaces, Entity $entity)
    {
        $this->correctNamespaces = $correctNamespaces;
        $this->entity = $entity;
    }

    public function getShortest()
    {
        return NamespaceCalculator::findShortest($this->correctNamespaces);
    }

    #[ExpectedValues(['filename', 'namespace'])]
    public function errorType()
    {
        return 'namespace';
    }
}
