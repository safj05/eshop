<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Author
 * @package App\Model\Entities
 * @property int $authorId
 * @property string $name
 * @property bool $czech
 * @property string|null $description
 * @property string $authorSearchString
 * @property AuthorProduct[] $products m:belongsToMany = []
 */
class Author extends Entity implements \Nette\Security\Resource {
    /**
     * @inheritDoc
     */
    function getResourceId(): string
    {
        return 'Author';
    }
}