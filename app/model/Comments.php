<?php
/**
 * @filesource  \Model\Comments.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \Model
 * @version     1.0.0 
 */

 

// Namespace definition
namespace Model;



/**
 * @abstract \Model\Comments 
 * Page comments model.
 */
class Comments extends \Nette\Database\Table\Selection
{
	
	
	/**
	 * Default constructor.
	 * @param Nette\Database\Connection $dbconn
	 * @return Comments
	 */
	public function __construct(\Nette\Database\Connection $dbconn)
	{
		parent::__construct('comments', $dbconn);
	} // __construct()
	
	
	
	public function getGuestbookComments()
	{
		return $this->where('page_id', NULL)->where('gallery_id', NULL)->where('news_id', NULL);
	}
	public function getPageComments($pageId)
	{
		return $this->where('page_id', $pageId)->where('gallery_id', NULL)->where('news_id', NULL);
	}
	public function getGalleryComments($galleryId)
	{
		return $this->where('page_id', NULL)->where('gallery_id', $galleryId)->where('news_id', NULL);
	}
	public function getNewsComments($newsId)
	{
		return $this->where('page_id', NULL)->where('gallery_id', NULL)->where('news_id', $newsId);
	}
	


	
} // class Comments
