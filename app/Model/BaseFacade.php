<?php declare(strict_types=1);

namespace App\Model;

abstract class BaseFacade
{
    public function __construct(
        protected \Nette\Database\Explorer $dbconn
    ) {}
}
