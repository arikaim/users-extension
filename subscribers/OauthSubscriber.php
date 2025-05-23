<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Subscribers;

use Arikaim\Core\Events\EventSubscriber;
use Arikaim\Core\Interfaces\Events\EventSubscriberInterface;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\Model;

/**
 * Execute oauth actions 
*/
class OauthSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->subscribe('oauth.auth','auth');
    }

    /**
     * Run auth action
     *
     * @param EventInterface $event
     * @return void
     */
    public function auth($event)
    {
        global $container;

        $data = $event->getParameters(); 
        $action = $data['action'] ?? null;
        $user = $data['user'];
        $userName = $user['user_name'] ?? '';
        $email = $user['email'] ?? '';

        $users = Model::Users();
        $tokens = Model::OauthTokens('oauth');
       
        // User signup
        if (empty($userName) == false || empty($email) == false) {
            $userFound = $users->getUser($userName,$email); 
            if ($userFound == false) {
                // create user
                $password = Utils::createRandomKey();
                $newUser = $users->createUser($userName,$password,$email);
                $userId = $newUser->id;            
            } else {
                $userId = $userFound->id;
            }

            $tokens->saveUserId($data['access_token'],$data['driver'],$userId);   
        }
       
        if ($action == 'login') {
            // login with oauth provider
            $container->get('access')->withProvider('oauth',$tokens)->authenticate([
                'token'  => $data['access_token'],
                'driver' => $data['driver']
            ]);
        }
    }
}
