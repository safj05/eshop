<?php

namespace App\Model\Facades;

use App\Model\Entities\Product;
use App\Model\Repositories\ProductRepository;
use Nette\Utils\Strings;
use Nette\Http\FileUpload;

/**
 * class ProductsFacade
 * @package App\Model\Facades
 */
class ProductsFacade {
    /** @var ProductRepository $productRepository */
    private $productRepository;

    /**
     * Ziskani jednoho produktu
     * @param int $id
     * @return Product
     * @throws \Exception
     */
    public function getProduct(int $id): Product{
        return $this->productRepository->find($id);
    }

    /**
     * podle url
     * @param string $url
     * @return Product
     * @throws \Exception
     */
    public function getProductByUrl(string $url):Product{
        return $this->productRepository->findBy(['url'=>$url]);
    }

    /**
     * vyhledani produktů
     * @param array|null $params = null
     * @param int|null $offset = null
     * @param int|null $limit = null
     * @return array
     */
    public function findProducts(array $params=null, int $offset=null, int $limit=null):array{
        return $this->productRepository->findAllBy($params,$offset,$limit);
    }

    /**
     * spocitat produkty
     * @param array|null $params
     * @return int
     */
    public function findProductCount(array $params=null):int{
        return $this->productRepository->findCountBy($params);
    }

    /**
     * ulozeni produktu
     * @param Product $product
     * @throws \Exception
     */
    public function saveProduct(Product &$product){
        if(empty($product->url)){
            $baseUrl = Strings::webalize($product->title);
        }else{
            $baseUrl = $product->url;
        }

        $url = $baseUrl;
        $urlNumber = 1;
        $productId = $product->productId ?? null;

        try {
            while ($existingProduct = $this->getProductByUrl($baseUrl)){
                if($existingProduct->productId == $productId){
                    //id se shoduje, stejny produkt
                    $product->url = $url;
                    break;
                }
                //zacinam od 1 takze nechci inkrementovat predem ale az potom
                $url = $baseUrl.$urlNumber++;
            }
        }catch (\Exception $e){
            //nenalezeno, url je volna
        }

        $product->url = $url;

        //search string je bez mezer a malymi pismeny v db je naindexovany
        $product->productSearchString = Strings::lower(str_replace(" ", "", $product->title));

        $this->productRepository->persist($product);
    }

    /**
     * Metoda pro uložení fotky produktu
     * @param FileUpload $fileUpload
     * @param Product $product
     * @throws \Exception
     */
    public function saveProductPhoto(FileUpload $fileUpload, Product &$product) {
        if ($fileUpload->isOk() && $fileUpload->isImage()){
            $fileExtension=strtolower($fileUpload->getImageFileExtension());
            $fileUpload->move(__DIR__.'/../../../www/img/products/'.$product->productId.'.'.$fileExtension);
            $product->photoExtension=$fileExtension;
            $this->saveProduct($product);
        }
    }

    public function __construct(ProductRepository $productRepository){
        $this->productRepository = $productRepository;
    }
}