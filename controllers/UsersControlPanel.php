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
use Arikaim\Core\Controllers\ApiController;

use Arikaim\Core\Controllers\Traits\Status;
use Arikaim\Core\Controllers\Traits\SoftDelete;

/**
 * Users control panel api controler
*/
class UsersControlPanel extends ApiController
{
    use Status,
        SoftDelete;

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
     * Constructor
     */
    public function __construct($container) 
    {
        parent::__construct($container);
        $this->setModelClass('Users');
        $this->setExtensionName(null);
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
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) { 
            $user = Model::Users()->createUser($data['user_name'],$data['password'],$data['email']);
            if (is_object($user) == true) {
                $result = Model::UserDetails('users')->saveDetails($user->id,$data->toArray());
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
            ->addRule('text:min=2|required','user_name')
            ->addRule('text:min=4|required','password')
            ->addRule('unique:model=Users|field=email|required','email')
            ->addRule('unique:model=Users|field=user_name|required','user_name')
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
        $this->requireControlPanelPermission();
        
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
            ->addRule('equal:value=' . "$repeat_password|required",'password','Password and repeat password does not match.')
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
        $this->requireControlPanelPermission();
        
        $user = Model::Users()->findById($data->get('uuid'));

        $this->onDataValid(function($data) use($user) {
            // save user 
            $result = $user->update($data->toArray());
            
            if ($result !== false) {
                // save user details
                $result = Model::UserDetails('Users')->saveDetails($user->id,$data->toArray());
                $this->setResponse($result,function() use($user) {                  
                    $this
                        ->message('update')
                        ->field('uuid',$user->uuid);                  
                },'errors.update');
                return;
            }
            $this->error('errors.update');            
        });        
        $data
            ->addRule('exists:model=Users|field=uuid','uuid')
            ->addRule('text:min=2','first_name')
            ->addRule('unique:model=Users|field=user_name|required|exclude=' . $user->user_name,'user_name','Username exist')
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
            $size = $data->get('size',15);
            $model = Model::Users()->where('user_name','like',"%$search%")->take($size)->get();
          
            $this->setResponse(is_object($model),function() use($model) {     
                $items = [];
                foreach ($model as $item) {
                    $items[] = ['name' => $item['user_name'],'value' => $item['uuid']];
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
        $this->requireControlPanelPermission();

        $accessTokens = Model::AccessTokens();
        $userDetails = Model::UserDetails('users');

        $users = Model::Users()->softDeletedQuery()->get();

        foreach ($users as $user) {
            // delete tokens
            $accessTokens->deleteUserToken($user->id,null);
            $userDetails->where('user_id','=',$user->id)->delete();
            $user->delete();
        }
    }
}
