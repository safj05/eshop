<?php

namespace App\Model\Repositories;

use Nette\Utils\Strings;

/**
 * Class AuthorRepository
 * @package App\Model\Repositories
 */
class AuthorRepository extends BaseRepository {

    public function getByNameLike(string $name, $offset = null, $limit = null){
        return $this->createEntities(
            $this->connection->select('*')
                ->from($this->getTable())
                ->where('author_search_string like %?%', Strings::lower(str_replace(" ", "", $name)))
                ->fetchAll($offset, $limit)
        );
    }

    public function countByNameLike(string $name){
        return $this->connection->select('count(*) as pocet')
            ->from($this->getTable())
            ->where('author_search_string like %?%', Strings::lower(str_replace(" ", "", $name)))
            ->fetchSingle();
    }
}