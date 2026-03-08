<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Migration;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Util\Database\TableHelper;

trait ColumnExistsTrait
{
    /**
     * @param non-empty-string $table
     */
    protected function columnExists(Connection $connection, string $table, string $column): bool
    {
        return TableHelper::columnExists($connection, $table, $column);
    }
}
