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
     * Get user storage path
     *
     * @param integer|null $userId
     * @param boolean $relative
     * @return string|null
     */
    public function getStoragePaht(?int $userId, bool $relative = true): ?string
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
