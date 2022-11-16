<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Jobs;

use Arikaim\Core\Queue\Jobs\Job;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\Job\JobInterface;
use Arikaim\Core\Utils\Text;

/**
* User signup action
*/
class SignupAction extends Job implements JobInterface
{
    /**
     * Signup action api
     *
     * @Api(
     *      description="User signup",    
     *      parameters={
     *          @ApiParameter (name="name",type="string",required=true,description="Action name"),
     *          @ApiParameter (name="secret",type="string",required=false,description="Secret key"),
     *          @ApiParameter (name="user_name",type="string",required=true,description="User name"),
     *          @ApiParameter (name="email",type="string",required=true,description="User email address"),
     *      }
     * )
     * 
     * @ApiResponse(
     *      fields={
     *          @ApiParameter (name="uuid",type="string",description="User uuid"),
     *          @ApiParameter (name="status",type="integer",description="User status"),
     *      }
     * )
     * 
     * Run signup action job
     *
     * @return mixed
     */
    public function execute()
    {      
        global $container;

        $email = $this->getParam('email');
        $userName = $this->getParam('user_name');
        if (empty($email) == true || empty($userName) == true) {
            return [
                'error' => 'Not valid email or user name.'
            ]; 
        }
        
        $model = Model::Users();

        // verify username
        if ($model->hasUserName($userName) == true) { 
            return [
                'error' => 'errors.username.exist'
            ];             
        }
       
        // verify email
        if ($model->hasUserEmail($email) == true) {               
            return [
                'error' => 'errors.email.exist'
            ];
        }
        
        $password = Text::createToken(62);
        $user = $model->createUser($userName,$password,$email);
       
        if (\is_object($user) == false) {   
            return [
                'error' => 'errors.signup'
            ];       
        } 
       
        // dispatch event   
        $container->get('event')->dispatch('user.signup',$user->toArray());

        return $user->toArray();
    }
}
