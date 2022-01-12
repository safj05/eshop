<?php

namespace App\Model\Repositories;

/**
 * Class RoleRepository
 * @package App\Model\Repositories
 */
class RoleRepository extends BaseRepository{

    /**
     * @return array
     */
    public function findAllOrdered() {
        return $this->createEntities(
            $this->connection->select('*')
                ->from($this->getTable())
                ->orderBy('id')
                ->fetchAll()
        );
    }
}