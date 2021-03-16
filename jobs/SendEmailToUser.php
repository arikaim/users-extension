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
use Arikaim\Core\Arikaim;
use Arikaim\Core\Interfaces\Job\JobInterface;

/**
 * Send email to user
 */
class SendEmailToUser extends Job implements JobInterface
{
    /**
     * Run job
     *
     * @return mixed
     */
    public function execute()
    {
        $user = $this->params['user'] ?? null;
        $emailComponent = $this->params['email.component'] ?? null;
    
        if (empty($user) == true || empty($emailComponent) == true) {
            return false;
        }

        $to = $user['email'] ?? null;
        if (empty($to) == true) {
            return false;
        }

        return Arikaim::mailer()->create($emailComponent,$user)                   
            ->to($to)
            ->send();
    }
}
