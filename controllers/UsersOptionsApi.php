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

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Controllers\Traits\Options\Options;

/**
 * Users api controller
*/
class UsersOptionsApi extends ApiController
{
    use       
        Options;

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
     * Constructor
     * 
     * @param Container|null $container
     */
    public function __construct($container = null) 
    {
        parent::__construct($container);
        $this->setModelClass('UserOptions');
        $this->setExtensionName('users');
    }
}
