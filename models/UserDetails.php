<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Models\Users;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Traits\Db\Uuid;

class UserDetails extends Model  
{
    use Uuid,
        Find,
        Status;
    
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'phone_2'
    ];
        
    public $timestamps = false;

    public function saveDetails($user_id, array $details)
    {
        $details['user_id'] = $user_id;
        $model = $this->findByColumn($user_id,'user_id');

        return (is_object($model) == true) ? $model->update($details) : $this->create($details);
    }

    public function getNameAttribute()
    {
        return trim($this->attributes['first_name'] . ' ' . $this->attributes['last_name']);
    }

    public function getDetails($id)
    {
        $user = new User();
        $user = $user->findById($id);
        $details = $this->where('user_id','=',$user->id)->first();
        
        return $details;
    } 
}
