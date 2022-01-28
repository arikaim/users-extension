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
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\Slug;
use Arikaim\Core\Db\Traits\Find;

/**
 * User type model class
 */
class UserType extends Model  
{
    use Uuid,
        Slug,
        Status,
        Find;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'user_type';

    /**
     * Fillable columns
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'status',
        'slug',
        'title',
        'description'
    ];
   
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Visible columns
     *
     * @var array
     */
    protected $visible = [             
        'slug',
        'title',              
    ];
}
