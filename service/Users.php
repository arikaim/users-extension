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
use Arikaim\Core\Arikaim;

/**
 * Users service class
*/
class Users extends Service implements ServiceInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setServiceName('users');
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
        $password = (empty($password) == true) ? Text::createToken(12) : $password;
        $user = Model::Users()->createUser($userName,$password,$email);

        if (\is_object($user) == true) {
            Arikaim::event()->dispatch('user.signup',$user->toArray()); 
            return $user;
        }

        return false;
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
        if (\is_object($model) == false) {
            return null;
        }

        if ($model->createStorageFolder() == false) {
            return null;
        }

        return $model->getUserStoragePath($relative);
    }    
}
