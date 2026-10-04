<?php declare(strict_types=1);

namespace Model;

abstract class BaseEntity
{
    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): self
    {
        throw new \LogicException('Method fromActiveRow() must be implemented in the child class.');
    }

    public static function fromSelection(\Nette\Database\Table\Selection $selection): array
    {
        return array_map(
            fn(\Nette\Database\Table\ActiveRow $row) => static::fromActiveRow($row),
            $selection->fetchAll(),
        );
    }
}
