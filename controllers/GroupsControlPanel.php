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
use Arikaim\Core\Controllers\ControlPanelApiController;

use Arikaim\Core\Controllers\Traits\Status;

/**
 * Groups control panel api controler
*/
class GroupsControlPanel extends ControlPanelApiController
{
    use Status;       

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('users::admin.messages');
        $this->setModelClass('UserGroups');
        $this->setExtensionName('users');
    }

    /**
     * Delete group
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
    */
    public function deleteController($request, $response, $data) 
    {       
        $data
            ->addRule('exists:model=UserGroups|field=uuid|required','uuid')                    
            ->validate(true);      
       
        $group = Model::UserGroups()->findById($data['uuid']);
        
        $memebersCount = $group->members->count();
        if ($memebersCount > 0) {
            $this->error('errors.groups.empty');
            return false;                
        }

        $result = $group->delete();
        if ($result === false) {
            $this->error('errors.group.delete','Error delete user group');
            return false; 
        }
           
        $this
            ->message('groups.delete')
            ->field('uuid',$data['uuid']);                                                          
    }

    /**
     * Add group
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function addController($request, $response, $data) 
    {       
        $data
            ->addRule('unique:model=UserGroups|field=title|required','title')                    
            ->validate(true);   
            
        $group = Model::UserGroups();
        $newGroup = $group->create($data->toArray());
    
        $this->setResponse(\is_object($newGroup),function() use($newGroup) {                  
            $this
                ->message('groups.add')
                ->field('uuid',$newGroup->uuid);                  
        },'errors.group.add');                                    
    }
   
    /**
     * Update group details
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function updateController($request, $response, $data) 
    { 
        $data
            ->addRule('exists:model=UserGroups|field=uuid','uuid')
            ->addRule('text:min=2','title')            
            ->validate(true);    

        // save group 
        $group = Model::UserGroups()->findById($data['uuid']);
        $result = $group->update($data->toArray());
            
        $this->setResponse($result,function() use($group) {                  
            $this
                ->message('groups.update')
                ->field('uuid',$group->uuid);                  
        },'errors.groups.update');                                    
    }

    /**
     * Add group member
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function addMemberController($request, $response, $data) 
    {    
        $data          
            ->addRule('exists:model=Users|field=uuid','user_uuid')
            ->addRule('exists:model=UserGroups|field=uuid','uuid')          
            ->validate(true);       

        $members = Model::UserGroupMembers();
        $member = $members->addMember($data['user_uuid'],$data['uuid']);
        
        $this->setResponse(\is_object($member),function() use($member) {                  
            $this
                ->message('groups.members.add')
                ->field('uuid',$member->uuid);                  
        },'errors.groups.members.add');      
    }

    /**
     * Remove group member
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function removeMemberController($request, $response, $data) 
    {      
        $data          
            ->addRule('exists:model=UserGroupMembers|field=uuid','uuid')         
            ->validate(true);   

        $member = Model::UserGroupMembers()->findById($data['uuid']);  
        $result = $member->delete();

        $this->setResponse((bool)$result,function() use($member) {                  
            $this
                ->message('groups.members.remove')
                ->field('uuid',$member->uuid);                  
        },'errors.groups.members.remove');      
    }
}
