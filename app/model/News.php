<?php
/**
 * @filesource  \Model\News.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \Model
 * @version     1.0.0 
 */

 

// Namespace definition
namespace Model;



/**
 * @abstract \Model\News 
 * News model.
 */
class News extends \Nette\Database\Table\Selection
{
	
	
	/**
	 * Default constructor.
	 * @param Nette\Database\Connection $dbconn
	 * @return Pages
	 */
	public function __construct(\Nette\Database\Connection $dbconn)
	{
		parent::__construct('news', $dbconn);
	} // __construct()


	
} // class \Model\News