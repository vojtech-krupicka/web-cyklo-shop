<?php
/**
 * @filesource  \AdminModule\CmsPresenter.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \AdminModule
 * @version     1.0.0 
 */



// Namespace definition
namespace AdminModule;


/**
 * @abstract  \AdminModule\CmsPresenter 
 * Presenter for creating cms pages.
 */
class CmsPresenter extends \AdminModule\BaseSecuredPresenter
{

	
	private $parentItem = NULL;
	private $menuItem = NULL;
	private $page = NULL;
	private $comments = NULL;
	
	
	private $showComments = true;
	private $commentId = NULL;
	private $commentEdit = false;
	
	
	/**
	 * Render default action.
	 */
	public function renderDefault()
	{
		$this->template->homePage = $this->context->createPages()->where('is_homepage', true)->fetch();
		$this->template->menuItems = $this->context->createMenuItems()->where('parent_id', NULL)->order('sort_order ASC');
	} // renderDefault()
	
	
	
	public function handleCreateHomepage()
	{
		$insertedPage = $this->context->createPages()->insert(array('created' => new \DateTime, 'is_homepage' => true));
		$this->flashMessage('Nová uvodní stránka byla úspěšně vytvořena.', 'info');	
		$this->redirect('this');
		
		return;
	} // handleCreateHomepage()
	
	
	
	public function handleActivate($itemId, $flag = false)
	{
		$item = $this->context->createMenuItems()->get($itemId);
		if($item) {
			$item->update(array('active' => $flag));
			if($flag) {
				$this->flashMessage("Položka menu '#".$itemId." - ".$item['name']."' byla úspěšně aktivována!", 'info');
			} else {
				$this->flashMessage("Položka menu '#".$itemId." - ".$item['name']."' byl úspěšně deaktivována!", 'info');
			}
		} else {
			$this->flashMessage("Položka menu s id '#".$itemId."' neexistuje!", 'error');
		}
		
		if($this->isAjax()) {
			$this->invalidateControl('menuItems');
		}
		else {
			$this->redirect('this');
		}
		
		return;
	} // handleActivate()
	
	
	
	public function handleDelete($itemId)
	{
		$item = $this->context->createMenuItems()->get($itemId);
		if($item) {
			$items = $this->context->createMenuItems()->where('parent_id', $item['parent_id'])->where('sort_order > ?', $item['sort_order']);
			while($i = $items->fetch()) {
				$i->update(array('sort_order' => $i['sort_order']-1));
			}
			
			if($item['page_id'] != NULL) {
				$page = $this->context->createPages()->get($item['page_id']);
				$page->delete();
			}
			
			$item->delete();
			$this->flashMessage("Položka menu '#".$itemId." - ".$item['name']."' byla úspěšně smazána!", 'info');			
		} else {
			$this->flashMessage("Položka menu s id '#".$itemId."' neexistuje!", 'error');
		}
		
		
		if($this->isAjax()) {
			$this->invalidateControl('menuItems');
		}
		else {
			$this->redirect('this');
		}
		
		return;
	} // handleDeleteItem()
	
	
	
	public function handleMove($itemId, $up = false)
	{
		$item = $this->context->createMenuItems()->get($itemId);
		if($item) {
			$menuItems = $this->context->createMenuItems()->where('parent_id', $item['parent_id']);
			if($up) {
				$ancestor = $menuItems->where('sort_order', ($item['sort_order']-1))->fetch();
				if($ancestor) {
					$item->update(array('sort_order' => $item['sort_order']-1));
					$ancestor->update(array('sort_order' => $item['sort_order']));
					$this->flashMessage("Položka menu '#".$itemId." - ".$item['name']."' byla úspěšně posunuta nahoru!", 'info');
				}
				else {
					$this->flashMessage("Při posunu položky menu '#".$itemId." - ".$item['name']."' došlo k chybě!", 'info');
				}
			}
			else {
				$successor = $menuItems->where('sort_order', ($item['sort_order']+1))->fetch();
				if($successor) {
					$item->update(array('sort_order' => $item['sort_order']+1));
					$successor->update(array('sort_order' => $item['sort_order']));
					$this->flashMessage("Položka menu '#".$itemId." - ".$item['name']."' byla úspěšně posunuta dolů!", 'info');
				}
				else {
					$this->flashMessage("Při posunu položky menu '#".$itemId." - ".$item['name']."' došlo k chybě!", 'info');
				}
			}
		} else {
			$this->flashMessage("Položka menu s id '#".$itemId."' neexistuje!", 'error');
		}
		
		
		if($this->isAjax()) {
			$this->invalidateControl('menuItems');
		}
		else {
			$this->redirect('this');
		}
		
		return;
	} // handleMove()
	
	
	
