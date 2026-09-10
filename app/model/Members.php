<?php
/**
 * @filesource  \Model\Members.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \Model
 * @version     1.0.0 
 */

 

// Namespace definition
namespace Model;



/**
 * @abstract \Model\Members 
 * Members model.
 */
class Members extends \Nette\Database\Table\Selection
{
	
	
	/**
	 * Default constructor.
	 * @param Nette\Database\Connection $dbconn
	 * @return Members
	 */
	public function __construct(\Nette\Database\Connection $dbconn)
	{
		parent::__construct('members', $dbconn);
	} // __construct()


	
} // class Members
