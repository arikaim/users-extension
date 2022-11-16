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

use Arikaim\Core\Access\Interfaces\AutoTokensInterface;
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
     * @return Psr\Http\Message\ResponseInterface
    */
    public function createController($request, $response, $data)
    {       
        $data
            ->addRule('text:min=2|required','password')
            ->validate(true);       

        $password = $data->get('password');
        $type = $data->get('type',AutoTokensInterface::API_ACCESS_TOKEN);
        $expireTime = $data->get('expire_time',-1);
        $reCreate = $data->get('recreate',false);
        $userId = $this->getUserId();

        if (empty($userId) == true) {
            $this->error('errors.id');
            return false;  
        }

        $user = Model::Users()->findById($userId);
        if ($user->verifyPassword($password) == false) {
            $this->error('errors.token.password');
            return false;  
        }
        $tokens = Model::AccessTokens();

        if ($tokens->hasToken($userId,$type) == true) {
            if ($reCreate == false) {
                $this->error('errors.token.exist');
                return false;  
            }
            $tokens->deleteUserToken($userId,$type);
        }

        $token = Model::AccessTokens()->createToken($userId,$type,$expireTime,false);

        $this->setResponse(\is_array($token),function() use ($token,$user) {  
            $this
                ->message('token.create')
                ->field('uuid',$token['uuid'])
                ->field('user_uuid',$user->uuid)
                ->field('type',$token['type']);                                          
        },function() {    
            $this->error('errors.token.create');                                                               
        });                
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
