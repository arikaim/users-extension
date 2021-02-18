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
use Arikaim\Core\Utils\Path;
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
     * @param string|null $uuid
     * @return string
     */
    public function getAvatarViewUrl($uuid = null)
    {
        $uuid = $uuid ?? (\is_object($this->user) == true) ? $this->user->uuid : '';
        $path = '/api/users/avatar/view/';

        return ($this->isPublic() == true) ? $path . $uuid : $path;
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
     * @param integer|null $userId User id
     * @return bool
     */
    public function deleteUserDetails($userId = null)
    {
        $userId = $userId ?? $this->user_id;
        $model = $this->where('user_id','=',$userId)->first();
             
        // delete options
        if (\is_object($model) == true) {
            $model->deleteUserOptions($userId);
        } else {
            $this->deleteUserOptions($userId);
        }
       
        return (\is_object($model) == true) ? $model->delete() : true;           
    }   

    /**
     * Delete user options
     *
     * @param int|null $userId
     * @return bool
     */
    public function deleteUserOptions($userId = null)
    {
        $userId = $userId ?? $this->user_id;

        $model = new UserOptions();
        $model = $model->where('reference_id','=',$userId);

        return (\is_object($model) == true) ? $model->delete() : true;
    }

    /**
     * Save user details
     *
     * @param integer $userId
     * @param array $details
     * @return Model|bool
     */
    public function saveDetails($userId, array $details)
    {
        $details['user_id'] = $userId;
        $model = $this->findByColumn($userId,'user_id');

        return (\is_object($model) == true) ? (bool)$model->update($details) : $this->create($details);
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
     * Find user details
     *
     * @param int $userId
     * @return Model|null
     */
    public function findDetails($userId)
    {
        return $this->where('user_id','=',$userId)->first();
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
        $result = true;
        if ($this->hasStorageFolder() == false) {
            $path = $this->getUserStoragePath(false);
            $result = File::makeDir($path);
            File::setWritable($path);           
        }

        return $result;
    }

    /**
     * Return true if user have storage folder
     *
     * @return boolean
     */
    public function hasStorageFolder()
    {
        return File::exists($this->getUserStoragePath(false));
    }

    /**
     * Return avatar image path
     *
     * @param bool $relative
     * @return string|false
     */
    public function getAvatarImagePath($relative = true)
    {
        $path = (empty($this->avatar) == true) ? false : $this->getUserStoragePath($relative) . $this->avatar;
        
        return $path;
    }

    /**
     * Delete avatar image
     *
     * @return boolean
     */
    public function deleteAvatarImage()
    {
        $path = $this->getAvatarImagePath(false);

        return ($path === false) ? true : File::delete($path);
    }

    /**
     * Get user storage relative path
     *
     * @param bool $relative
     * @return string
     */
    public function getUserStoragePath($relative = true)
    {
        $path = 'users' . DIRECTORY_SEPARATOR . 'user-' . $this->user->uuid . DIRECTORY_SEPARATOR;

        return ($relative == true) ? $path : Path::STORAGE_PATH . $path;
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
