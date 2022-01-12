<?php

namespace App\AdminModule\Components\AuthorEditForm;

use App\Model\Entities\Author;
use Nette\Forms\Controls\SubmitButton;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use App\Model\Facades\AuthorsFacade;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;
use Nette;
use Tracy\Debugger;

/**
 * Class AuthorEditForm
 * @package App\AdminModule\Components\AuthorEditForm
 *
 * @method onFinished(string $message = '')
 * @method onFailed(string $message = '')
 * @method onCancel()
 */
class AuthorEditForm extends Form {
    use SmartObject;


    /** @var callable[] $onFinished */
    public $onFinished = [];
    /** @var callable[] $onFailed */
    public $onFailed = [];
    /** @var callable[] $onCancel */
    public $onCancel = [];
    /** @var AuthorsFacade $authorsFacade */
    public $authorsFacade;

    /**
     * TagEditForm constructor.
     * @param Nette\ComponentModel\IContainer|null $parent
     * @param string|null $name
     * @param AuthorsFacade $authorsFacade
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, AuthorsFacade $authorsFacade) {
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
        $this->authorsFacade = $authorsFacade;
        $this->createSubComponents();

    }

    private function createSubComponents(){
        $authorId = $this->addHidden('authorId');
        $this->addText('name', 'Jméno autora')
            ->setRequired('musite zadat jmeno autora');
        $this->addTextArea('description', 'O autorovi')
            ->setRequired(false);
        $this->addCheckbox('czech', 'Cesky autor')
            ->setRequired(false)
            ->setDefaultValue(false);
        $this->addSubmit('ok', 'ulozit')
            ->onClick[]=function(SubmitButton $button){

            $values=$this->getValues('array');

            if (!empty($values['authorId'])){
                try{
                    $author=$this->authorsFacade->getAuthor($values['authorId']);
                }catch (\Exception $e){
                    $this->onFailed('Požadovany autor nebyl nalezen.');
                    return;
                }
            }else{
                $author=new Author();
            }

            $author->assign($values,['name','description','czech']);
            $this->authorsFacade->saveAuthor($author);

            $this->setValues(['authorId'=>$author->authorId]);
            $this->onFinished('Autor byl uložen.');
        };
        $this->addSubmit('storno','zrušit')
            ->setValidationScope([$authorId])
            ->onClick[]=function(SubmitButton $button){
            $this->onCancel();
        };
    }

    /**
     * Metoda pro nastavení výchozích hodnot formuláře
     * @param Author|array|object $values
     * @param bool $erase = false
     * @return $this
     */
    public function setDefaults($values, bool $erase = false):self {
        if ($values instanceof Author){
            $values = [
                'authorId'=>$values->authorId,
                'name'=>$values->name,
                'description'=>$values->description,
                'czech'=>$values->czech
            ];
        }
        parent::setDefaults($values, $erase);
        return $this;
    }
}