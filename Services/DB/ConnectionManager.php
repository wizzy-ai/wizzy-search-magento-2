<?php
namespace Wizzy\Search\Services\DB;

use Magento\Framework\App\ResourceConnection;

class ConnectionManager
{

    private $connection;
    private $resource;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resource = $resourceConnection;
        $this->connection = $this->resource->getConnection();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getTableName($tableName)
    {
        return $this->resource->getTableName($tableName);
    }

    public function insertMultiple($tableName, $data, $updateDuplicates = true)
    {
        try {
            $tableName = $this->resource->getTableName($tableName);
            if ($updateDuplicates) {
                return $this->connection->insertOnDuplicate($tableName, $data);
            }
            return $this->connection->insertMultiple($tableName, $data);
        } catch (\Exception $exception) {
            return false;
        }
    }
}
