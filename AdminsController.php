<?php
namespace Sudo\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Sudo\Model\UserAdmin;
use Sudo\Model\UserAdminRole;

class AdminsController extends AbstractActionController
{
    protected $userAdminTable;
    protected $userAdminRoleTable;
    protected $translator;
   
    public function getAdminTable(){
        if (!$this->userAdminTable) {
            $sm = $this->getServiceLocator();
            $this->userAdminTable = $sm->get('UserAdminTable');
        }
        return $this->userAdminTable;
    }
    
    public function getUserAdminRoleTable()
    {
        if (!$this->userAdminRoleTable) {
            $sm = $this->getServiceLocator();
            $this->userAdminRoleTable = $sm->get('UserAdminRoleTable');
        }
        return $this->userAdminRoleTable;
    }
    
    protected function getTranslator()
    {
        if (!$this->translator)
        {
            $this->translator = $this->getServiceLocator()->get('Translator');
        }
        return $this->translator;
    }
    
    public function indexAction()
    {
        return new ViewModel(array(
            'UserAdmins' => $this->getAdminTable()->fetchAll(),
        ));
    }
    
    public function createAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')->get('AdminForm');
        
        // обрабатываем форму
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $userAdmin = new UserAdmin();
            $form->setInputFilter($userAdmin->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid())
            {
                // сохраняем данные админа
                $userAdmin->exchangeArray($form->getData());
                
                $result = $this->getAdminTable()->saveAdmin($userAdmin);
                
                if (is_array($result))
                {
                    throw new \Exception($this->getTranslator()->translate($result['details']), $result['code']);
                }
                if (!is_numeric($result))
                {
                    throw new \Exception($this->getTranslator()->translate('Please contact system administrator'), 500);
                }
                $adminId = $result;
                unset($result);
                
                // сохраняем роли администратора
                $roles = $this->params()->fromPost('role');
                // если не указаны роли, то переходим в список
                if (empty($roles))
                {
                    return $this->redirect()->toRoute('admins');
                }
                foreach ($roles as $roleId)
                {
                    $adminRole = new UserAdminRole();
                    $adminRole->roleId = $roleId;
                    $adminRole->adminId = $adminId;
                    
                    $result = $this->getUserAdminRoleTable()->saveAdminRole($adminRole);
                    
                    if (is_array($result))
                    {
                        throw new \Exception($this->getTranslator()->translate($result['details']), $result['code']);
                    }
                }
                return $this->redirect()->toRoute('admins');
            } //isValid
        } // isPost
        return new ViewModel(array('form' => $form));
    }
    
    public function editAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')->get('AdminForm');
        
        $sid = (int) $this->params()->fromRoute('id', 0);
        if (empty($sid))
        {
            return $this->redirect()->toRoute('admins', array('action' => 'create'));
        }
        
        // выбираем Админа по идентификатору
        // ловим ошибку отсутствия Админа и перенаправляем в список админов
        try
        {
            $currentAdmin = $this->getAdminTable()->getAdmin($sid);
        }
        catch (\Exception $ex)
        {
            return $this->redirect()->toRoute('admin');
        }
        // передаем данные в форму
        $form->bind($currentAdmin);
        $form->get('submit')->setAttribute('value', $this->getTranslator()->translate('Save'));
        
        // обрабатываем форму
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $userAdmin = new UserAdmin();
            
            $form->setInputFilter($userAdmin->getInputFilter());
            
            $formData = $request->getPost();
            // обновляем данные пароля
            if (empty($formData->passwd))
            {
                $formData->passwd = $currentAdmin->passwd;
                $formData->confirm_password = $currentAdmin->passwd;
            }
            $form->setData($formData);
            
            if ($form->isValid())
            {
                // сохраняем данные админа
                if (is_object($form->getData()))
                {
                    $userAdmin = $form->getData();
                }
                else 
                {
                    $userAdmin->exchangeArray($form->getData());
                }
                
                $userAdmin->id = $sid;
                $result = $this->getAdminTable()->saveAdmin($userAdmin);
                
                if (is_array($result))
                {
                    throw new \Exception($this->getTranslator()->translate($result['details']), $result['code']);
                }
                unset($result);
                
                // удаляем предыдущие связки админа ролей
                $this->getUserAdminRoleTable()->deleteRoleForAdmin($sid);
        
                // сохраняем роли администратора
                $roles = $this->params()->fromPost('role');
                // если не указаны роли, то переходим в список
                if (empty($roles))
                {
                    return $this->redirect()->toRoute('admins');
                }
                foreach ($roles as $roleId)
                {
                    $adminRole = new UserAdminRole();
                    $adminRole->roleId = $roleId;
                    $adminRole->adminId = $sid;
                
                    $result = $this->getUserAdminRoleTable()->saveAdminRole($adminRole);
                
                    if (is_array($result))
                    {
                        throw new \Exception($this->getTranslator()->translate($result['details']), $result['code']);
                    }
                }
                return $this->redirect()->toRoute('admins');
            } // isValid
            else 
            {
                // делаем поля для паролей необязательными
                $form->get('passwd')->setAttribute('required', false);
                $form->get('confirm_password')->setAttribute('required', false);
            }
        } // isPost
        
        return new ViewModel(array('form' => $form, 'sid' => $sid));
    }
    
    public function deleteAction()
    {
        $sid = (int) $this->params()->fromRoute('id', 0);
        if (empty($sid))
        {
            return $this->redirect()->toRoute('admins');
        }
        try
        {
            //удаление ролей админа
            $this->getUserAdminRoleTable()->deleteRoleForAdmin($sid);
            //удаление админа
            $this->getAdminTable()->deleteAdmin($sid);
        }
        catch (\Exception $ex)
        {
            throw new \Exception($this->getTranslator()->translate($ex->getMessage()), 500);
        }
    
        return $this->redirect()->toRoute('admins');
    }
}