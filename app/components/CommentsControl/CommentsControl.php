<?php
/**
 * @filesource  \Components\CommentsControl\CommentsControl.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \AdminModule
 * @version     1.0.0 
 */



// Namespace definition
namespace Components\CommentsControl;


/**
 * @abstract  \Components\CommentsControl\CommentsControl 
 * Component for render list of comments.
 */
class CommentsControl extends \Components\BaseControl
{
	
	/**
	 * @var \Model\Comments
	 */
	protected $comments = NULL;
	
	
	/**
	 * @var \Nette\Security\User  
	 */
	protected $user = NULL;
	
	
	protected $page = NULL;
	protected $gallery = NULL;
	protected $news = NULL;
	protected $ownerId = 0;
	protected $showBar = false;
	
	
	
	/**
	 * @persistent
	 */
	public $showComments = true;
	/**
	 * @persistent
	 */
	public $commentId = 0;
	/**
	 * @persistent
	 */
	public $commentEdit = false;
	
	
	
	
	
	/**
	 * Default constructor
	 * @param \Model\Comments $comment
	 * @return CommentsControl
	 */
	public function __construct(\Model\Comments $comments, $page = NULL, $gallery = NULL, $news = NULL)
	{
		parent::__construct();
		$this->comments = $comments;
		$this->page = $page;
		$this->gallery = $gallery;
		
		if($this->page) $this->ownerId = $this->page->id;
		else if($this->gallery) $this->ownerId = $this->gallery->id;
	}
	
	
	
	/**
	 * put your comment there...
	 * @param mixed $component
	 */
	protected function attached($component)
	{
		parent::attached($component);
		$this->user = $component->user;
	}
	
	
	public function setShowBar($flag = true)
	{
		$this->showBar = $flag;
		return $this;
	}
	
	
	/**
	 * 
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . '/commentsControl.latte');
        
        $this->template->comments = $this->comments;
        $this->template->user = $this->user;
        
        $this->template->showComments = $this->showComments;
        $this->template->commentId = $this->commentId;
        $this->template->commentEdit = $this->commentEdit;
        
        $this->template->page = $this->page;
        $this->template->gallery = $this->gallery;
        $this->template->ownerId = $this->ownerId;
        $this->template->showBar = $this->showBar;
        
        $this->template->render();
	} // render()
	
	
	
	
		/**
	 * Creates form for edit CMS page and their content
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentCommentForm()
	{
		// New instance of nette form
		$form = new \Nette\Application\UI\Form;
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table class="form"';
 		
 		$form->addGroup();
		$form->addText('nickname', '*Jméno:')
			 ->setRequired('Musíte vyplnit Vaše jméno!');
		$form->addText('email', 'E-mail:')
			 ->setAttribute('placeholder', 'Vaše emalová adresa')
			 ->addCondition(\Nette\Forms\Form::FILLED)
			 ->addRule(\Nette\Forms\Form::EMAIL, 'Zadaná emailová adresa není platná!');
		$form->addText('subject', 'Předmět:');
		$form->addTextArea('comment', '*Text:', 80, 16)
			 ->setRequired('Musíte vyplnit text komentáře!')
			 ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Minimální délka textu je %d znaků!', 5)
			 ->setAttribute('class', 'mceEditorLite');
			
		$comment = $this->comments->get($this->commentId);	 
		if($comment) {
			if($this->commentEdit) {				 
				$form['nickname']->setDefaultValue($comment->nickname);
				$form['email']->setDefaultValue($comment->email);
				$form['subject']->setDefaultValue($comment->subject);
				$form['comment']->setDefaultValue($comment->comment);
			}
			else {
				$defaultVal = '<blockquote><h2>Původní zpráva od [<a href="'.$this->link('this#comment-'.$comment->id.'-owner-'.$this->ownerId, array('commentId' => NULL, 'commentEdit' => false)).'" title="'.$comment->nickname.'">'.$comment->nickname.'</a>]:</h2>'.$comment->comment.'</blockquote><br />';
				$form['comment']->setDefaultValue($defaultVal);
				$form['subject']->setDefaultValue('Re: '.$comment->subject);
			}
		}
	
		// Add two buttons
		$form->addGroup();
		$form->addSubmit('save', 'Uložit');
		$form->addSubmit('cancel', 'Zrušit')
		     ->setValidationScope(false);
		
		// Add callback
		$form->onSuccess[] = $this->commentAddSubmitted;
		
		
		// Return new form instance
		return $form;
	} // createComponentPageEdit()
	
	
	
	/**
	 * Callback method for adding new menu item
	 * @param \Nette\Application\UI\Form $form
	 */
	public function commentAddSubmitted(\Nette\Application\UI\Form $form)
	{
		// Check submit button
		if($form['cancel']->isSubmittedBy()) {
			$this->redirect('this', array('commentId' => NULL, 'commentEdit' => false));
			exit();
		}
		
		
		// Get values
		$values = $form->values;
		$values['added'] = new \DateTime;
		if($this->page) $values['page_id'] = $this->page->id;
		else  if($this->gallery) $values['gallery_id'] = $this->gallery->id;
		
		
		if($this->commentId) {
			if(!$this->commentEdit) {
				$values['parent_id'] = $this->commentId;	
				$this->comments->insert($values);
				$this->presenter->flashMessage("Nový komentář byl úspěšně vytvořen a uložen.", 'info');
				
				$this->redirect('this', array('commentId' => NULL, 'commentEdit' => false));
				exit();		
			}	
			else {
				$comment = $this->comments->get($this->commentId);
				if($comment) {
					$comment->update($values);
					$this->presenter->flashMessage("Komentář byl úspěšně upraven a uložen.", 'info');
					
					$this->redirect('this', array('commentId' => NULL, 'commentEdit' => false));
					exit();
				}
				else {
					$form->addError("Při editaci komentáře došlo k chybě! Komentáře s id ".$this->commentId." neexistuje.");
				}
			}
			
		}
		else {
			$this->comments->insert($values);
			$this->presenter->flashMessage("Nový komentář byl úspěšně vytvořen a uložen.", 'info');
			
			$this->redirect('this', array('commentId' => NULL, 'commentEdit' => false));
			exit();				
		}
		
		return;
	} // commentAddSubmitted()
		
	
	
	/**
	 * Delete comment with given ID.
	 * @param mixed $id
	 */
	public function handleDelete($commentId)
	{
		$comment = $this->comments->where('id', $commentId)->fetch();
		if($comment) {
			$comment->delete();
			$this->presenter->flashMessage('Komentář byl úspěšně odstraněn.', 'info');
		}
		else {
			$this->presenter->flashMessage('Komentář s id #'.$commentId.' neexistuje!', 'error');
		}
		
		if($this->presenter->isAjax()) {
			$this->invalidateControl('comments');
		}
		else {
			$this->redirect('this', array('commentId' => NULL, 'commentEdit' => false));
			exit();
		}
	} // handleDelete()
	
	
	
} // class  \Components\CommentsControl\CommentsControl