	/** actionAdd ********************************************************** */
	
	
	
	/**
	 * Action for create new menu item. If sets ID, try to select item's parent. 
	 */
	public function actionAdd()
	{
		if($this->id != NULL) {
			$this->parentItem = $this->context->createMenuItems()->get($this->id);
		}
	} // actionAdd()
	
	
	
	/**
	 * Creates form for add new menu item into DB
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentMenuItemAdd()
	{
		// New instance of nette form
		$form = new \Nette\Application\UI\Form;
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table class="form"';
 		
 		$form->addGroup();
		
		// Add item name (text in menu)
		$form->addText('name', '*Popisek položky:')
			 ->setRequired('Vyplňte prosím popisek položky!')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Popisek položky je příliš dlouhý, max. délka je %d znaků!', 64);
			 
		
		// Add item title (text in title attribute)
		$form->addText('title', 'Titulek položky:')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Titulek položky je příliš dlouhý, max. délka je %d znaků!', 64);
		
		
		// Add item URL (relative or absolute)
		$form->addGroup();
		$form->addText('full_url', 'Adresa položky:')
			 ->setDefaultValue((($this->parentItem && $this->parentItem['page_id']) ? $this->template->baseUrl .'/'. $this->parentItem->url . '/' : ''))
			 ->setDisabled(true);
		
		$form->addText('url', 'URL položky:');
	
		// If parent id is sets, check if parent is external, if it is external, child should be
		// external too, if is not external, child should be external or local	
		if($this->parentItem && $this->parentItem['page_id'] === NULL) {
			$form['url']->addRule(\Nette\Application\UI\Form::URL, 'URL Adresa není ve správném formátu!');
		} else {
			// Add URL type (local or extern)	
			$form->addCheckbox('extern', 'Odkaz na externí stránky?');
		
			// Add condition on URL (if URL is extern, URL must be valid URL)
			$form['url']->addConditionOn($form['extern'], \Nette\Application\UI\Form::EQUAL, true)
			 			->addRule(\Nette\Application\UI\Form::URL, 'URL Adresa není ve správném formátu!');
		}
		
		
		/*
		$form->addText('url_query', 'Parametry URL')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Parametry URL jsou příliš dlouhé, max. délka je %d znaků!', 256);
			 
		$form->addText('url_fragment', 'Fragment URL')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Fragment URL je příliš dlouhý, max. délka je %d znaků!', 64);
		*/
		
		
		// Add two buttons
		$form->addGroup();
		$form->addSubmit('save', 'Vytvořit');
		$form->addSubmit('cancel', 'Zrušit')
		     ->setValidationScope(false);
		
