<?php
/**
 * @filesource  \AdminModule\GalleryPresenter.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \AdminModule
 * @version     1.0.0 
 */



// Namespace definition
namespace AdminModule;


/**
 * @abstract  \AdminModule\GalleryPresenter 
 * Presenter for discussion.
 */
class GalleryPresenter extends \AdminModule\BaseSecuredPresenter
{
	
	/** @var int */
	const DEFAULT_DIR = '/galleries/gallery_'; 
	const DEFAULT_THUMB_WIDTH = 188;
	const DEFAULT_THUMB_HEIGHT = 128;
	
	private $gallery = NULL;
	private $galleryItems = NULL;
	
	

	/**
	 * Render default action.
	 */
	public function renderDefault()
	{
		$this->template->galleries = $this->context->createGalleries()->order('added DESC');	
	} // renderDefault()
	
	
	
	/** actionAdd ********************************************************** */
	
	
	
	/**
	 * Action for create new gallery. 
	 */
	public function actionAdd()
	{
	} // actionAdd()
	
	
	
	/**
	 * Creates form for add new gallery into DB
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentGalleryAdd()
	{
		// New instance of nette form
		$form = new \Nette\Application\UI\Form;
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table class="form"';
 		
 		$form->addGroup();
		
		// Add item name (text in menu)
		$form->addText('name', '*Název galerie:')
			 ->setRequired('Vyplňte prosím název galerie!')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Název galerie je příliš dlouhý, max. délka je %d znaků!', 64);
			 
		
		// Add item title (text in title attribute)
		$form->addTextArea('description', 'Popis galerie:', 80, 4)
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Titulek položky je příliš dlouhý, max. délka je %d znaků!', 256);
		
		// Allow comments
		$form->addRadioList('allow_comments', 'Povolit komentáře?', array(true => 'Ano', false => 'Ne'))
			 ->setDefaultValue(true);
		
		
		// Add two buttons
		$form->addGroup();
		$form->addSubmit('save', 'Vytvořit');
		$form->addSubmit('cancel', 'Zrušit')
		     ->setValidationScope(false);
		
		// Add callback
		$form->onSuccess[] = $this->galleryAddSubmitted;
		
		
		// Return new form instance
		return $form;
	} // createComponentMenuItemAdd()
	
	
	
	/**
	 * Callback method for adding new gallery.
	 * @param \Nette\Application\UI\Form $form
	 */
	public function galleryAddSubmitted(\Nette\Application\UI\Form $form)
	{
		// Check submit button
		if($form['cancel']->isSubmittedBy()) {
			$this->redirect(':Admin:Gallery:default');
			exit();
		}
		
		
		// Get values
		$values = $form->values;
		$values['added'] = new \DateTime();
		$values['active'] = false;
		
		
		// Insert into DB and create new folder
		$inserted = $this->context->createGalleries()->insert($values);
		$dirName = $this->getDirName() . $inserted->id;
		if(!is_dir($dirName)) {
			mkdir($dirName);
		}
		
		
		// Set flash message and redirect
		$this->flashMessage("Nová galerie '".$inserted->id." - ".$values['name']."' byla úspěšně vytvořena!", 'info');
		$this->redirect(':Admin:Gallery:edit', array('id' => $inserted->id));
		
		
		return;
	} // galleryAddSubmitted()
	
	
	
	/** actionEdit *********************************************************** */
	
	
	
	/**
	 * Action for edit gallery. 
	 */
	public function actionEdit($id = NULL)
	{
		$this->gallery = $this->context->createGalleries()->get($id);
		if(!$this->gallery) {
			$this->flashMessage('Nebylo zadáno platné ID galerie. Nelze editovat', 'error');
			$this->redirect(':Admin:Gallery:', array('id' => NULL));
			exit();
		}
		
		$this->galleryItems = $this->context->createGalleryItems()->where(array('gallery_id' => $this->gallery->id))->order('sort_order ASC');
	} // actionEdit()
	
	
	
	/**
	 * Render edit gallery action.
	 */
	public function renderEdit()
	{
		$this->template->gallery = $this->gallery;
		$this->template->galleryItems = $this->galleryItems;
		$this->template->wwwDir = $this->context->parameters['wwwDir'];
	} // renderEdit()
	
	
	
