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

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\Page;

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
        'phone_2',
        'public_profile'
    ];
    
    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'name'
    ];

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Delete user details
     * 
     * @param string|integer $id User id
     * @return boolean
     */
    public function deleteUserDetails($id)
    {
        $model = $this->where('user_id','=',$id)->first();
        if (is_object($model) == false) {
            return false;
        }

        // delete avatar
        $model->deleteAvatarImage();
        
        return $model->delete();
    }   

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
     * Find or create user details row
     *
     * @param integer $id
     * @return Model|false
     */
    public function findOrCreate($id)
    {
        $model = $this->where('user_id','=',$id)->first();
        
        return (is_object($model) == true) ? $model : $this->create(['user_id' => $id]);          
    } 

    /**
     * Return true if profile is public
     *
     * @return boolean
     */
    public function isPublic()
    {
        return ($this->public_profile == 1);
    }

    /**
     * Return true if email is confirmed
     *
     * @return boolean
     */
    public function isConfirmedEmail()
    {
        return ($this->email_status == 1);
    }

    /**
     * Set email status
     *
     * @param integer $status
     * @return boolean
     */
    public function setEmailStatus($status)
    {
        $this->email_status = $status;
        
        return $this->save();
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
    
    /**
     * Create user storage folder
     *
     * @return boolean
     */
    public function createStorageFolder()
    {
        return ($this->hasStorageFolder() == false) ? Arikaim::storage()->createDir($this->getUserStoragePath()) : true;       
    }

    /**
     * Return true if user have storage folder
     *
     * @return boolean
     */
    public function hasStorageFolder()
    {
        return Arikaim::storage()->has($this->getUserStoragePath());
    }

    /**
     * Return avatar image path
     *
     * @return string|false
     */
    public function getAvatarImagePath()
    {
        if (empty($this->avatar) == true) {
            return false;
        }
        $path = $this->getUserStoragePath() . $this->avatar;

        return (Arikaim::storage()->has($path) == true) ? $path : false;
    }

    /**
     * Delete avatar image
     *
     * @return boolean
     */
    public function deleteAvatarImage()
    {
        $path = $this->getAvatarImagePath();

        return ($path === false) ? true : Arikaim::storage()->delete($path,false);
    }

    /**
     * Get user storage relative path
     *
     * @return string
     */
    public function getUserStoragePath()
    {
        return DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . "user-" . $this->user->uuid . DIRECTORY_SEPARATOR;
    }

    /**
     * Get user profile url
     * 
     * @param boolean $full
     * @param string|null $language
     * @return string
     */
    public function getUserProfileUrl($full = true, $language = null)
    {
        return Page::getUrl('/user/profile/' . $this->user->uuid,$full,$language);
    }

    /**
     * Get user avatar url
     * 
     * @param boolean $full
     * @param string|null $language
     * @return string
     */
    public function getUserAvatarUrl($full = true, $language = null)
    {
        return Page::getUrl('/users/avatar/view/' . $this->user->uuid,$full,$language);
    }
}
