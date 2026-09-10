<?php
/**
 * @filesource  \AdminModule\FilePresenter.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \AdminModule
 * @version     1.0.0 
 */



// Namespace definition
namespace AdminModule;


/**
 * @abstract  \AdminModule\FilePresenter 
 * Presenter for upload and handle files (images, pdfs, documents, ...).
 */
class FilePresenter extends \AdminModule\BaseSecuredPresenter
{

	/** @var int */
	const DEFAULT_IMAGES_DIR = '/images'; 
	const DEFAULT_FILES_DIR = '/files';
		
	
	/**
	 * Render default action.
	 */
	public function renderDefault()
	{
		$dirName = $this->getDirName(false);
		
		$this->template->files = array();
		$files = \Nette\Utils\Finder::findFiles('*')->in($dirName.'/')->exclude('.', '..', '.svn');
		foreach($files as $fn => $foo) {
			
			$fn = substr($fn, strlen($dirName)+1);
			$parts = explode('.', $fn);
			
			$file['file_name'] = $fn;
			$file['extension'] = array_pop($parts);
			$file['name'] = join('.', $parts);
			$file['full_path'] = $dirName . '/' .$file['file_name'];
			$file['web_path'] = $this->template->baseUrl . '/resources/files/' .$file['file_name'];
			$file['file_size'] = @filesize($file['full_path']);
			$file['modified'] = new \DateTime(date("Y-m-d h:i:s", @filemtime($file['full_path'])));
			
			$this->template->files[] = $file;
		}
		
		
		 
		
		$dirName = $this->getDirName(true);
		$this->template->images = array();
		$images = \Nette\Utils\Finder::findFiles('*')->in($dirName.'/')->exclude('.', '..', '.svn');
		foreach($images as $fn => $foo) {			
			$fn = substr($fn, strlen($dirName)+1);
			$parts = explode('.', $fn);
			
			$file['file_name'] = $fn;
			$file['extension'] = array_pop($parts);
			$file['name'] = join('.', $parts);
			$file['full_path'] = $dirName . '/' .$file['file_name'];
			$file['web_path'] = $this->template->baseUrl . '/resources/images/' .$file['file_name'];
			$file['file_size'] = @filesize($file['full_path']);
			$file['modified'] = new \DateTime(date("Y-m-d h:i:s", @filemtime($file['full_path'])));
			
			$this->template->images[] = $file;
		}
		
	} // renderDefault()
	
	
	
	/**
	 * Creates form for add new gallery item.
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentFileAdd()
	{
		// New instance of nette form
		$form = new \Nette\Application\UI\Form;
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table class="form"';
 		
 		$form->addGroup();
		
		// Add item name (text in menu)
		$form->addText('name', 'Nové jméno:');
			 
		// File
		$form->addUpload('file', '*Soubor:')
			 ->setRequired('Musíte vložit soubor, který chcete nahrát!');		
			 
		// Allow owerride
		$form->addCheckbox('overwrite', 'Povolit přepsání souborů se stejným názvem?')
			 ->setDefaultValue(false);
		
		
		// Add two buttons
		$form->addGroup();
		$form->addSubmit('save', 'Přidat');
		
		
		// Add callback
		$form->onSuccess[] = $this->fileAddSubmitted;
		
		
		// Return new form instance
		return $form;
	} // createComponentFileAdd()
	
	
	
	/**
	 * Callback method for adding new gallery item.
	 * @param \Nette\Application\UI\Form $form
	 */
	public function fileAddSubmitted(\Nette\Application\UI\Form $form)
	{
		// Get values
		$file = $form->values->file;
		
		// Check if new image is OK
		if(!$file->isOk()) {
			$this->flashMessage("Při nahrávání souboru '".$fileName."' na server došlo k chybě!", 'error');
			$this->redirect('this');
		}
		
		
		$fileName = $form->values->file->sanitizedName;
		$parts = explode('.', $fileName);
		$extension = array_pop($parts);		
		$fileName = join('.', $parts); 
		
		$newFileName = ($form->values->name) ? \Nette\Utils\Strings::webalize($form->values->name) : $fileName;		
		$isImage = $file->isImage();
		$dirName = $this->getDirName($isImage);
		
		if(!$form->values->overwrite) {
			$index = 0;
			if(file_exists($dirName.'/'.$newFileName.'.'.$extension)) {
				foreach(\Nette\Utils\Finder::findFiles('*')->in($dirName.'/')->exclude('.', '..', '.svn') as $fn => $foo) {
					$fn = substr($fn, strlen($dirName)+1);
					
					if(\Nette\Utils\Strings::match($fn, '#^'.$newFileName.'_[0-9]*.'.$extension.'#')) {
						$index++;
					}
				}
				
				$newFileName = $newFileName.'_'.$index; 
				$this->flashMessage("Obrázek byl přejmenován na '".$newFileName.'.'.$extension."' z důvodu konfliktu jmen!", 'warning');
			}
		}
		
		$newFileName .= '.'.$extension;
		$file->move($dirName.'/'.$newFileName);
		$this->generateFilesList($isImage);
		
		if($isImage) {
			$this->flashMessage("Nový obrázek '".$newFileName."' byl úspěšně nahrán na server!", 'info');
		} else {
			$this->flashMessage("Nový soubor '".$newFileName."' byl úspěšně nahrán na server!", 'info');
		}
		
		
		$this->redirect('this');
		
		
		return;
	} // fileAddSubmitted()
	
	
	
