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

use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\UserRelation;

/**
 * Users details model
 */
class UserDetails extends Model  
{
    use Uuid,
        Find,
        UserRelation,
        Status;
    
    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'avatar',
        'first_name',
        'last_name',
        'email_status',
        'phone',
        'phone_2'
    ];
        
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Save user details
     *
     * @param integer $userId
     * @param array $details
     * @return void
     */
    public function saveDetails($userId, array $details)
    {
        $details['user_id'] = $userId;
        $model = $this->findByColumn($userId,'user_id');

        return (is_object($model) == true) ? $model->update($details) : $this->create($details);
    }

    /**
     * Full name attribute
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return trim($this->attributes['first_name'] . ' ' . $this->attributes['last_name']);
    }    
}
