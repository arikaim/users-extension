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

use Arikaim\Core\Models\Users;
use Arikaim\Extensions\Users\Models\UserDetails;
use Arikaim\Extensions\Users\Models\UserOptionType;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Options\Options;

/**
 * Users options model class
 */
class UserOptions extends Model  
{
    use Uuid,       
        Options,
        Find;
    
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'user_options';

    /**
     * Fillable columns
     *
     * @var array
     */
    protected $fillable = [
        'reference_id',       
        'value',
        'key',
        'uuid',
        'type_id'       
    ];

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Option type model class 
     *
     * @var string
     */
    protected $optionTypeClass = UserOptionType::class;

    /**
     * Get user relation
     *
     * @return Relation
     */
    public function user()
    {
        return $this->belongsTo(Users::class,'reference_id');
    }

    /**
     * user details relation
     *
     * @return Relation
     */
    public function details()
    {
        return $this->belongsTo(UserDetails::class,'reference_id','user_id');
    } 
}
