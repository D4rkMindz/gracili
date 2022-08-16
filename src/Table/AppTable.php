<?php

namespace App\Table;

use Cake\Database\Connection;
use Cake\Database\Query;
use Cake\Database\StatementInterface;
use Moment\Moment;

/**
 * Class AppTable.
 */
abstract class AppTable implements TableInterface
{
    protected ?string $table = null;
    protected ?Connection $connection = null;

    /**
     * AppTable constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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

        return $query->execute()->fetchAll('assoc');
    }

    /**
     * Get Query.
     *
     * @return Query
     */
    public function newSelect(): Query
    {
        $query = $this->connection->newQuery()->from($this->table);
        $date = new Moment();
        $date->addMinutes(1);

        return $query->where([
            'OR' => [
                [$this->table . '.archived_at IS NULL'],
                [$this->table . '.archived_at >=' => $date->format('Y-m-d H:i:s')],
            ],
        ]);
    }

    /**
     * Insert into database.
     *
     * @param array $row with data to insertUser into database
     * @param int   $executorId
     * @param array $types
     *
     * @return StatementInterface
     */
    public function insert(array $row, int $executorId = 0, array $types = []): StatementInterface
    {
        $now = new Moment();
        $row = array_merge($row, [
            'created_at' => $now->format('Y-m-d H:i:s'),
            'created_by' => $executorId,
            'modified_at' => $now->format('Y-m-d H:i:s'),
            'modified_by' => $executorId,
        ]);


        return $this->connection->insert($this->table, $row, $types);
    }

    /**
     * Hard Delete from database.
     *
     * @param string $id
     *
     * @return bool
     *
     * @see AppTable::archive
     */
    public function delete(string $id): bool
    {
        return (bool)$this->connection->delete($this->table, ['id' => $id]);
    }

    /**
     * Archive from database (soft delete).
     *
     * @param int $id
     * @param int $executorId
     *
     * @return bool
     *
     * @see AppTable::delete
     */
    public function archive(int $id, int $executorId)
    {
        $now = new Moment();
        $row = [
            'archived_at' => $now->format('Y-m-d H:i:s'),
            'archived_by' => $executorId,
        ];

        $query = $this->connection->newQuery();
        $query->update($this->table)
            ->set($row)
            ->where(['id' => $id]);

        return (bool)$query->execute();
    }

    /**
     * Update database.
     *
     * @param array $row
     * @param array $where
     * @param int   $executorId
     *
     * @return bool
     */
    public function update(array $row, array $where = ['id' => 1], int $executorId = 0): bool
    {
        $now = new Moment();
        $row = array_merge($row, [
            'modified_at' => $now->format('Y-m-d H:i:s'),
            'modified_by' => $executorId,
        ]);
        $query = $this->connection->newQuery();
        $query->update($this->table)
            ->set($row)
            ->where($where);

        return (bool)$query->execute();
    }

    /**
     * Check if a record exists.
     *
     * @param array|null $where
     *
     * @return bool true if the record exists
     */
    public function exist(?array $where = ['id' => 1])
    {
        $query = $this->newSelect();
        $query->select([1])->andWhere($where);
        $row = $query->execute()->fetch();

        return !empty($row);
    }
}
