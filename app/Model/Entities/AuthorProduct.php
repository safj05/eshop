<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class AuthorProduct
 * @package App\Model\Entities
 * @property int $authorProductId
 * @property Author $author m:hasOne
 * @property Product $product m:hasOne
 * @property int $authorOrder
 */
class AuthorProduct extends Entity implements \Nette\Security\Resource {
    /**
     * @inheritDoc
     */
    function getResourceId(): string
    {
        return 'AuthorProduct';
    }
}