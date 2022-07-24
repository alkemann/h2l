<?php declare(strict_types=1);

namespace alkemann\h2l\interfaces;

/**
 * Interface Source
 *
 * @package alkemann\h2l\interfaces
 */
interface Source
{
    public function __construct(array $config = []);

    /**
     * @param mixed $query
     * @param array $params
     */
    public function query($query, array $params = []);

    /**
     * @param string $table
     * @param array $conditions
     * @param array $options
     * @return null|array
     */
    public function one(string $table, array $conditions, array $options = []): ?array;

    /**
     * @param string $table
     * @param array $conditions
     * @param array $options
     */
    public function find(string $table, array $conditions, array $options = []);

    /**
     * @param string $table
     * @param array $conditions
     * @param array $data
     * @param array $options
     */
    public function update(string $table, array $conditions, array $data, array $options = []);

    /**
     * @param string $table
     * @param array $data
     * @param array $options
     */
    public function insert(string $table, array $data, array $options = []);

    /**
     * @param string $table
     * @param array $conditions
     * @param array $options
     */
    public function delete(string $table, array $conditions, array $options = []);
}