	/**
	 * Creates form for edit gallery.
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentGalleryEdit()
	{
		// New instance of nette form
		$form = new \Nette\Application\UI\Form;
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table class="form"';
 		
 		$form->addGroup();
		
		// Add item name (text in menu)
		$form->addText('name', '*Název galerie:')
			 ->setRequired('Vyplňte prosím název galerie!')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Název galerie je příliš dlouhý, max. délka je %d znaků!', 64)
			 ->setDefaultValue($this->gallery->name);
			 
		
		// Add item title (text in title attribute)
		$form->addTextArea('description', 'Popis galerie:', 80, 4)
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Popise galerie je příliš dlouhý, max. délka je %d znaků!', 256)
			 ->setDefaultValue($this->gallery->description);
		
		// Allow comments
		$form->addRadioList('allow_comments', 'Povolit komentáře?', array(true => 'Ano', false => 'Ne'))
			 ->setDefaultValue($this->gallery['allow_comments']);
		
		
		// Add two buttons
		$form->addGroup();
		$form->addSubmit('save', 'Uložit změny');
		$form->addSubmit('cancel', 'Zrušit')
		     ->setValidationScope(false);
		
		// Add callback
		$form->onSuccess[] = $this->galleryEditSubmitted;
		
		
		// Return new form instance
		return $form;
	} // createComponentGalleryEdit()
	
	
	
	/**
	 * Callback method for edit gallery.
	 * @param \Nette\Application\UI\Form $form
	 */
	public function galleryEditSubmitted(\Nette\Application\UI\Form $form)
	{
		// Check submit button
		if($form['cancel']->isSubmittedBy()) {
			$this->redirect(':Admin:Gallery:default', array('id' => NULL));
			exit();
		}
		
		
		// Get values
		$values = $form->values;
		
		
		// Insert into DB and create new folder
		$this->gallery->update($values);
		$dirName = $this->getDirName() . $this->gallery->id;
		if(!is_dir($dirName)) {
			mkdir($dirName);
		}
		
		
		// Set flash message and redirect
		$this->flashMessage("Galerie '".$this->gallery->id." - ".$values['name']."' byla úspěšně upravena!", 'info');
		$this->redirect('this');
		
		
		return;
	} // galleryEditSubmitted()
	
	
	
	/**
	 * Creates form for add new gallery item.
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentGalleryItemAdd()
	{
		// New instance of nette form
		$form = new \Nette\Application\UI\Form;
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table class="form"';
 		
 		$form->addGroup();
		
		// Add item name (text in menu)
		$form->addText('title', '*Název položky:')
			 ->setRequired('Vyplňte prosím název položky!')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Název položky je příliš dlouhý, max. délka je %d znaků!', 64);
			 
		
		// Add item title (text in title attribute)
		$form->addTextArea('description', 'Popisek položky:', 80, 1)
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Popisek položky je příliš dlouhý, max. délka je %d znaků!', 256);
		
		// File
		$form->addUpload('gallery_item', '*Obrázek:')
			 ->setRequired('Musíte vložit soubor s obrázkem!');		
		
		
		// Add two buttons
		$form->addGroup();
		$form->addSubmit('save', 'Přidat');
		$form->addSubmit('cancel', 'Zrušit')
		     ->setValidationScope(false);
		
		
		// Add callback
		$form->onSuccess[] = $this->galleryItemAddSubmitted;
		
		
		// Return new form instance
		return $form;
	} // createComponentGalleryEdit()
	
	
	
	/**
	 * Callback method for adding new gallery item.
	 * @param \Nette\Application\UI\Form $form
	 */
	public function galleryItemAddSubmitted(\Nette\Application\UI\Form $form)
	{
		// Check submit button
		if($form['cancel']->isSubmittedBy()) {
			$this->redirect(':Admin:Gallery:default', array('id' => NULL));
			exit();
		}
		
		// Get values
		$values = $form->values;
		$fileName = $values['gallery_item']->name;
		
		// Check if new image is OK
		if(!$values['gallery_item']->isOk()) {
			$this->flashMessage("Při nahrávání obrázku '".$fileName."' na server došlo k chybě (#".$values['gallery_item']->error.")!", 'error');
			$this->redirect('this');
		}
		
		// Insert into DB and create new folder
		$dirName = $this->getDirName() . $this->gallery->id;
		if(!is_dir($dirName)) {
			mkdir($dirName);
		}
		
		$index = 0;
		if(file_exists($dirName.'/'.$fileName)) {
			dump($fileName);	
			foreach(\Nette\Utils\Finder::findFiles('*')->in($dirName.'/')->exclude('.', '..', '.svn') as $fn => $foo) {
				$fn = substr($fn, strlen($dirName)+1);
				if(\Nette\Utils\Strings::match($fn, '#^[0-9]*_'.$fileName.'#')) {
					$index++;
				}
			}
			
			$fileName = $index.'_'.$fileName; 
			$this->flashMessage("Obrázek byl přejmenován na '".$fileName."' z důvodu konfliktu jmen!", 'warning');
		}

		$values['file_name'] = $fileName;
		$values['active'] = false;
		$values['added'] = new \DateTime;
		$values['gallery_id'] = $this->gallery->id;
		$values['sort_order'] = $this->galleryItems->count();
		
		$values['gallery_item']->move($dirName.'/'.$fileName);
		$thumb = $values['gallery_item']->toImage();
		$thumb->resize(self::DEFAULT_THUMB_WIDTH, self::DEFAULT_THUMB_HEIGHT, \Nette\Image::STRETCH);
		$thumb->save($dirName.'/thumb_'.$fileName);
		
		
		
		// Set flash message and redirect
		unset($values['gallery_item']);
		$this->context->createGalleryItems()->insert($values);
		$this->flashMessage("Nový obrázek '".$fileName."' byl úspěšně nahrán na server!", 'info');
		$this->redirect('this');
		
		
		return;
	} // galleryItemAddSubmitted()
	
	
	
