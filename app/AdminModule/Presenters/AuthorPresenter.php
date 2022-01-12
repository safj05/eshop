<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Components\AuthorEditForm\AuthorEditForm;
use App\AdminModule\Components\AuthorEditForm\AuthorEditFormFactory;
use App\Model\Entities\Author;
use App\Model\Facades\AuthorsFacade;
use Tracy\Debugger;

/**
 * Class AuthorPresenter
 * @package App\AdminModule\Presenters
 */
class AuthorPresenter extends BasePresenter {
    /** @var AuthorsFacade $authorsFacade */
    private $authorsFacade;
    /** @var AuthorEditFormFactory $authorEditFormFactory */
    private $authorEditFormFactory;

    /**
     * Akce pro vykreslení seznamu kategorií
     */
    public function renderDefault():void {
        $this->template->authors=$this->authorsFacade->findAuthors(['order'=>'name']);
    }

    /**
     * Akce pro úpravu jedné kategorie
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function renderEdit(int $id):void {
        try{
            $author=$this->authorsFacade->getAuthor($id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovany autor nebyl nalezen.', 'error');
            $this->redirect('default');
        }
        $form=$this->getComponent('authorEditForm');
        $form->setDefaults($author);
        $this->template->category=$author;
    }

    /**
     * Akce pro smazání kategorie
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDelete(int $id):void {
        try{
            $author=$this->authorsFacade->getAuthor($id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaná kategorie nebyla nalezena.', 'error');
            $this->redirect('default');
        }

        if (!$this->user->isAllowed($author,'delete')){
            $this->flashMessage('Tohoto autora není možné smazat.', 'error');
            $this->redirect('default');
        }

        if ($this->authorsFacade->deleteAuthor($author)){
            $this->flashMessage('Autor byl smazán.', 'info');
        }else{
            $this->flashMessage('Tohoto autora není možné smazat.', 'error');
        }

        $this->redirect('default');
    }

    /**
     * Formulář na editaci kategorií
     * @return AuthorEditForm
     */
    public function createComponentAuthorEditForm():AuthorEditForm {
        $form = $this->authorEditFormFactory->create();
        $form->onCancel[]=function(){
            $this->redirect('default');
        };
        $form->onFinished[]=function($message=null){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onFailed[]=function($message=null){
            if (!empty($message)){
                $this->flashMessage($message,'error');
            }
            $this->redirect('default');
        };
        return $form;
    }



    #region injections
    public function injectCategoriesFacade(AuthorsFacade $authorsFacade){
        $this->authorsFacade=$authorsFacade;
    }
    public function injectCategoryEditFormFactory(AuthorEditFormFactory $authorEditFormFactory){
        $this->authorEditFormFactory=$authorEditFormFactory;
    }
    #endregion injections
}