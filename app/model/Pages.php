<?php
/**
 * @filesource  \Model\Pages.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \Model
 * @version     1.0.0 
 */

 

// Namespace definition
namespace Model;



/**
 * @abstract \Model\Pages 
 * Pages model.
 */
class Pages extends \Nette\Database\Table\Selection
{
	
	
	/**
	 * Default constructor.
	 * @param Nette\Database\Connection $dbconn
	 * @return Pages
	 */
	public function __construct(\Nette\Database\Connection $dbconn)
	{
		parent::__construct('pages', $dbconn);
	} // __construct()


	
} // class Pages