	/**
	 * Creates form for add new gallery item.
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentGalleryItemEdit()
	{
		// Create multiplier
		$self = $this;
		$items = $this->context->createGalleryItems();
		$control = new \Nette\Application\UI\Multiplier(function($name) use ($self, $items) {
			// New instance of nette form
			$form = new \Nette\Application\UI\Form;
			$item = $items->get($name);
			
			// Add item name (text in menu)
			$form->addText('title', '')
				 ->setRequired('Vyplňte prosím název položky!')
				 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Název položky je příliš dlouhý, max. délka je %d znaků!', 64)
				 ->setDefaultValue($item->title);
				 
			
			// Add item title (text in title attribute)
			$form->addTextArea('description', '', 80, 1)
				 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Popisek položky je příliš dlouhý, max. délka je %d znaků!', 256)
				 ->setDefaultValue($item->description);
			
			// File
			$form->addHidden('id', $name);		
			
			// Add button
			$form->addSubmit('save', 'Uložit');
			
			// Add callback
			$form->onSuccess[] = $self->galleryItemEditSubmitted;
						
			// Return new form instance
			return $form;
		});
		
		return $control;
	} // createComponentGalleryEdit()
	
	
	
	protected function createComponentGalleryComments()
	{
		$comments = $this->context->createComments()->getGalleryComments($this->id)->order('added ASC');
		$control = new \Components\CommentsControl\CommentsControl($comments, NULL, $this->gallery);
		$control->setShowBar(true);
		return $control;
	}
	
	
	
	/**
	 * Callback method for adding new gallery item.
	 * @param \Nette\Application\UI\Form $form
	 */
	public function galleryItemEditSubmitted(\Nette\Application\UI\Form $form)
	{
		// Get values
		$values = $form->values;
		$id = $values['id'];
		
		// Set flash message and redirect
		unset($values['id']);
		$item = $this->galleryItems->get($id);
		if($item) {
			$item->update($values);
			$this->flashMessage("Popisek obrázku '#".$id." - ".$item['full_name']."' byl úspěšně upraven!", 'info');
		} else {
			$this->flashMessage("Obrázek s id '#".$id."' neexistuje!", 'error');
		}
		
		if($this->isAjax()) {
			$this->invalidateControl('galleryItem-'.$id);
		}
		else {
			$this->redirect('this');
		}
		
		
		return;
	} // galleryItemAddSubmitted()
	
	
	
	/** signals ************************************************************** */
	
	
	public function handleActivate($galleryId, $flag = false)
	{
		$gall = $this->context->createGalleries()->get($galleryId);
		if($gall) {
			$gall->update(array('active' => $flag));
			if($flag) {
				$this->flashMessage("Galerie '#".$galleryId." - ".$gall['name']."' byla úspěšně aktivována!", 'info');
			} else {
				$this->flashMessage("Galerie '#".$galleryId." - ".$gall['name']."' byl úspěšně deaktivována!", 'info');
			}
		} else {
			$this->flashMessage("Galerie s id '#".$galleryId."' neexistuje!", 'error');
		}
		
		if($this->isAjax()) {
			$this->invalidateControl('galleries');
		}
		else {
			$this->redirect('this');
		}
		
		return;
	} // handleActivate()
	
	
	
