<?php
/**
 * @filesource  \Model\GalleryItems.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \Model
 * @version     1.0.0 
 */

 

// Namespace definition
namespace Model;



/**
 * @abstract \Model\GalleryItems 
 * Gallery items model.
 */
class GalleryItems extends \Nette\Database\Table\Selection
{
	
	
	/**
	 * Default constructor.
	 * @param Nette\Database\Connection $dbconn
	 * @return Pages
	 */
	public function __construct(\Nette\Database\Connection $dbconn)
	{
		parent::__construct('gallery_items', $dbconn);
	} // __construct()


	
} // class GalleryItems