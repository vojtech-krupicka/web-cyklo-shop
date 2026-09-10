<?php
/**
 * @filesource  \Model\MenuItems.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \Model
 * @version     1.0.0 
 */

 

// Namespace definition
namespace Model;



/**
 * @abstract \Model\MenuItems 
 * Menu Items model.
 */
class MenuItems extends \Nette\Database\Table\Selection
{
	
	
	/**
	 * Default constructor.
	 * @param Nette\Database\Connection $dbconn
	 * @return MenuItems
	 */
	public function __construct(\Nette\Database\Connection $dbconn)
	{
		parent::__construct('menu_items', $dbconn);
	} // __construct()

	
	
} // class MenuItems
