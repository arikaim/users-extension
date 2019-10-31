<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2016-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Controllers;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Arikaim;

/**
 * Users control panel api controler
*/
class UsersControlPanel extends ApiController
{
    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('users::admin.messages');
    }

    /**
     * Add user
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function add($request, $response, $data) 
    {       
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) { 
            $user = Model::Users()->createUser($data['user_name'],$data['password'],$data['email']);
            if (is_object($user) == true) {
                $result = Model::UserDetails('users')->saveDetails($user->id,$data->toArray());
                if ($result != false) {
                    return $this->setResult(['message' => 'User created']);
                } 
            } 
            $this->setError('Error add user');           
        });

        $data
            ->addRule('user_name','text:min=2|required')
            ->addRule('password','text:min=4|required')
            ->addRule('email','unique:model=Users|field=email|required','Email exist')
            ->addRule('user_name','unique:model=Users|field=user_name|required','Username exist')
            ->addRule('repeat_password','equal:value=' . $data->get('password'))
            ->validate();

        return $this->getResponse();   
    }

    /**
     * Change user password
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function changePassword($request, $response, $data) 
    {
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) { 
            $password = $data->get('password');
            $model = Model::Users();           
            
            $result = $model->changePassword($data->get('uuid'),$password);
            if ($result !== false) {
                $this->setResult(['message' => 'Password changed.']);
            } else {
                $this->setError('Error change password');
            }
        });

        $repeat_password = $data->get('repeat_password');
        $data
            ->addRule('uuid','exists:model=Users|field=uuid')
            ->addRule('repeat_password','text:min=4|required')
            ->addRule('password','text:min=4|required')
            ->addRule('password','equal:value=' . "$repeat_password|required",'Password and repeat password does not match.')
            ->validate();
        
        return $this->getResponse();   
    }

    public function update($request, $response, $data) 
    { 
        $this->requireControlPanelPermission();
        
        $user = Model::Users()->findById($data->get('uuid'));

        $this->onDataValid(function($data) use($user)  { 
            // save user 
            $result = $user->update($data->toArray());
            // saev user details
            $result = Model::UserDetails('Users')->saveDetails($user->id,$data->toArray());

            $this->setResult(['message' => 'Chnages saved','data' => $data->toArray()]);           
        });
        
        $data
            ->addRule('uuid','exists:model=Users|field=uuid')
            ->addRule('first_name','text:min=2')
            ->addRule('user_name','unique:model=Users|field=user_name|required|exclude=' . $user->user_name,'Username exist')
            ->addRule('email','unique:model=Users|field=email|exclude=' . $user->email,'Email exist')
            ->validate();

        return $this->getResponse();   
    }

    /**
     * Delete user
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return void
     */
    public function delete($request, $response, $data)
    { 
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) { 
            $uuid = $data->get('uuid');
            $result = Model::Users()->remove($uuid);          
        });

        $data->addRule('uuid','exists:model=Users|field=uuid');
        $data->validate();

        return $this->getResponse();     
    }
    
    /**
     * Set user status
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return void
     */
    public function setStatus($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) { 
            $model = Model::Users()->findById($data->get('uuid'));
            if ($model->isControlPanelUser() == true) {
                $this->setError("Acces denied");
            } else {
                $result = $model->setStatus($data->get('status'));
                $this->setResult(['message' => 'Status changed', 'status' => $data->get('status'), 'uuid' => $data->get('uuid')]);
            }
        });

        $data
            ->addRule('uuid','exists:model=Users|field=uuid')          
            ->validate();

        return $this->getResponse();   
    }

    public function read($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {            
            $details = Model::UserDetails('users')->getDetails($data->get('uuid'));
            $this->setResult($details);
        });

        $data
            ->addRule('uuid','exists:model=Users|field=uuid')        
            ->validate();

        return $this->getResponse();  
    }
}
