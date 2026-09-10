<?php
/**
 * @filesource  \Model\Authenticator.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \Model
 * @version     1.0.0 
 */

 

// Namespace definition
namespace Model;



/**
 * @abstract \Model\Authenticator 
 * Users authenticator.
 */
class Authenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{
	
	
	/** @var \Model\Members */
	private $members;



	/**
	 * Default constructor.
	 * @param Nette\Database\Connection $database
	 * @return Authenticator
	 */
	public function __construct(\Model\Members $members)
	{
		$this->members = $members;
	} // __construct()



	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		// Get member from DB
		list($nickname, $password) = $credentials;
		$row = $this->members->where(array('nickname' => $nickname, 'active' => true))->fetch();

		
		// Check credentials
		if (!$row || $row->password !== $this->saltPassword($nickname, $password)) {
			throw new \Nette\Security\AuthenticationException("Přihlášení se nezdařilo, přihlašovací jméno a heslo se neexistuje!", self::INVALID_CREDENTIAL);
		}

		
		// Unset password and update last logon
		unset($row->password);
		$row->lastLogon = new \DateTime;
		$this->members->update(array('last_logon' => $row->lastLogon));
		
		// Create new identity
		return new \Nette\Security\Identity($row->id, $row->role, $row->toArray());
	} // authenticate()

	
	
	/**
	 * Computes salted password hash a salt and cipher password.
	 * @param string $salt
	 * @param string $pssw
	 * @param bool   $hash
	 * @return string Return salted encrypted password.
	 */
	public static function saltPassword($salt, $str, $hash = true)
	{
		$strLen = strlen($str);
		$strParts = array();
		$strParts[0] = substr($str, 0, ($strLen / 2));
		$strParts[1] = substr($str, ($strLen / 2));
		$salted = $strParts[0] . $salt . $strParts[1];
		if($hash) $salted = \Nette\Utils\Strings::lower(hash('sha512', $salted));
		
		return $salted;
	} // saltPassword()
	

} // class Authenticator
