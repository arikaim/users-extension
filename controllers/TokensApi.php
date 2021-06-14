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
use Arikaim\Core\Http\Url;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Http\Cookie;
use Arikaim\Core\Http\Session;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Text;
use Arikaim\Core\View\Html\Page;

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
        $this->onDataValid(function($data)  {  
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
                $this->error('errors.tokens.password');
                return false;  
            }
            $tokens = Model::AccessTokens();

            if ($tokens->hasToken($userId,$type) == true) {
                if ($reCreate == false) {
                    $this->error('errors.tokens.exist');
                    return false;  
                }
                $tokens->deleteUserToken($userId,$type);
            }

            $token = Model::AccessTokens()->createToken($userId,$type,$expireTime,false);

            $this->setResponse(\is_array($token),function() use ($token,$user) {  
                $this
                    ->message('tokens.create')
                    ->field('uuid',$token['uuid'])
                    ->field('user_uuid',$user->uuid)
                    ->field('type',$token['type']);                                          
            },function() {    
                $this->error('errors.tokens.create');                                                               
            }); 

        });
        $data
            ->addRule('text:min=2|required','password')
            ->validate();               
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
        $this->onDataValid(function($data)  {  
            $uuid = $data->get('uuid');
            $userId = $this->getUserId();

            if (empty($userId) == true) {
                $this->error('errors.id');
                return false;  
            }
            $user = Model::Users()->findById($userId);
          
            $token = Model::AccessTokens()->findById($uuid);
            if (\is_object($token) == false) {
                $this->error('errors.tokens.id');
                return false;  
            }

            if ($token->user_id != $userId) {
                $this->error('errors.access');
                return false;  
            }

            $result = $token->delete();
          
            $this->setResponse($result,function() use ($token,$user) {  
                $this
                    ->message('tokens.delete')
                    ->field('uuid',$token->uuid)
                    ->field('user_uuid',$user->uuid)
                    ->field('type',$token->type);                                          
            },function() {    
                $this->error('errors.tokens.delete');                                                               
            }); 

        });
        $data
            ->addRule('text:min=2|required','uuid')
            ->validate();               
    }

}
