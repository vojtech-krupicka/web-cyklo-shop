<?php

namespace App\Presentation;

use Nette;

/**
 * @abstract  \BasePresenter
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    public function startup(): void
	{
		// Zavola rodice !!!
		parent::startup();

		// Nastavi generovani absolutnich URL
		$this->absoluteUrls = true;

		// Nastaveni ticheho modu pro generovani odkazu
		$this->invalidLinkMode = self::InvalidLinkWarning;

		return;
	}


    /**
	 * Overloading flash message method for authomatic invalidate snippet.
	 * @param mixed $message
	 * @param mixed $type
     * @return \stdClass
	 */
	public function flashMessage($message, $type = 'info'): \stdClass
	{
		$flash = parent::flashMessage($message, $type);

		if($this->isAjax()) {
			$this->invalidateControl("flashes");
		}

		return $flash;
	}

}
