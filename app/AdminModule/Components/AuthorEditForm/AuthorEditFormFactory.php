<?php

namespace App\AdminModule\Components\AuthorEditForm;

/**
 * Interface AuthorEditFormFactory
 * @package App\AdminModule\Components\AuthorEditForm
 */
interface AuthorEditFormFactory{

    public function create():AuthorEditForm;

}