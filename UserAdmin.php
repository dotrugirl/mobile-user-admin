<?php
namespace Sudo\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class UserAdmin implements InputFilterAwareInterface
{
    public $id;
    public $active;
    public $email;
    public $last_name;
    public $first_name;
    public $passwd;
    
    protected $inputFilter;
    
    public function getArrayCopy()
    {
        $data = array(
                'id' => $this->id,
                'active' => $this->active,
                'email' => $this->email,
                'last_name' => $this->last_name,
                'first_name' => $this->first_name,
                'passwd' => $this->passwd
        );
        return $data;
    }
    
    public function exchangeArray($data)
    {
        $this->id                = (!empty($data['id'])) ? $data['id'] : null;
        $this->active         = (!empty($data['active'])) ? $data['active'] : 0;
        $this->email          = (!empty($data['email'])) ? $data['email'] : null;
        $this->last_name  = (!empty($data['last_name'])) ? $data['last_name'] : null;
        $this->first_name  = (!empty($data['first_name'])) ? $data['first_name'] : null;
        
        if (!empty($data["passwd"]))
        {
            if ($this->is_sha1($data['passwd']))
            {
                $this->passwd = $data['passwd'];
            }
            else 
            {
                $this->setPassword($data["passwd"]);
            }
        }
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
     
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
             /*
            $inputFilter->add(array(
                    'name'     => 'id',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'Int'),
                    ),
            ));
             
            */
            
            $inputFilter->add(array(
                    'name'     => 'email',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                            array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min'      => 1,
                                            'max'      => 255,
                                    ),
                            ),
                            array(
                                    'name' => 'EmailAddress',
                                    'options' => array(
                                            'domain' => true,
                                            'messages' => array(
                                                    \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid',
                                                    'emailAddressInvalidHostname' => 'Email address format is invalid',
                                                    'hostnameUnknownTld' => 'Email address format is invalid',
                                                    'hostnameLocalNameNotAllowed' => 'Email address format is invalid',
                                            )
                                    )
                            )
                    ),
            ));
             
            $inputFilter->add(array(
                    'name'     => 'last_name',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                            array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min'      => 3,
                                            'max'      => 255,
                                    ),
                            ),
                            array(
                                    'name' => 'Alpha',
                                    'options' => array('allowWhiteSpace' => true),
                            )
                    ),
            ));
            
            $inputFilter->add(array(
                    'name'     => 'first_name',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                            array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min'      => 3,
                                            'max'      => 255,
                                    ),
                            ),
                                    array(
                                            'name' => 'Alpha',
                                            'options' => array('allowWhiteSpace' => true)
                                    )
                    ),
            ));
            
            $inputFilter->add(array(
                    'name'     => 'passwd',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                            array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min'      => 6,
                                            'max'      => 100,
                                    ),
                            ),
                    ),
            ));
            //TODO написать custom validator для проверки совпадения пароля
            $inputFilter->add(array(
                    'name'     => 'confirm_password',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                            array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min'      => 6,
                                            'max'      => 100,
                                    ),
                            ),
                    ),
            ));
            
            $this->inputFilter = $inputFilter;
        }
         
        return $this->inputFilter;
    }
    
    protected function setPassword($clear_password)
    {
        $this->passwd = sha1($clear_password);
    }
    
    protected function is_sha1($str) 
    {
        return (bool) preg_match('/^[0-9a-f]{40}$/i', $str);
    }
}
