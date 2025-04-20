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

use Arikaim\Core\Access\Interfaces\AuthTokensInterface;
use Arikaim\Core\Controllers\Traits\AccessToken;
use Arikaim\Core\Controllers\Traits\Captcha;
use Arikaim\Extensions\Users\Controllers\Traits\Users;

/**
 * Tokens api controller
*/
class TokensApi extends ApiController
{
    use 
        AccessToken,
        Users,
        Captcha;

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('users>users.messages');
    }

    /**
     * Create token
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return mixed
    */
    public function create($request, $response, $data)
    {       
        $data
            ->validate(true);       

        $password = $data->get('password');
        $type = $data->get('type',AuthTokensInterface::API_ACCESS_TOKEN);
        $defaultExpireTime = ($type == AuthTokensInterface::API_ACCESS_TOKEN) ? -1 : 10000;
        $expireTime = $data->get('expire_time',$defaultExpireTime);
        $reCreate = $data->get('recreate',false);
        $userId = $this->getUserId();

        if (empty($userId) == true) {
            $this->error('Access denied');
            return false;  
        }

        $user = Model::Users()->findById($userId);
        if (empty($password) == false) {
            if ($user->verifyPassword($password) == false) {
                $this->error('Not valid user password');
                return false;  
            }
        }
      
        $tokens = Model::AccessTokens();

        if ($tokens->hasToken($userId,$type) == true) {
            if ($reCreate == false) {
                $this->error('Token exist error');
                return false;  
            }
            $tokens->deleteUserToken($userId,$type);
        }

        $token = Model::AccessTokens()->createToken($userId,$type,$expireTime,false);
        if (\is_array($token) == false) {
            $this->error('Error create access token.');  
        }
      
        $this
            ->message('token.create',"Token created.")
            ->field('uuid',$token['uuid'])
            ->field('user_uuid',$user->uuid)
            ->field('type',$token['type']);                                                               
    }

    /**
     * Delete token
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function deleteController($request, $response, $data)
    {       
        $data
            ->addRule('text:min=2|required','uuid')
            ->validate(true);    

        $uuid = $data->get('uuid');
        $userId = $this->getUserId();

        if (empty($userId) == true) {
            $this->error('errors.id');
            return false;  
        }
        $user = Model::Users()->findById($userId);
        
        $token = Model::AccessTokens()->findById($uuid);
        if ($token == null) {
            $this->error('errors.token.id','Not valid token id');
            return false;  
        }

        if ($token->user_id != $userId) {
            $this->error('errors.access');
            return false;  
        }

        $result = $token->delete();
        
        $this->setResponse($result,function() use ($token,$user) {  
            $this
                ->message('token.delete')
                ->field('uuid',$token->uuid)
                ->field('user_uuid',$user->uuid)
                ->field('type',$token->type);                                          
        },function() {    
            $this->error('errors.token.delete');                                                               
        });                   
    }
}
