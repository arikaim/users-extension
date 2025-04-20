<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Events;

use Arikaim\Core\Events\EventDescriptor;

/**
 * User event descriptor class
*/
class UserEventDescriptor extends EventDescriptor
{
    protected function definition(): void
    {
        $this->property('user_name',function($property) {
            $property
                ->title('Username')
                ->type('text')   
                ->required(false);                    
                        
        });
        
        $this->property('email',function($property) {
            $property
                ->title('Email')
                ->type('text')   
                ->required(false);                                   
        });

        $this->property('status',function($property) {
            $property
                ->title('Status')
                ->type('number')   
                ->required(false);                                   
        });
    }  
}
