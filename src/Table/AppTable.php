<?php

namespace App\Table;

use Cake\Database\Connection;
use Cake\Database\Query;
use Cake\Database\StatementInterface;
use DateTime;

/**
 * Class AppTable.
 */
abstract class AppTable implements ModelInterface
{
    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var Connection
     */
    protected $connection = null;

    /**
     * AppTable constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * Get Query.
     *
     * @param bool $forTableWithMetaInformation Indicator if the table contains the meta fields created_at, created_by,
     *                                          modified_at, modified_by, archived_at, archived_by
     * @return Query
     */
    public function newSelect(bool $forTableWithMetaInformation = true): Query
    {
        $query = $this->connection->newQuery()->from($this->table);
        if ($forTableWithMetaInformation) {
            $date = new DateTime();
            $date->modify('+60 seconds');
            $query->where(['OR' => [['archived_at IS' => null], ['archived_at >=' => $date->format('Y-m-d H:i:s')]]]);
        }
        return $query;
    }

    /**
     * Get all entries from database.
     *
     * @return array $rows
     */
    public function getAll(): array
    {
        $query = $this->newSelect();
        $query->select('*');
        $rows = $query->execute()->fetchAll('assoc');

        return $rows;
    }

    /**
     * Insert into database.
     *
     * @param array $row with data to insertUser into database
     *
     * @return StatementInterface
     */
    public function insert(array $row): StatementInterface
    {
        return $this->connection->insert($this->table, $row);
    }

    /**
     * Update database.
     *
     * @param array $row
     * @param array $where
     * @return StatementInterface
     */
    public function update(array $row, array $where = ['id' => 1]): bool
    {
        $query = $this->connection->newQuery();
        $query->update($this->table)
            ->set($row)
            ->where($where);

        return (bool)$query->execute();
    }

    /**
     * Hard Delete from database.
     *
     * @see AppTable::archive
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return (bool)$this->connection->delete($this->table, ['id' => $id]);
    }

    /**
     * Archive from database (soft delete)
     *
     * @see AppTable::delete
     *
     * @param string $id
     * @param string $executorId
     * @return bool
     */
    public function archive(string $id, string $executorId)
    {
        $row = [
            'archived_at' => date('Y-m-d H:i:s'),
            'archived_by' => $executorId,
        ];

        $query = $this->connection->newQuery();
        $query->update($this->table)
            ->set($row)
            ->where(['id' => $id]);

        return (bool)$query->execute();
    }

    /**
     * Check if a record exists.
     *
     * @param array $where
     * @return bool true if the record exists
     */
    public function exist(array $where = ['id' => 1])
    {
        $query = $this->newSelect();
        $query->select(1)->where($where);
        $row = $query->execute()->fetch();
        return !empty($row);
    }
}
