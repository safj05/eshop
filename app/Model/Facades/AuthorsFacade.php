<?php

namespace App\Model\Facades;

use App\Model\Entities\Author;
use App\Model\Repositories\AuthorRepository;
use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * class AuthorsFacade
 * @package App\Model\Facades
 */
class AuthorsFacade {
    /** @var AuthorRepository $authorRepository */
    private  $authorRepository;

    /**
     * ziskani autora podle Id
     * @param int $id
     * @return Author
     * @throws \Exception
     */
    public function getAuthor(int $id): Author{
        return $this->authorRepository->find($id);
    }

    /**
     * autori podle jmena
     * @param string $name
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function getAuthorByName(string $name, int $offset=null, int $limit=null): array{
        return $this->authorRepository->getByNameLike($name, $offset, $limit);
    }

    /**
     * spocitat autory podle jmena
     * @param string $name
     * @return int
     */
    public function countAuthorsByName(string $name){
        return $this->authorRepository->countByNameLike($name);
    }

    /**
     * najit autory
     * @param array|null $params
     * @param int|null $offset
     * @param int|null $limit
     * @return Author[]
     */
    public function findAuthors(array $params=null, int $offset=null, int $limit=null): array{
        return $this->authorRepository->findAllBy($params, $offset, $limit);
    }

    /**
     * spocitat autory
     * @param array|null $params
     * @return int
     */
    public function countAuthors(array $params=null): int{
        return $this->authorRepository->findCountBy($params);
    }

    /**
     * ulozeni autora
     * @param Author $author
     * @return bool - true, pokud byly v DB provedeny nějaké změny
     */
    public function saveAuthor(Author &$author):bool{
        //search string je bez mezer a malymi pismeny v db je naindexovany
        $author->authorSearchString = Strings::lower(str_replace(" ", "", $author->name));

        return (bool)$this->authorRepository->persist($author);
    }

    /**
     * Metoda pro smazání autora
     * @param Author $author
     * @return bool
     */
    public function deleteAuthor(Author $author):bool {
        try{
            return (bool)$this->authorRepository->delete($author);
        }catch (\Exception $e){
            return false;
        }
    }

    public function __construct(AuthorRepository $authorRepository) {
        $this->authorRepository = $authorRepository;
    }
}