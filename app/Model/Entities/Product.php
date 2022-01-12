<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Product
 * @package App\Model\Entities
 * @property int $productId
 * @property string $title
 * @property string $url
 * @property string $description
 * @property float $price
 * @property string $photoExtension = ''
 * @property integer $available = 0
 * @property string $productSearchString
 * @property Category|null $category m:hasOne
 * @property AuthorProduct[] $authors m:belongsToMany = []
 * @property int|null $year
 */
class Product extends Entity implements \Nette\Security\Resource {

    /**
     * @inheritDoc
     */
    function getResourceId(): string
    {
        return 'Product';
    }
}