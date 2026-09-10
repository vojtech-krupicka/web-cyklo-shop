<?php
/**
 * @filesource  \Hassa\Diagnostics\Panels\SessionPanel.php
 *
 * @author      Bc. Vojtech Krupicka
 * @copyright   hl2 - ZONE 2011
 * @package     Hassa
 * @version     1.0.0 
 */
 

// Namespace for this file
namespace Hassa\Diagnostics\Panels;
 
 
/**
 * @final  \Hassa\Components\Navigation\Navigation 
 * Class for create new panel to debug bar for show all sessions. 
 */
class SessionPanel implements \Nette\Diagnostics\IBarPanel 
{
	private $sess;
	
	

	public function __construct(\Nette\Http\Session $sess) {
		$this->sess = $sess;
	}

	
	
	function getTab() {
		return '<img src="data:image/png;base64,/9j/4AAQSkZJRgABAQAAAQABAAD//gA7
Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2NjIpLCBxdWFsaXR5ID0gODUK/9sA
QwAFAwQEBAMFBAQEBQUFBgcMCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQi
HiQcHh8e/9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e
Hh4eHh4eHh4eHh4eHh4e/8AAEQgADwAPAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgME
BQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHw
JDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SF
hoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq
8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQE
AAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4
OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ip
qrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A
7v44fFu70fV2tILtIlZZ3toHu1t0lSGMyOS7HBJG3CnJJdVA6mmfs+fGxvEtkLyaS6ezDtDPbzHfJC4A
I2n0wRjoMZ4BFR/Hz4HReMtdsrKeSWCGe8/0K5gKloQ5AdSjEbgFAyMjOxcNkkV6n4P+C3w78J6dFY6L
orwRo252+0yb5Thhlznk/N/46vpXlUqFSd53ampdW7Wvtba1vx8z2auIowSp8qcHHolzJ23b3vzfKx//
2Q=="/>' . '&nbsp;' . $this->sess->getIterator()->count() . '&nbsp;sessions';
	}

	
	
	function getPanel() {
		$ret = array();
		foreach($this->sess->getIterator() as $ns) {
			$ret[$ns] = iterator_to_array($this->sess->getSection($ns));
		}
		
		$html = \Nette\Utils\Html::el('div')->class('nette-UserPanel')
			->add(\Nette\Utils\Html::el('h1', 'Sessions'))
			->add(\Nette\Diagnostics\Helpers::clickableDump($ret));
		
		return $html;
	}
	
	

} // class \Hassa\Diagnostics\Panels\SessionPanel
