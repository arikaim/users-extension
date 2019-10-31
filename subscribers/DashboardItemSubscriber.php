<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/

namespace Arikaim\Extensions\Users\Events;

use Arikaim\Core\Events\EventSubscriber;

/**
 * Add item in dashboard extension
*/
class DashboardItemSubscriber extends EventSubscriber
{
    public function __construct() 
    {
        $this->subscribe('dashboard.get.items','users');
    }

    public function execute($event)
    {
        return "users:admin.dashboard";
    }
}