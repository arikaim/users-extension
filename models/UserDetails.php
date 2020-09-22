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

use Arikaim\Core\Utils\File;
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\Page;
use Arikaim\Extensions\Users\Models\UserType;
use Arikaim\Extensions\Users\Models\UserOptions;

use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\UserRelation;
use Arikaim\Core\Db\Traits\Options\OptionsRelation;

/**
 * Users details model
 */
class UserDetails extends Model  
{
    use Uuid,
        Find,
        OptionsRelation,
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
        'type_id',
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
     * Options class name
     *
     * @var string
     */
    protected $optionsClass = UserOptions::class;

    /**
     * Options primary key
     *
     * @var string
     */
    protected $optionsPrimaryKey = 'user_id';

    /**
     * Get view avatar url
     *
     * @param bool $logged
     * @param string|null $uuid
     * @return string|null
     */
    public function getAvatarViewUrl($logged = false, $uuid = null)
    {
        if (empty($this->avatar) == true) {
            return null;
        }
        if ($logged == true) {
            return '/api/users/avatar/view';
        }
        $uuid = (empty($uuid) == true) ? $this->user->uuid : $uuid;

        return ($this->isPublic() == true) ? '/users/avatar/view/' . $uuid : null;
    }

    /**
     * Get options type name
     *
     * @return string|null
     */
    public function getOptionsType()
    {
        return (\is_object($this->type) == true) ? $this->type->slug : null;
    }

    /**
     * Get user type relation
     *
     * @return Relation
     */
    public function type()
    {
        return $this->belongsTo(UserType::class,'type_id');
    } 

    /**
     * Delete user details
     * 
     * @param string|integer $id User id
     * @return boolean
     */
    public function deleteUserDetails($id)
    {
        $model = $this->where('user_id','=',$id)->first();
        if (\is_object($model) == false) {
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

        return (\is_object($model) == true) ? $model->update($details) : $this->create($details);
    }

    /**
     * Find or create user details row
     *
     * @param integer $userId
     * @return Model|false
     */
    public function findOrCreate($userId)
    {
        $model = $this->where('user_id','=',$userId)->first();
        
        return (\is_object($model) == true) ? $model : $this->create(['user_id' => $userId]);          
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
        return \trim($this->attributes['first_name'] . ' ' . $this->attributes['last_name']);
    }
    
    /**
     * Create user storage folder
     *
     * @return boolean
     */
    public function createStorageFolder()
    {
        if ($this->hasStorageFolder() == false) {
            $result = Arikaim::storage()->createDir($this->getUserStoragePath());
            File::setWritable($this->getUserStoragePath());
            return $result;
        }

        return true;
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

        return ($path === false) ? true : Arikaim::storage()->delete($path);
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
