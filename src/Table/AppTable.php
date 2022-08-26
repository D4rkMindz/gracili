<?php

namespace App\Table;

use Cake\Database\Connection;
use Cake\Database\Query;
use Cake\Database\StatementInterface;
use LogicException;
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
     * @param Connection|null $connection
     */
    public function __construct(?Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the table name
     *
     * @return string
     */
    public static function getName(): string
    {
        $class = get_called_class();
        $self = new $class(null);

        return $self->table;
    }

    /**
     * Recursify an array
     *
     * @param array       $array
     * @param string|null $delimiter
     *
     * @return array
     */
    public static function recursify(array $array, ?string $delimiter = '-'): array
    {
        $new = [];
        foreach ($array as $key => $value) {
            if (!str_contains($key, $delimiter)) {
                $new[$key] = is_array($value) ? self::recursify($value, $delimiter) : $value;
                continue;
            }

            $segments = explode($delimiter, $key);
            $last = &$new[$segments[0]];
            if (!is_null($last) && !is_array($last)) {
                throw new LogicException(
                    sprintf(
                        "The '%s' key has already been defined as being '%s'",
                        $segments[0],
                        gettype($last)
                    )
                );
            }

            foreach ($segments as $k => $segment) {
                if ($k != 0) {
                    $last = &$last[$segment];
                }
            }
            $last = is_array($value) ? self::recursify($value, $delimiter) : $value;
        }

        return $new;
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
     * Archive all by a specific query
     *
     * @param array $where = ['user_id' => 0]
     * @param       $executorId
     *
     * @return int The count of archived record
     */
    public function archiveAll(array $where, $executorId)
    {
        $query = $this->newSelect();
        $query->select(['id'])
            ->where($where);
        $q = $query->execute();
        $count = $q->rowCount();
        $result = $q->fetchAll('assoc');

        foreach ($result as $record) {
            $this->archive($record['id'], $executorId);
        }

        return $count;
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
     * @param array      $row
     * @param array      $where
     * @param int|null   $executorId
     * @param array|null $types
     *
     * @return bool
     */
    public function update(array $row, array $where = ['id' => 1], ?int $executorId = 0, ?array $types = []): bool
    {
        $now = new Moment();
        $row = array_merge($row, [
            'modified_at' => $now->format('Y-m-d H:i:s'),
            'modified_by' => $executorId,
        ]);
        $query = $this->connection->newQuery();
        $query->update($this->table)
            ->set($row, $types)
            ->where($where);

        return (bool)$query->execute();
    }

    /**
     * Delete all by a specific query
     *
     * USE WITH CAUTION! Should only be used on data delete requests
     *
     * @param array $where = ['user_id' => 0]
     *
     * @return int The count of archived record
     */
    public function deleteAll(array $where): int
    {
        $query = $this->newSelect();
        $query->select(['id'])
            ->where($where);
        $q = $query->execute();
        $count = $q->rowCount();
        $result = $q->fetchAll('assoc');

        foreach ($result as $record) {
            $this->delete($record['id']);
        }

        return $count;
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
}