	public function handleDelete($fileName, $img = true)
	{
		$dirName = $this->getDirName($img);
		if(is_file($dirName  . '/' . $fileName)) {
			unlink($dirName  . '/' . $fileName);
			$this->generateFilesList($img);
			$this->flashMessage("Soubor '$fileName' byl úspěšně smazán!", 'info');
		}
		else {
			$this->flashMessage("Soubor '$fileName' neexistuje!", 'error');
		}
		
		$this->redirect('this');
		
		
		return;
	} // handleDelete()
	
	
	
	/** others methods ******************************************************* */
	
	
	
	/**
	 * returns basic directory path to Galleries dir in resources 
	 */
	public function getDirName($img = true) 
	{
		return $this->context->parameters['resourcesDir']. (($img) ? self::DEFAULT_IMAGES_DIR : self::DEFAULT_FILES_DIR);
	} // getDirName()
	
	
	
	
	public function generateFilesList($img = true)
	{
		// You can't simply echo everything right away because we need to set some headers first!
		$output = ''; // Here we buffer the JavaScript code we want to send to the browser.
		$delim = "\n"; // for eye candy... code gets new lines

		$output .= ($img) ? 'var tinyMCEImageList = new Array(' : 'var tinyMCELinkList = new Array(';

		// Since TinyMCE3.x you need absolute image paths in the list...
		$wwwPath = (($img) ? '/resources/images' : $this->template->baseUrl . '/resources/files');
		$outputName = ($img) ? 'tinymce.imagelist.js' : 'tinymce.filelist.js';
		$outputPath = $this->context->parameters['wwwDir'] . '/javascript';
		$dir = $this->getDirName($img);

		foreach(\Nette\Utils\Finder::findFiles('*')->in($dir.'/')->exclude('.', '..', '.svn') as  $fn => $foo) {
			if(is_file("$fn")) {
		        // We got ourselves a file! Make an array entry:
		        $fn = substr($fn, strlen($dir)+1);
		        $output .= $delim
		            . '["'
		            . $fn
		            . '", "'
		            . "$wwwPath/$fn"
		            . '"],';
		    }
		}
		
		$output = substr($output, 0, -1); // remove last comma from array item list (breaks some browsers)
		$output .= $delim;
	
		// Finish code: end of array definition. Now we have the JavaScript code ready!
		$output .= ');';
		
		
		file_put_contents($outputPath.'/'.$outputName, $output);

		
		return;
	} // generateFilesList()
	
	

} // class \AdminModule\FilePresenter 
