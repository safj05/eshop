<?php

namespace App\AdminModule\Components\ProductEditForm;

use App\Model\Entities\Product;
use App\Model\Facades\AuthorsFacade;
use App\Model\Facades\CategoriesFacade;
use App\Model\Facades\ProductsFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;
use Tracy\Debugger;

/**
 * Class ProductEditForm
 * @package App\AdminModule\Components\ProductEditForm
 *
 * @method onFinished(string $message = '')
 * @method onFailed(string $message = '')
 * @method onCancel()
 */
class ProductEditForm extends Form{

  use SmartObject;

  /** @var callable[] $onFinished */
  public $onFinished = [];
  /** @var callable[] $onFailed */
  public $onFailed = [];
  /** @var callable[] $onCancel */
  public $onCancel = [];
  /** @var CategoriesFacade */
  private $categoriesFacade;
  /** @var ProductsFacade $productsFacade */
  private $productsFacade;
  /** @var AuthorsFacade $authorsFacade */
    private $authorsFacade;

  /**
   * TagEditForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   * @param ProductsFacade $productsFacade
   * @noinspection PhpOptionalBeforeRequiredParametersInspection
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, CategoriesFacade $categoriesFacade, ProductsFacade $productsFacade, AuthorsFacade $authorsFacade){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->categoriesFacade=$categoriesFacade;
    $this->productsFacade=$productsFacade;
    $this->authorsFacade = $authorsFacade;
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    $productId=$this->addHidden('productId');
    $this->addText('title','Název produktu')
      ->setRequired('Musíte zadat název produktu')
      ->setMaxLength(100);

    $this->addText('url','URL produktu')
      ->setMaxLength(100)
      ->addFilter(function(string $url){
        return Nette\Utils\Strings::webalize($url);
      })
      ->addRule(function(Nette\Forms\Controls\TextInput $input)use($productId){
        try{
          $existingProduct = $this->productsFacade->getProductByUrl($input->value);
          return $existingProduct->productId==$productId->value;
        }catch (\Exception $e){
          return true;
        }
      },'Zvolená URL je již obsazena jiným produktem');

    #region kategorie
    $categories=$this->categoriesFacade->findCategories();
    $categoriesArr=[];
    foreach ($categories as $category){
      $categoriesArr[$category->categoryId]=$category->title;
    }
    $this->addSelect('categoryId','Kategorie',$categoriesArr)
      ->setPrompt('--vyberte kategorii--')
      ->setRequired(false);
    #endregion kategorie

      #region autor
      $authors = $this->authorsFacade->findAuthors();
      $authorsArr=[];
      foreach ($authors as $author) {
          $authorsArr[$author->authorId] = $author->name;
      }
      $this->addSelect('authorId', 'Autor', $authorsArr)
          ->setPrompt('--vyberte autora--')
          ->setRequired('Kniha musi mit autora');
      #endregion autor

    $this->addTextArea('description', 'Popis produktu')
      ->setRequired('Zadejte popis produktu.');

    $this->addText('price', 'Cena')
      ->setHtmlType('number')
      ->addRule(Form::NUMERIC)
      ->setRequired('Musíte zadat cenu produktu');//tady by mohly být další kontroly pro min, max atp.

    $this->addInteger('available', 'počet na skladě')
      ->setDefaultValue(0);

      $this->addInteger('year', 'rok vydání')
          ->setRequired(false);

    #region obrázek
    $photoUpload=$this->addUpload('photo','Fotka produktu');
    //pokud není zadané ID produktu, je nahrání fotky povinné
    $photoUpload //vyžadování nahrání souboru, pokud není známé productId
      ->addConditionOn($productId, Form::EQUAL, '')
        ->setRequired('Pro uložení nového produktu je nutné nahrát jeho fotku.');

    $photoUpload //limit pro velikost nahrávaného souboru
      ->addRule(Form::MAX_FILE_SIZE, 'Nahraný soubor je příliš velký', 1000000);

    $photoUpload //kontrola typu nahraného souboru, pokud je nahraný
      ->addCondition(Form::FILLED)
        ->addRule(function(Nette\Forms\Controls\UploadControl $photoUpload){
          $uploadedFile = $photoUpload->value;
          if ($uploadedFile instanceof Nette\Http\FileUpload){
            $extension=strtolower($uploadedFile->getImageFileExtension());
            return in_array($extension,['jpg','jpeg','png']);
          }
          return false;
        },'Je nutné nahrát obrázek ve formátu JPEG či PNG.');
    #endregion obrázek

    $this->addSubmit('ok','uložit')
      ->onClick[]=function(SubmitButton $button){
        $values=$this->getValues('array');

        if (!empty($values['productId'])){
          try{
            $product=$this->productsFacade->getProduct($values['productId']);
          }catch (\Exception $e){
            $this->onFailed('Požadovaný produkt nebyl nalezen.');
            return;
          }
        }else{
          $product=new Product();
        }
        $product->assign($values,['title','url','description','available']);
        $product->price=floatval($values['price']);
        $product->year=intval($values['year']);

        //vlozeni autora a kategorie
        if(!empty($values['categoryId'])){
            $product->category = $this->categoriesFacade->getCategory($values['categoryId']);
        }

        $product->authors[0] = $this->authorsFacade->getAuthor($values['authorId']);

        $this->productsFacade->saveProduct($product);
        $this->setValues(['productId'=>$product->productId]);

        //uložení fotky
        if (($values['photo'] instanceof Nette\Http\FileUpload) && ($values['photo']->isOk())){
          try{
            $this->productsFacade->saveProductPhoto($values['photo'], $product);
          }catch (\Exception $e){
              Debugger::barDump($e);
            $this->onFailed('Produkt byl uložen, ale nepodařilo se uložit jeho fotku.');
          }
        }

        $this->onFinished('Produkt byl uložen.');
      };
    $this->addSubmit('storno','zrušit')
      ->setValidationScope([$productId])
      ->onClick[]=function(SubmitButton $button){
        $this->onCancel();
      };
  }

  /**
   * Metoda pro nastavení výchozích hodnot formuláře
   * @param Product|array|object $values
   * @param bool $erase = false
   * @return $this
   */
  public function setDefaults($values, bool $erase = false):self {
    if ($values instanceof Product){
      $values = [
        'productId'=>$values->productId,
        'categoryId'=>$values->category?$values->category->categoryId:null,
        'title'=>$values->title,
        'url'=>$values->url,
        'description'=>$values->description,
        'price'=>$values->price,
          'authorId'=>$values->authors[0]->author->authorId,
          'available'=>$values->available,
        'year'=>$values->year
      ];
    }
    parent::setDefaults($values, $erase);
    return $this;
  }

}