	public function handleDelete($galleryId)
	{
		$gall = $this->context->createGalleries()->get($galleryId);
		if($gall) {
			$dirName = $this->getDirName() . $galleryId;			
			if(is_dir($dirName  . '/')) {
				//rmdir($dirName  . '/');
				$gall->delete();
			}
			
			$this->flashMessage("Galerie '#".$galleryId." - ".$gall['name']."' byla úspěšně smazána!", 'info');			
		} else {
			$this->flashMessage("Galerie s id '#".$galleryId."' neexistuje!", 'error');
		}
		
		
		if($this->isAjax()) {
			$this->invalidateControl('galleries');
		}
		else {
			$this->redirect('this');
		}
		
		return;
	} // handleDeleteItem()
	
	
	
	public function handleActivateItem($itemId, $flag = false)
	{
		$item = $this->galleryItems->get($itemId);
		if($item) {
			$item->update(array('active' => $flag));
			if($flag) {
				$this->flashMessage("Popisek obrázku '#".$itemId." - ".$item['file_name']."' byl úspěšně aktivován!", 'info');
			} else {
				$this->flashMessage("Popisek obrázku '#".$itemId." - ".$item['file_name']."' byl úspěšně deaktivován!", 'info');
			}
		} else {
			$this->flashMessage("Obrázek s id '#".$itemId."' neexistuje!", 'error');
		}
		
		if($this->isAjax()) {
			$this->invalidateControl('galleryItems');
		}
		else {
			$this->redirect('this#snippet--galleryItem-'.$itemId);
		}
		
		return;
	} // handleActivateItem()
	
	
	
	public function handleMoveItem($itemId, $up = false)
	{
		$item = $this->galleryItems->get($itemId);
		if($item) {
			if($up) {
				$ancestor = $this->galleryItems->where('sort_order', ($item['sort_order']-1))->fetch();
				if($ancestor) {
					$item->update(array('sort_order' => $item['sort_order']-1));
					$ancestor->update(array('sort_order' => $item['sort_order']));
					$this->flashMessage("Obrázek '#".$itemId." - ".$item['file_name']."' byl úspěšně posunut nahoru!", 'info');
				}
				else {
					$this->flashMessage("Při posunu obrázku '#".$itemId." - ".$item['file_name']."' došlo k chybě!", 'info');
				}
			}
			else {
				$successor = $this->galleryItems->where('sort_order', ($item['sort_order']+1))->fetch();
				if($successor) {
					$item->update(array('sort_order' => $item['sort_order']+1));
					$successor->update(array('sort_order' => $item['sort_order']));
					$this->flashMessage("Obrázek '#".$itemId." - ".$item['file_name']."' byl úspěšně posunut dolů!", 'info');
				}
				else {
					$this->flashMessage("Při posunu obrázku '#".$itemId." - ".$item['file_name']."' došlo k chybě!", 'info');
				}
			}
		} else {
			$this->flashMessage("Obrázek s id '#".$itemId."' neexistuje!", 'error');
		}
		
		
		if($this->isAjax()) {
			$this->invalidateControl('galleryItems');
		}
		else {
			$this->redirect('this#snippet--galleryItem-'.$itemId);
		}
		
		return;
	} // handleMoveItem()
	
	
	
	public function handleDeleteItem($itemId)
	{
		$item = $this->galleryItems->get($itemId);
		if($item) {
			$fileName = $item['file_name'];
			$dirName = $this->getDirName() . $this->gallery->id;
			
			$items = $this->context->createGalleryItems()->where('gallery_id', $this->gallery->id)->where('sort_order > ?', $item['sort_order']);
			while($i = $items->fetch()) {
				$i->update(array('sort_order' => $i['sort_order']-1));
			}
			
			
			$item->delete();
			
			if(is_file($dirName  . '/' . $fileName)) {
				unlink($dirName  . '/' . $fileName);
			}
			if(is_file($dirName  . '/thumb_' . $fileName)) {
				unlink($dirName  . '/thumb_' . $fileName);
			}
			
			$this->flashMessage("Obrázek '#".$itemId." - ".$fileName."' byl úspěšně smazán!", 'info');			
		} else {
			$this->flashMessage("Obrázek s id '#".$itemId."' neexistuje!", 'error');
		}
		
		
		if($this->isAjax()) {
			$this->invalidateControl('galleryItems');
		}
		else {
			$this->redirect('this#snippet--galleryItems');
		}
		
		return;
	} // handleDeleteItem()
	
	
	
	/** others methods ******************************************************* */
	
	
	
	/**
	 * returns basic directory path to Galleries dir in resources 
	 */
	public function getDirName() 
	{
		return $this->context->parameters['resourcesDir'].self::DEFAULT_DIR;
	} // getDirName()
	
	

} // class \AdminModule\GalleryPresenter 
