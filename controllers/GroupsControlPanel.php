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
    }

    /**
     * Constructor
     * 
     * @param Container|null $container
     */
    public function __construct($container = null) 
    {
        parent::__construct($container);
        $this->setModelClass('UserGroups');
        $this->setExtensionName('users');
    }

    /**
     * Delete group
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function deleteController($request, $response, $data) 
    {       
        $this->onDataValid(function($data) { 
            $group = Model::UserGroups()->findById($data['uuid']);
            
            $memebersCount = $group->members->count();
            if ($memebersCount > 0) {
                $this->error('errors.groups.empty');
                return;                
            }

            $result = $group->delete();

            $this->setResponse($result,function() use($data) {                  
                $this
                    ->message('groups.delete')
                    ->field('uuid',$data['uuid']);                  
            },'errors.group.delete');                                    
        });
        $data
            ->addRule('exists:model=UserGroups|field=uuid|required','uuid')                    
            ->validate();       
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
        $this->onDataValid(function($data) { 
            $group = Model::UserGroups();
            $newGroup = $group->create($data->toArray());
        
            $this->setResponse(\is_object($newGroup),function() use($newGroup) {                  
                $this
                    ->message('groups.add')
                    ->field('uuid',$newGroup->uuid);                  
            },'errors.group.add');                                    
        });
        $data
            ->addRule('unique:model=UserGroups|field=title|required','title')                    
            ->validate();       
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
        $this->onDataValid(function($data) {
            // save group 
            $group = Model::UserGroups()->findById($data['uuid']);
            $result = $group->update($data->toArray());
             
            $this->setResponse($result,function() use($group) {                  
                $this
                    ->message('groups.update')
                    ->field('uuid',$group->uuid);                  
            },'errors.groups.update');
                                        
        });        
        $data
            ->addRule('exists:model=UserGroups|field=uuid','uuid')
            ->addRule('text:min=2','title')            
            ->validate();      
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
        $this->onDataValid(function($data) { 
            $members = Model::UserGroupMembers();
            $member = $members->addMember($data['user_uuid'],$data['uuid']);
            
            $this->setResponse(\is_object($member),function() use($member) {                  
                $this
                    ->message('groups.members.add')
                    ->field('uuid',$member->uuid);                  
            },'errors.groups.members.add');      
        });
        $data          
            ->addRule('exists:model=Users|field=uuid','user_uuid')
            ->addRule('exists:model=UserGroups|field=uuid','uuid')          
            ->validate();       
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
        $this->onDataValid(function($data) { 
            $member = Model::UserGroupMembers()->findById($data['uuid']);
                
            $result = $member->delete();

            $this->setResponse($result,function() use($member) {                  
                $this
                    ->message('groups.members.remove')
                    ->field('uuid',$member->uuid);                  
            },'errors.groups.members.remove');      
        });
        $data          
            ->addRule('exists:model=UserGroupMembers|field=uuid','uuid')         
            ->validate();       
    }
}
