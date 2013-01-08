<?php

class PgsqlQueue extends AbstractPdoQueue
{
    public function __construct(\PDO $conn, $tableName)
    {
        if ('pgsql' != $conn->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            throw new \InvalidArgumentException(sprintf('%s expects "pgsql" PDO driver, "%s" given.',
                __CLASS__, $conn->getAttribute(\PDO::ATTR_DRIVER_NAME)
            ));
        }

        parent::__construct($conn, $tableName);
    }

    /**
     * {@inheritdoc}
     *
     * @link http://stackoverflow.com/questions/6507475/job-queue-as-sql-table-with-multiple-consumers-postgresql
     */
    public function pop()
    {
        $sql = 'SELECT id FROM '.$this->tableName.' WHERE eta <= '.time().' ORDER BY eta, id LIMIT 1';
        $sql = 'DELETE FROM '.$this->tableName.' WHERE id = ('.$sql.') RETURNING item';

        $this->conn->beginTransaction();

        try {
            $this->exec('LOCK TABLE '.$this->tableName.' IN EXCLUSIVE MODE');
            $stmt = $this->query($sql);
            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }

        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result;
    }
}
