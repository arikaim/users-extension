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
class UserSignup extends Job implements JobInterface
{
    /**
     * Init job
     *
     * @return void
     */
    public function init(): void
    {
        $this->setName('user.signup');
    }

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
            $this->result()->error('Not valid email or user name.');
            return;
        }
        
        $model = Model::Users();

        // verify username
        if ($model->hasUserName($userName) == true) { 
            $this->result()->error('Username exists');
            return;         
        }
       
        // verify email
        if ($model->hasUserEmail($email) == true) {         
            $this->result()->error('Email are used.');      
            return;
        }
        
        $password = Text::createToken(62);
        $user = $model->createUser($userName,$password,$email);
       
        if (\is_object($user) == false) {   
            $this->result()->error('Error signup');
            return;    
        } 
       
        // dispatch event   
        $container->get('event')->dispatch('user.signup',$user->toArray());

        // set job result field
        $this->result()->field('user',$user->toArray());
    }

    /**
     * Init descriptor properties 
     *
     * @return void
     */
    protected function initDescriptor(): void
    {
        $this->descriptor->set('title','User signup');
        $this->descriptor->set('description','Create user with user name or email and auto generated password.');

        $this->descriptor->set('allow.admin.config',false);
        
        // properties
        $this->descriptor->collection('parameters')->property('user_name',function($property) {
            $property
                ->title('Username')
                ->type('text')   
                ->required(true)                    
                ->value('');                         
        });
        $this->descriptor->collection('parameters')->property('email',function($property) {
            $property
                ->title('Email')
                ->type('email')   
                ->required(true)                    
                ->value('');                         
        });
        // result
        $this->descriptor->collection('result')->property('error',function($property) {
            $property
                ->title('Error')
                ->type('text')   
                ->required(false)                    
                ->value('');                         
        });
        $this->descriptor->collection('result')->property('user',function($property) {
            $property
                ->title('User')
                ->type('list')   
                ->required(false)                    
                ->value('');                         
        });
    }
}
