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

/**
 * Add item in dashboard extension
*/
class DashboardSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->subscribe('dashboard.get.items');
    }

    /**
     * Run 
     *
     * @param EventInterface $event
     * @return void
     */
    public function execute($event)
    {
        return [
            'component' => 'users::admin.dashboard',
            'class'     => 'three wide'
        ];   
    }
}
