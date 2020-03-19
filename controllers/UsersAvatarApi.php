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
        $this->loadMessages('users::messages');
    }

    /**
     * Delete avatar
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

        $this->onDataValid(function($data) use ($user) { 
            $user = Model::Users()->findByid($user['uuid']);
            $details = Model::UserDetails('users')->findOrCreate($user->id);

            $details->deleteAvatarImage();
            $result = $details->update(['avatar' => null]);

            $this->setResponse($result,function() use($user) {                  
                $this
                    ->message('avatar.delete')
                    ->field('uuid',$user->uuid);                  
            },'errors.avatar.delete');               
        });
        $data->validate();

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

        $this->onDataValid(function($data) use ($request,$user) {          
            $user = Model::Users()->findById($user['id']);
            if (is_object($user) == false) {
                $this->error('Not valid user id');
                return;
            }
            $details = Model::UserDetails('users')->findOrCreate($user->id);
            if (is_object($details) == false) {
                $this->error('User details not exists.');
                return;
            }

            $result = $details->createStorageFolder();
            if ($result === false) {
                $this->error("Can't create user storage directory.");
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
           
            $this->setResponse(is_array($files),function() use($user,$avatar) {                  
                $this
                    ->message('avatar.upload')
                    ->field('uuid',$user->uuid)
                    ->field('avatar',$avatar);                                                
            },'errors.avatar.upload');   

        });
        $data->validate();   
    }

    /**
     * View avatar in user admin
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function viewUserAvatar($request, $response, $data) 
    { 
        $user = $this->get('access')->getUser();      
         
        $user = Model::Users()->findById($user['uuid']);
        if (is_object($user) == false) {
            // user not found
            return false;
        }
        $details = Model::UserDetails('users')->findOrCreate($user['id']);
       
        $avatarImage = $details->getAvatarImagePath();
       
        return $this->viewImage($response,$avatarImage);       
    }

    /**
     * View avatar
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function viewAvatar($request, $response, $data) 
    { 
        $uuid = $data->get('uuid');
        $user = Model::Users()->findById($uuid);
        if (is_object($user) == false) {
            // user not found
            return false;
        }
        $details = Model::UserDetails('users')->findOrCreate($user->id);
        if ($details->isPublic() == false) {
            // user profile is private
            $this->error('Private user profile');
            return $this->getResponse();
        }
        $avatarImage = $details->getAvatarImagePath();
       
        return $this->viewImage($response,$avatarImage);       
    }
}
