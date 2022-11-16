<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Extensions\Users\Console;

use Arikaim\Core\Console\ConsoleCommand;
use Arikaim\Core\Db\Model;

/**
 * Delete games command
 */
class DeleteGames extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('users:delete')->setDescription('Delete user.'); 
        $this->addOptionalArgument('id','User id or uuid');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function executeCommand($input, $output)
    {       
        global $container;

        $userId = $input->getArgument('id');
       
        $this->style->writeLn('');
        $this->style->writeLn('Delete User: ' . $userId);
        $this->style->writeLn('');

        $user = Model::Users()->findById($userId);
        if ($user == null) {
            $this->showError('Not vlaid user Id!');
            return;
        }
        if ($user->isControlPanelUser() == true) {
            $this->showError("Can't deelete control panel user!");
            return;
        }
       
        // trigger before user delete event
        $container->get('event')->dispatch('user.before.delete',$user->toArray());

        // delete user details
        $userDetails = Model::UserDetails('users');
        $userDetails->deleteUserDetails($userId);
        // delete user
        $user->deleteUser();

        $this->showCompleted();
    }
}
