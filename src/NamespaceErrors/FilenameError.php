<?php

namespace ImanGhafoori\ComposerJson\NamespaceErrors;

use ImanGhafoori\ComposerJson\Entity;
use JetBrains\PhpStorm\ExpectedValues;

class FilenameError
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var \ImanGhafoori\ComposerJson\Entity
     */
    public $entity;

    public function __construct(Entity $entity)
    {
        $this->filename = $entity->getEntityName().'.php';
        $this->entity = $entity;
    }

    #[ExpectedValues(['filename', 'namespace'])]
    public function errorType()
    {
        return 'filename';
    }

    public function getCorrectFileName()
    {
        return $this->filename;
    }
}
