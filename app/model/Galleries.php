<?php
/**
 * @filesource  \Model\Galleries.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \Model
 * @version     1.0.0 
 */

 

// Namespace definition
namespace Model;



/**
 * @abstract \Model\Galleries 
 * Galleries model.
 */
class Galleries extends \Nette\Database\Table\Selection
{
	
	
	/**
	 * Default constructor.
	 * @param Nette\Database\Connection $dbconn
	 * @return Pages
	 */
	public function __construct(\Nette\Database\Connection $dbconn)
	{
		parent::__construct('galleries', $dbconn);
	} // __construct()


	
} // class Galleries