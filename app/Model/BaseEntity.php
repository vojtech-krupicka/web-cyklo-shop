<?php declare(strict_types=1);

namespace App\Model;

use Nette\Database\Table;

abstract class BaseEntity
{
    abstract public static function fromActiveRow(Table\ActiveRow $row): static;

    /**
     * @param Table\Selection<Table\ActiveRow> $selection
     * @return list<static>
     */
    public static function fromSelection(Table\Selection $selection): array
    {
        return array_values(array_map(
            fn(Table\ActiveRow $row) => static::fromActiveRow($row),
            $selection->fetchAll(),
        ));
    }
}
