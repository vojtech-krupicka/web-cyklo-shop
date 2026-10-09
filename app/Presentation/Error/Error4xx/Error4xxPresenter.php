<?php declare(strict_types=1);

namespace App\Presentation\Error\Error4xx;

use Nette;
use Nette\Application\Attributes\Requires;


/**
 * Handles 4xx HTTP error responses.
 */
#[Requires(methods: '*', forward: true)]
final class Error4xxPresenter extends Nette\Application\UI\Presenter
{
    public function actionDefault(\Throwable $exception, ?Nette\Application\Request $request = null): void
	{
		$module = str_starts_with((string) $request?->getPresenterName(), 'AdminModule:')
			? 'AdminModule'
			: 'FrontModule';
		$this->forward(":$module:Error4xx:default", ['exception' => $exception]);
	}
}