		// Add callback
		$form->onSuccess[] = $this->menuItemAddSubmitted;
		
		
		// Return new form instance
		return $form;
	} // createComponentMenuItemAdd()
	
	
	
	/**
	 * Callback method for adding new menu item
	 * @param \Nette\Application\UI\Form $form
	 */
	public function menuItemAddSubmitted(\Nette\Application\UI\Form $form)
	{
		// Check submit button
		if($form['cancel']->isSubmittedBy()) {
			$this->redirect(':Admin:Cms:default');
			exit();
		}
		
		
		// Get values
		$parentUrl = '';
		$parentId = ($this->parentItem) ? $this->parentItem->id : NULL;
		$values = $form->values;
		$values['url_fragment'] = '';
		$values['url_query'] = '';
		$values['sort_order'] = $this->context->createMenuItems()->where('parent_id', $parentId)->count();
		$values['active'] = false;
		
		// If ID not empty
		if($this->parentItem !== NULL) {
			$values['parent_id'] = $this->parentItem->id;
			if($this->parentItem['page_id'] == NULL) {
				$values['extern'] = true;
			}
			else {
				$parentUrl = $this->parentItem->url . '/';
			}
		}
		// Check title
		if($values['title'] == NULL) {
			$values['title'] = $values['name'];
		} 
		// If new item is not extern
		if($values['extern'] === false) {
			if($values['url'] == NULL) {
				$values['url_rewrite_name'] = \Nette\Utils\Strings::webalize($values['name']);	
			}
			else {
				$values['url_rewrite_name'] = \Nette\Utils\Strings::webalize($values['url']);
			}
			
			// Set full URL
			$values['url'] = $parentUrl . $values['url_rewrite_name'];
			
			// Create new page for this item
			$insertPage = $this->context->createPages()->insert(array('heading' => $values['name'], 'seo_title' => $values['title'], 'created' => new \DateTime));
			$values['page_id'] = $insertPage->id;
		}
		else {
			if(!\Nette\Utils\Strings::startsWith($values['url'], 'http')) {
				$values['url'] = 'http://'.$values['url'];
			}
		}
		
		
		// Insert into DB, set flash message and redirect
		unset($values['extern']);
		$inserted = $this->context->createMenuItems()->insert($values);
		$this->flashMessage("Nová položka menu '".$inserted->id." - ".$values['name']."' byla úspěšně vytvořena!", 'info');
		$this->redirect(':Admin:Cms:', array($this->id => NULL));
		
		
		return;
	} // menuItemAddSubmitted()
	
	
	
	/** actionEditItem ***************************************************** */
	
	
	
	/**
	 * Action for edit menu item. 
	 */
	public function actionEditItem()
	{
		if($this->id != NULL) {
			$this->menuItem = $this->context->createMenuItems()->get($this->id);
			$this->parentItem = $this->context->createMenuItems()->get($this->menuItem['parent_id']);
		}
		if(!$this->menuItem) {
			$this->flashMessage('Nebylo zadáno platné ID položky menu. Nelze editovat', 'error');
			$this->redirect(':Admin:Cms:', array('id' => NULL));
			exit();
		}
	} // actionEditItem()
	
	
	
	
	/**
	 * Render edit item action.
	 */
	public function renderEditItem()
	{
		$this->template->menuItem = $this->menuItem;
	} // renderEditItem()
	
	
	
	/**
	 * Creates form for add new menu item into DB
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentMenuItemEdit()
	{
		// New instance of nette form
		$form = new \Nette\Application\UI\Form;
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table class="form"';
 		
		$form->addGroup();
		
		// Add item name (text in menu)
		$form->addText('name', '*Popisek položky:')
			 ->setRequired('Vyplňte prosím popisek položky!')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Popisek položky je příliš dlouhý, max. délka je %d znaků!', 64)
			 ->setDefaultValue($this->menuItem->name);
			 
		
		// Add item title (text in title attribute)
		$form->addText('title', 'Titulek položky:')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Titulek položky je příliš dlouhý, max. délka je %d znaků!', 64)
			 ->setDefaultValue($this->menuItem->title);
		
		$form->addGroup();
		
		// Add full URL
		if($this->menuItem['page_id'] != NULL) {
			$form->addText('full_url', 'Adresa položky:')
				 ->setDefaultValue('http://www.example.com/' . (($this->parentItem) ? $this->parentItem->url . '/' : ''))
				 ->setDisabled(true);
		}
		
		// Add item URL (relative or absolute)
		$form->addText('url', 'URL položky:')
			 ->setDefaultValue(($this->menuItem['page_id']) ? $this->menuItem['url_rewrite_name'] : $this->menuItem['url']);
	
		// If parent id is sets, check if parent is external, if it is external, child should be
		// external too, if is not external, child should be external or local	
		if($this->menuItem['page_id'] === NULL) {
			$form['url']->addRule(\Nette\Application\UI\Form::URL, 'URL Adresa není ve správném formátu!');
		}
		
		$form->addGroup();
		$form->addSelect('parent_id', 'Nadřazená položka:', $this->generateMenuItemsInSelectbox($this->id))
			 ->setPrompt('-- hlavní úroveň --')
			 ->setDefaultValue($this->menuItem->parent_id);
		
		
		/*
		$form->addText('url_query', 'Parametry URL')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Parametry URL jsou příliš dlouhé, max. délka je %d znaků!', 256);
			 
		$form->addText('url_fragment', 'Fragment URL')
			 ->addRule(\Nette\Application\UI\Form::MAX_LENGTH, 'Fragment URL je příliš dlouhý, max. délka je %d znaků!', 64);
		*/
		
		
		// Add two buttons
		$form->addGroup();
		$form->addSubmit('save', 'Uložit');
		$form->addSubmit('save_and_back', 'Uložit a pokračovat');
		$form->addSubmit('cancel', 'Zrušit')
		     ->setValidationScope(false);
		
		// Add callback
		$form->onSuccess[] = $this->menuItemEditSubmitted;
		
		
		// Return new form instance
		return $form;
	} // createComponentMenuItemEdit()
	
	
	
	/**
	 * Callback method for adding new menu item
	 * @param \Nette\Application\UI\Form $form
	 */
	public function menuItemEditSubmitted(\Nette\Application\UI\Form $form)
	{
		// Check submit button
		if($form['cancel']->isSubmittedBy()) {
			$this->redirect(':Admin:Cms:default');
			exit();
		}
		
		
		// Get values
		$parentUrl = '';
		$parentId = ($this->parentItem) ? $this->parentItem->id : NULL;
		$values = $form->values;
		
		// Set new parent
		if($this->menuItem->parent_id != $values['parent_id']) {
			// Set parent
			$this->parentItem = $this->context->createMenuItems()->get($values['parent_id']);
			
			// Set new sort order
			$values['sort_order'] = $this->context->createMenuItems()->where('parent_id', $values['parent_id'])->count();
			
			// Get all items 
			$items = $this->context->createMenuItems()->where('parent_id', $this->menuItem->parent_id)->where('sort_order > ?', $this->menuItem->sort_order);
			while($i = $items->fetch()) {
				$i->update(array('sort_order' => $i['sort_order']-1));
			}
		}
		// If ID not empty
		if($this->parentItem !== NULL) {
			if($this->parentItem['page_id'] != NULL) {
				$parentUrl = $this->parentItem->url . '/';
			}
		}
		
		// Check title
		if($values['title'] == NULL) {
			$values['title'] = $values['name'];
		} 
		// If new item is not extern
		if($this->menuItem['page_id'] != NULL) {
			if($values['url'] == NULL) {
				$values['url_rewrite_name'] = \Nette\Utils\Strings::webalize($values['name']);	
			}
			else {
				$values['url_rewrite_name'] = \Nette\Utils\Strings::webalize($values['url']);
			}
			
			// Set full URL
			$values['url'] = $parentUrl . $values['url_rewrite_name'];
			
			// Edit all URLs for children items
			$this->updateChildrenUrl($this->menuItem->id, $values['url']);
		}
		else {
			if(!\Nette\Utils\Strings::startsWith($values['url'], 'http')) {
				$values['url'] = 'http://'.$values['url'];
			}
		}
		
		
		
		// Insert into DB, set flash message and redirect
		$this->menuItem->update($values);
		$this->flashMessage("Položka menu '".$this->menuItem->id." - ".$values['name']."' byla úspěšně aktualizována.", 'info');
		if($form['save_and_back']->isSubmittedBy()) {
			$this->redirect(':Admin:Cms:default');
			exit();
		}		
		
		$this->redirect(':Admin:Cms:editItem', array($this->id => NULL));
		exit();
		
		return;
	} // menuItemEditSubmitted()
	
	
	
	/** actionEditPage ***************************************************** */
	
	
	
	/**
	 * Action for edit menu item. 
	 */
	public function actionEditPage($showComments = true, $commentId = NULL, $commentEdit = false)
	{
		if($this->id != NULL) {
			$this->menuItem = $this->context->createMenuItems()->where(array('page_id' => $this->id));
			$this->page = $this->context->createPages()->get($this->id);
			
			$this->commentId = $commentId; 
			$this->commentEdit = $commentEdit;
			if($showComments) {
				$this->comments = $this->page->related('comments')->order('added ASC');
				$this->showComments = true;
			}
		}
		if(!$this->page || !$this->menuItem) {
			$this->flashMessage('Nebylo zadáno platné ID stránky obsahu. Nelze editovat', 'error');
			$this->redirect(':Admin:Cms:', array('id' => NULL));
			exit();
		}
		
		if($this->isAjax()) {
			$this->invalidateControl('comments');
		}
	} // actionAdd()
	
	
	
	
	/**
	 * Render edit item action.
	 */
	public function renderEditPage($showComments = true, $commentId = NULL)
	{
		$this->template->menuItem = $this->menuItem;
		$this->template->page = $this->page;
		$this->template->comments = $this->comments;
		$this->template->showComments = $this->showComments;
	} // renderEditItem()
	
	
	
	/**
	 * Creates form for edit CMS page and their content
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentPageEdit()
	{
		// New instance of nette form
		$form = new \Nette\Application\UI\Form;
		$form->addGroup();
		
		$form->addText('created', 'Vytvořeno:')
			 ->setDefaultValue(\Nette\Templating\Helpers::date($this->page['created'], 'H:i:s d.m.Y'))
			 ->setDisabled(true);
		$form->addText('modified', 'Naposledy upraveno:')
			 ->setDefaultValue(\Nette\Templating\Helpers::date($this->page['modified'], 'H:i:s d.m.Y'))
			 ->setDisabled(true);
		
		$form->addGroup();
		$form->addText('heading', 'Nadpis stránky:')
			 ->setDefaultValue($this->page['heading']);
		
		$form->addGroup();
		$form->addText('seo_title', '(SEO) Titulek:')
			 ->setDefaultValue($this->page['seo_title']);
		$form->addText('seo_keywords', '(SEO) Klíčová slova:')
			 ->setDefaultValue($this->page['seo_keywords']);
		$form->addTextArea('seo_description', '(SEO) Popis:', 80, 3)
			 ->setDefaultValue($this->page['seo_description']);
	
		$form->addGroup();
		$form->addRadioList('allow_comments', 'Povolit komentáře?', array(true => 'Ano', false => 'Ne'))
			 ->setDefaultValue($this->page['allow_comments']);
			 
		$form->addTextArea('content', NULL, 80, 40)
			 ->setRequired('Musíte napsat nějaký obsah, který se bude zobrazovat na stránce!')
			 ->setDefaultValue($this->page['content']);			 
		
		
		// Add two buttons
		$form->addGroup();
		$form->addSubmit('save', 'Uložit');
		$form->addSubmit('save_and_back', 'Uložit a pokračovat');
		$form->addSubmit('cancel', 'Zrušit')
		     ->setValidationScope(false);
		
		// Add callback
		$form->onSuccess[] = $this->pageEditSubmitted;
		
		
		// Return new form instance
		return $form;
	} // createComponentPageEdit()
	
	
	
	/**
	 * Callback method for adding new menu item
	 * @param \Nette\Application\UI\Form $form
	 */
	public function pageEditSubmitted(\Nette\Application\UI\Form $form)
	{
		// Check submit button
		if($form['cancel']->isSubmittedBy()) {
			$this->redirect(':Admin:Cms:default');
			exit();
		}
		
		
		// Get values
		$values = $form->values;
		$values['modified'] = new \DateTime;
		
		
		// Insert into DB, set flash message and redirect
		$this->page->update($values);
		$this->flashMessage("Stránka obsahu '".$this->page->id." - ".$values['heading']."' byla úspěšně aktualizována.", 'info');
		if($form['save_and_back']->isSubmittedBy()) {
			$this->redirect(':Admin:Cms:default');
			exit();
		}		
		
		$this->redirect('this');
		exit();
		
		
		return;
	} // pageEditSubmitted()
	
	
	
	protected function createComponentPageComments()
	{
		$comments = $this->context->createComments()->getPageComments($this->page->id)->order('added ASC');
		$control = new \Components\CommentsControl\CommentsControl($comments, $this->page);
		$control->setShowBar(true);
		return $control;
	}
	
	
	
	/** *****************************/
	
	
	private function updateChildrenUrl($parentId, $parentUrl)
	{
		$children = $this->context->createMenuItems()->where('parent_id', $parentId);
		foreach($children as $child) {
			$url = $parentUrl.'/'.$child['url_rewrite_name'];
			$child->update(array('url' => $url));
			$this->updateChildrenUrl($child->id, $url);
		}
		
		return;
	} // updateChildrenUrl()
	
	
	
	private function generateMenuItemsInSelectbox($itemId, $parentId = NULL, $level = 0)
	{
		$items = array();
		$result = $this->context->createMenuItems()->where('parent_id', $parentId)->where('id <> ?', $itemId)->order('sort_order ASC');
		if($result) { 
			foreach($result as $item) {
				$items[$item->id] = str_repeat("-", 4*$level) .' '. $item->name;
				$children = $this->generateMenuItemsInSelectbox($itemId, $item->id, $level+1);
				if(!empty($children)) {
					foreach($children as $id => $child) {
						$items[$id] = $child;
					}
				}
			}
		}
		
		return $items;
	}
	
	

} // class \AdminModule\CmsPresenter 
