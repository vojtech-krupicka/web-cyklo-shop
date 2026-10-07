<?php declare(strict_types=1);

namespace App\Model;

use Nette\Database\Table;

abstract class BaseEntity
{
    /**
     * @return static
     */
    public static function fromActiveRow(Table\ActiveRow $row): self
    {
        throw new \LogicException('Method fromActiveRow() must be implemented in the child class.');
    }

    /**
     * @param Table\Selection<Table\ActiveRow> $selection
     * @return array<static>
     */
    public static function fromSelection(Table\Selection $selection): array
    {
        return array_map(
            fn(Table\ActiveRow $row) => static::fromActiveRow($row),
            $selection->fetchAll(),
        );
    }
}
