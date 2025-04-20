<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Service;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Service\Service;
use Arikaim\Core\Service\ServiceInterface;
use Arikaim\Core\Utils\Text;

/**
 * Users service class
*/
class Users extends Service implements ServiceInterface
{
    /**
     * Init service
    */
    public function boot()
    {
        $this->setServiceName('users');
    }

    /**
     * Get view avatar url
     *
     * @param string|null $uuid
     * @return string|null
     */
    public function getViewAvatarUrl(?string $uuid = null): ?string
    {
        return '/api/users/avatar/view/' . $uuid ?? '';
    }

    /**
     * Create user
     *
     * @param string|null $userName
     * @param string|null $password
     * @param string|null $email
     * @return Model|false
     */
    public function create(?string $userName, ?string $password = null, ?string $email = null)
    {
        global $arikaim;

        $password = (empty($password) == true) ? Text::createToken(12) : $password;
        $user = Model::Users()->createUser($userName,$password,$email);

        if ($user === false || $user === null) {
            return false;
        }

        $arikaim->get('event')->dispatch('user.signup',$user->toArray()); 

        return $user;       
    }

    /**
     * Get user storage path
     *
     * @param integer|null $userId
     * @param boolean $relative
     * @return string|null
     */
    public function getStoragePath(?int $userId, bool $relative = true): ?string
    {
        $model = Model::UserDetails('users')->findOrCreate($userId);
        if ($model == null) {
            return null;
        }

        if ($model->createStorageFolder() == false) {
            return null;
        }

        return $model->getUserStoragePath($relative);
    }    
}
