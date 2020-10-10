<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Options\OptionsListDefinition;

/**
 * User options list model class
 */
class UserOptionsList extends Model  
{
    use Uuid,  
        Find,     
        OptionsListDefinition;
            
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'user_options_list';

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;    
}
