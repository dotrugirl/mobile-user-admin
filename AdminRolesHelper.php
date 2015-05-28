<?php
namespace Sudo\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * View Helper вытаскивает идетнификаторы и название ролей, которые привязаны к администратору
 * 
 *
 */
class AdminRolesHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
     
    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
    }
     
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function __invoke($adminId)
    {
        $sm = $this->getServiceLocator()->getServiceLocator();
        $currentRoles = array();
        $currentRolesObject = $sm->get('UserAdminRoleTable')->getRolesForAdmin($adminId);
        $roleTable = $sm->get('RoleTable');
        
        foreach ($currentRolesObject as $ro)
         {
             $currentRoles[] = array(
                     'id' => $ro->roleId,
                     'title' => $roleTable->getRole($ro->roleId)->title
             );
         }
         
        return $currentRoles;
    }
}