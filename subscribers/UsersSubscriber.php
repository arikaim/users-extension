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
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\Model;

/**
 * Execute post signup, login actions 
*/
class UsersSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->subscribe('user.signup','signup');
        $this->subscribe('user.login','login');
        $this->subscribe('user.logout','logout');
    }

    /**
     * Run post signup action
     *
     * @param EventInterface $event
     * @return void
     */
    public function signup($event)
    {
        $user = $event->getParameters(); 
        $sendWelcomeEmail = Arikaim::options()->get('users.notifications.email.welcome',false);
        $adminNotification = Arikaim::options()->get('users.notifications.email.signup',false);
        
        if (($sendWelcomeEmail == true) && (Utils::isEmail($user['email']) == true)) {
            // send welcome email to user
            Arikaim::mailer()->create()
                ->loadComponent('users>users.emails.welcome',$user)
                ->to($user['email'])
                ->send();
        }

        if ($adminNotification == true) {
            $adminUser = Model::Users()->getControlPanelUser();
            if (Utils::isEmail($adminUser->email) == true) {
                // send email to admin
                Arikaim::mailer()->create()
                    ->loadComponent('users>users.emails.signup',$user)
                    ->to($adminUser->email)
                    ->send();
            }
        }
    }

    /**
     * Run post login action
     *
     * @param EventInterface $event
     * @return void
     */
    public function login($event)
    {
         // your code here
    }

    /**
     * Run post logout action
     *
     * @param EventInterface $event
     * @return void
     */
    public function logout($event)
    {
        // your code here
    }
}
