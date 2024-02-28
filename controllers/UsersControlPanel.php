<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Controllers;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Controllers\ControlPanelApiController;

use Arikaim\Core\Controllers\Traits\Status;
use Arikaim\Core\Controllers\Traits\SoftDelete;
use Arikaim\Core\Controllers\Traits\FileUpload;
use Arikaim\Core\Controllers\Traits\FileDownload;

/**
 * Users control panel api controler
*/
class UsersControlPanel extends ControlPanelApiController
{
    use Status,
        FileUpload,
        FileDownload,
        SoftDelete;

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('users::admin.messages');
        $this->setModelClass('Users');
        $this->setExtensionName('core');
    }

    /**
     * Add user
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function addController($request, $response, $data) 
    {       
        $this->onDataValid(function($data) { 
            $userName = $data->getString('user_name',null);
            $password = $data->getString('password',null);
            $email = $data->getString('email',null);

            $user = Model::Users()->createUser($userName,$password,$email);
            if (\is_object($user) == true) {
                $userDetails = $data->toArray();
                $typeId = $data->get('type_id',null);
                $userDetails['type_id'] = (empty($typeId) == true) ? null : $typeId;
        
                $details = Model::UserDetails('users')->saveDetails($user->id,$userDetails);
                $result = (\is_object($details) == true || $details !== false);

                $this->setResponse($result,function() use($user) {                  
                    $this
                        ->message('add')
                        ->field('uuid',$user->uuid);                  
                },'errors.add');
                return;
            } 
            $this->error('errors.add');           
        });
        $data                  
            ->addRule('regexp:exp=/^[A-Za-z][A-Za-z0-9]{4,32}$/|required','user_name',$this->getMessage('errors.username.valid'))
            ->addRule('text:min=4|required','password')
            ->addRule('unique:model=Users|field=email|required','email',$this->getMessage('errors.email.exist'))
            ->addRule('unique:model=Users|field=user_name|required','user_name',$this->getMessage('errors.username.exist'))
            ->addRule('equal:value=' . $data->get('password'),'repeat_password')
            ->validate();       
    }
   
    /**
     * Change user password
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function changePasswordController($request, $response, $data) 
    {
        $this->onDataValid(function($data) { 
            $password = $data->get('password');
            $user = Model::Users()->findById($data->get('uuid'));

            $result = $user->changePassword($user->id,$password);
            
            $this->setResponse($result,function() use($user) {                  
                $this
                    ->message('password')
                    ->field('uuid',$user->uuid);                  
            },'errors.password');
        });

        $repeat_password = $data->get('repeat_password');
        $data
            ->addRule('exists:model=Users|field=uuid','uuid')
            ->addRule('text:min=4|required','repeat_password')
            ->addRule('text:min=4|required','password')
            ->addRule('equal:value=' . $repeat_password . '|required','password','Password and repeat password does not match.')
            ->validate();       
    }

    /**
     * Update user details
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function updateController($request, $response, $data) 
    {  
        $user = Model::Users()->findById($data->get('uuid'));

        $this->onDataValid(function($data) use($user) {
            // save user 
            $data['type_id'] = (empty($data->get('type_id') == true)) ? 1 : $data->get('type_id');
            $userName = $data->getString('user_name',null);
            $email = $data->getString('email',null);
            
            if (empty($userName) == true && empty($email) == true) {
                $this->error('Email Or Username is required');
                return false;
            }

            $result = $user->update([
                'user_name' => $userName,
                'email'     => $email
            ]);
        
            // save user details
            $result = Model::UserDetails('Users')->saveDetails($user->id,$data->toArray());
            $this->setResponse((\is_object($result) == true || $result !== false),function() use($user) {                  
                $this
                    ->message('update')
                    ->field('uuid',$user->uuid);                  
            },'errors.update');                     
        });        
        $data
            ->addRule('exists:model=Users|field=uuid','uuid')
            ->addRule('text:min=2','first_name')
            ->addRule('unique:model=Users|field=user_name|exclude=' . $user->user_name,'user_name','Username exist')
            ->addRule('unique:model=Users|field=email|exclude=' . $user->email,'email','Email exist')
            ->validate();      
    }

    /**
     * Get users list
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function getList($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {          
            $search = $data->get('query','');
            $dataField = $data->get('data_field','uuid');
            $size = $data->get('size',15);
            
            $model = Model::Users()->getNotDeletedQuery();
            $model = $model
                ->where('user_name','like','%' . $search . '%')
                ->orWhere('email','like','%' . $search . '%')->take($size)->get();
          
            $this->setResponse(\is_object($model),function() use($model,$dataField) {     
                $items = [];
                foreach ($model as $item) {
                    $name = (empty($item['user_name']) == true) ? $item['email'] : $item['user_name'];
                    $items[] = [
                        'name' => $name,
                        'value' => $item[$dataField]
                    ];
                }
                $this                    
                    ->field('success',true)
                    ->field('results',$items);  
            },'errors.list');
        });
        $data->validate();

        return $this->getResponse(true); 
    }

    /**
     * Empty trash
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function emptyTrashController($request, $response, $data)
    {
        $accessTokens = Model::AccessTokens();
        $userDetails = Model::UserDetails('users');

        $users = Model::Users()->softDeletedQuery()->get();
    
        foreach ($users as $user) {
            // dispatch event user.before.delete
            $this->get('event')->dispatch('user.before.delete',$user->toArray());
            // delete tokens
            $accessTokens->deleteUserToken($user->id,null);
            $userDetails->deleteUserDetails($user->id);
            $user->deleteUser();
        }

        $this->message('trash_empty');
    }
}
