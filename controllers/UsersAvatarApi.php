<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Controllers;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Controllers\ApiController;

use Arikaim\Core\Controllers\Traits\FileUpload;
use Arikaim\Core\Controllers\Traits\FileDownload;

/**
 * Users api controller
*/
class UsersAvatarApi extends ApiController
{
    use 
        FileUpload,
        FileDownload;

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('users>users.messages');
    }

    /**
     * Delete avatar
     *
     * @Api(
     *      description="Delete user avatar",    
     *      parameters={
     *          @ApiParameter (name="uuid",type="string",description="User uuid",required=true)             
     *      }
     * )
     * 
     * @ApiResponse(
     *      fields={
     *          @ApiParameter (name="uuid",type="string",description="User uuid")
     *      }
     * )  
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function deleteAvatarController($request, $response, $data) 
    { 
        // get current auth user
        $user = $this->get('access')->getUser();
        $data->validate(true);

        $user = Model::Users()->findByid($user['uuid']);
        $details = Model::UserDetails('users')->findOrCreate($user->id);

        $details->deleteAvatarImage();
        $result = $details->update(['avatar' => null]);

        $this->setResponse((bool)$result,function() use($user) {                  
            $this
                ->message('avatar.delete')
                ->field('uuid',$user->uuid);                  
        },'errors.avatar.delete');               
    }

    /**
     * Upload avatar
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function uploadAvatarController($request, $response, $data) 
    {        
        // get current auth user
        $user = $this->get('access')->getUser();
        $data->validate(true);   

        $user = Model::Users()->findById($user['id']);
        if ($user == null) {
            $this->error('errors.id','Not valid user id');
            return;
        }
        $details = Model::UserDetails('users')->findOrCreate($user->id);
        if (\is_object($details) == false) {
            $this->error('errors.details');
            return;
        }

        $result = $details->createStorageFolder();
        if ($result === false) {
            $this->error('errors.storage');
            return;
        }
        $destinationPath = $details->getUserStoragePath();
    
        $files = $this->uploadFiles($request,$destinationPath);

        // process uploaded files
        $avatar = null;
        foreach ($files as $item) {               
            if (empty($item['error']) == false) {
                continue;
            }
            if (empty($details->avatar) == false) {
                // remove prev avatar
                $details->deleteAvatarImage();
            }  

            // set avatar image           
            $avatar = $item['name'];                  
            $result = (bool)$details->update(['avatar' => $avatar]);                            
        }
        
        $this->setResponse(\is_array($files),function() use($user,$avatar) {                  
            $this
                ->message('avatar.upload')
                ->field('uuid',$user->uuid)
                ->field('avatar',$avatar);                                                
        },'errors.avatar.upload');    
    }

    /**
     * View avatar in user admin
     *
     * @Api(
     *      description="View user avatar",    
     *      parameters={
     *          @ApiParameter (name="uuid",type="string",description="User uuid",required=false,default="Current loged user")             
     *      }
     * )
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function viewAvatar($request, $response, $data) 
    { 
        $model = Model::Users();
        $userId = $this->getUserId(); 
        $uuid = $data->get('uuid');
        $userId = (empty($uuid) == true) ? $userId : $uuid;       
        
        $user = $model->findById($userId);
        if ($user == null) {
            // user not found
            $this->error('Not valid user id.');
            return $this->getResponse();
        }
       
        $details = Model::UserDetails('users')->findOrCreate($user->id);
        if (($details->isPublic() == false) && (empty($this->getUserId()) == true)) {
            $this->error('Access denied.');
            return $this->getResponse();
        }

        $avatarImage = $details->getAvatarImagePath();
        if ($avatarImage === false) {
            $this->error('No avatar image.');
            return $this->getResponse();
        }
      
        return $this->viewImage($response,$avatarImage);       
    }
}
