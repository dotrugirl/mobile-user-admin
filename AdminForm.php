<?php
namespace Sudo\Form;

 use Zend\Form\Form;
 use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Element;
		
 class AdminForm extends Form implements ServiceLocatorAwareInterface
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
     
     public function init()
     {
         $sm = $this->getServiceLocator();
         $roles = $sm->get('RoleTable')->fetchAll();
         
         $rolesChecboxes = new Element\MultiCheckbox('role');
         $rolesChecboxes->setLabel('Roles');
         
         $currentObject = $this->getObject();
         $currentRoles = array();
         
         //заполняем значения ролей
         if (!empty($this->data) && !empty($this->data['role'])) // если форма уже обрабатывается и есть ошибки
         {
             foreach ($this->data['role'] as $ra)
             {
                 $currentRoles[] = $ra;
             }
         }
         elseif (!empty($currentObject)) // если есть объект админа, то есть происходит редактирование админа
         {
             // снимаем обязательность с паролей, если у нас есть объект
             $this->get('passwd')->setAttribute('required', false);
             $this->get('confirm_password')->setAttribute('required', false);
             
             $sid = $currentObject->id;
             $currentRolesObject = $sm->get('UserAdminRoleTable')->getRolesForAdmin($sid);
             
             foreach ($currentRolesObject as $ro)
             {
                 $currentRoles[] = $ro->roleId;
             }
         }
         
         if (!empty($currentRoles))
         {
            $rolesChecboxes->setAttributes(array('value' => $currentRoles));
         }
         
         $valueOptions = array();
         foreach ($roles as $role)
         {
             $valueOptions[] = array(
                    'value' => $role->sid,
                    'label' => $role->title,
                    'attributes' => array('id' => 'role-' . $role->sid),
                    'label_attributes' => array(
                         'class' => 'checkbox inline col-sm-6',
                         'for' => 'role-' . $role->sid,
                    ),
             );
         }
         $rolesChecboxes->setValueOptions($valueOptions);
         $this->add($rolesChecboxes);
         
     }
     
     public function __construct($name = 'admin')
     {
         parent::__construct($name);
         
         $this->add(array(
                 'name' => 'active',
                 'type' => 'Checkbox',
                 'options' => array(
                         'label' => 'Active',
                         'checked_value' => 1,
                         'unchecked_value' => 0,
                 )
         ));
         
         $this->add(array(
                 'name' => 'email',
                 'type'  => 'email',
                 'options' => array(
                         'label' => 'Email',
                 ),
                 'attributes' => array(
                         'required' => true,
                         'class' => 'form-control',
                         'placeholder' => 'Email'
                 ),
                 'filters' => array(
                         array('name' => 'StringTrim'),
                 ),
         ));
         
         $this->add(array(
                 'name' => 'last_name',
                 'type'  => 'Text',
                 'options' => array(
                         'label' => 'Last Name',
                 ),
                 'attributes' => array(
                         'class' => 'form-control',
                         'placeholder' => 'Last Name',
                         'required' => true
                 ),
                 'filters' => array(
                         array('name' => 'StringTrim'),
                 ),
         ));
         $this->add(array(
                 'name' => 'first_name',
                 'type'  => 'Text',
                 'options' => array(
                         'label' => 'First Name',
                 ),
                 'attributes' => array(
                         'class' => 'form-control',
                         'placeholder' => 'First Name',
                         'required' => true
                 ),
                 'filters' => array(
                         array('name' => 'StringTrim'),
                 )
         ));
          
          
         $this->add(array(
                 'name' => 'passwd',
                 'type'  => 'password',
                 'options' => array(
                         'label' => 'Password',
                 ),
                 'attributes' => array(
                         'required' => true,
                         'class' => 'form-control',
                         'placeholder' => 'Password'
                 ),
                 'filters' => array(
                         array('name' => 'StringTrim'),
                 )
         ));
         
         $this->add(array(
                 'name' => 'confirm_password',
                 'type'  => 'password',
                 'options' => array(
                         'label' => 'Confirm Password',
                 ),
                 'attributes' => array(
                         'required' => true,
                         'class' => 'form-control',
                         'placeholder' => 'Confirm Password'
                 ),
                 'filters' => array(
                         array('name' => 'StringTrim'),
                 )
         ));
          
         $this->add(array(
                 'name' => 'submit',
                 'type' => 'Submit',
                 'attributes' => array(
                         'value' => 'Create',
                         'id' => 'submitbutton',
                         'class' => 'btn btn-primary'
                 ),
         ));
          
         $this->add(array(
                 'name' => 'cancel',
                 'type' => 'Button',
                 'options' => array(
                         'label' => 'Cancel',
                 ),
                 'attributes' => array(
                         'id' => 'exit',
                         'class' => 'btn btn-default btn-left'
                 ),
         ));
     }
 }