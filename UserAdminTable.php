<?php
namespace Sudo\Model;

use Zend\Db\TableGateway\TableGateway;

/**
 * 
 * Модель предназначена для работы с таблицей userAdmin
 * удаление данных из таблице влечет за собой удаление строки в базе
 *
 */
class UserAdminTable
{
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    
    public function fetchAll() 
    {
        return $this->tableGateway->select();
    } 
    
    public function getAdmin($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
    public function saveAdmin(UserAdmin $admin)
    {
        $data = $admin->getArrayCopy();
        if (empty($data['id']))
        {
            //insert
            try
            {
                $connection = $this->tableGateway->getAdapter()->getDriver()->getConnection();
                $connection->beginTransaction();
            
                $this->tableGateway->insert($data);
                $id = $this->tableGateway->lastInsertValue;
            
                if (empty($id))
                {
                    return $error = array('code' => 500, 'details' => 'Admin was not created');
                }
            
                $connection->commit();
                return $id;
            }
            catch (\Exception $e) {
                if ($connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface) {
                    $connection->rollback();
                }
            
                return $error = array('code' => 500, 'details' => $e->getMessage());
            }
        }
        else 
        {
            //update
            if ($this->getAdmin($data['id'])) 
            {
                try
                {
                    $connection = $this->tableGateway->getAdapter()->getDriver()->getConnection();
                    $connection->beginTransaction();
                
                    $this->tableGateway->update($data, array('id' => $data['id']));
                    $connection->commit();
                    return true;
                }
                catch (\Exception $e) {
                    if ($connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface) {
                        $connection->rollback();
                    }
                
                    return $error = array('code' => 500, 'details' => $e->getMessage());
                }
            } else 
            {
                return $error = array('code' => 500, 'details' => 'Admin does not exist');
            }
        }
        
    }
    
    public function deleteAdmin($id)
    {
        try
        {
            $this->tableGateway->delete(array('id' => (int) $id));
            return true;
        }
        catch (\Exception $e) {
            return $error = array('code' => 500, 'details' => $e->getMessage());
        }
    }
    
}