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

/**
 * Permisisons control panel api controler
*/
class PermissionsControlPanel extends ControlPanelApiController
{
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
     * Delete permission
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function deletePermissionController($request, $response, $data) 
    {       
        $this->onDataValid(function($data) { 
            $permission = Model::Permissions()->findById($data['uuid']);
            $relations = Model::PermissionRelations();

            if ($permission->editable != 1) {
                $this->error('errors.permission.editable');
                return;
            }

            if ($relations->hasRelatedItems($permission->id) == true) {
                $this->error('errors.permission.used');
                return;
            }

            $result = $permission->delete();
        
            $this->setResponse((bool)$result,function() use($permission) {                  
                $this
                    ->message('permission.delete')
                    ->field('uuid',$permission->uuid);                  
            },'errors.permission.delete');                                    
        });
        $data
            ->addRule('exists:model=Permissions|field=uuid','uuid')   
            ->validate();       
    }

    /**
     * Update permission
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function updatePermissionController($request, $response, $data) 
    {       
        $this->onDataValid(function($data) { 
            $permission = Model::Permissions()->findById($data['uuid']);

            if ($permission->editable != 1) {
                $this->error('errors.permission.editable');
                return;
            }

            $result = $permission->update([
                'name'        => $data['name'],
                'title'       => $data['title'],
                'description' => $data['description'],
                'editable'    => true
            ]);
        
            $this->setResponse((bool)$result,function() use($permission) {                  
                $this
                    ->message('permission.update')
                    ->field('uuid',$permission->uuid);                  
            },'errors.permission.update');                                    
        });
        $data
            ->addRule('exists:model=Permissions|field=uuid','uuid')   
            ->validate();       
    }

    /**
     * Add permission
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function addPermissionController($request, $response, $data) 
    {       
        $this->onDataValid(function($data) { 
            $permissions = Model::Permissions();

            if ($permissions->has($data['name']) == true) {
                $this->error('errors.permission.exist');
                return;
            }
            $permission = $permissions->create([
                'name'        => $data['name'],
                'title'       => $data['title'],
                'description' => $data['description'],
                'editable'    => true
            ]);
        
            $this->setResponse(\is_object($permission),function() use($permission) {                  
                $this
                    ->message('permission.add')
                    ->field('uuid',$permission->uuid);                  
            },'errors.permission.add');                                    
        });
        $data->validate();       
    }

    /**
     * Grant permission
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function grantPermissionController($request, $response, $data) 
    { 
        $this->onDataValid(function($data) {
            $type = $data->get('type','user'); // user or group relation type

            $model = ($type == 'group') ? Model::UserGroups()->findById($data['target_uuid']) : Model::Users()->findById($data['target_uuid']);

            $relations = Model::PermissionRelations();
            $permission = Model::Permissions()->findById($data['uuid']);
            
            if ($permission->name == $this->get('access')->getControlPanelPermission()) {
                $this->error('errors.permission.admin');
                return;
            }

            if ($type == 'group') {
                $relation = $relations->setGroupPermission($permission->name,['read','write','delete','execute'],$model->id);
            } else{
                $relation = $relations->setUserPermission($permission->name,['read','write','delete','execute'],$model->id);
            }           
            $this->setResponse(\is_object($relation),function() use($relation) {                  
                $this
                    ->message('permission.grant')
                    ->field('relation_id',$relation->relation_id)
                    ->field('uuid',$relation->uuid); 

            },'errors.permission.grant');                                        
        });        
        $data
            ->addRule('exists:model=Permissions|field=uuid','uuid')   
            ->validate();      
    }

    /**
     * Deny permission
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function denyPermissionController($request, $response, $data) 
    {
        $this->onDataValid(function($data) {
            $relations = Model::PermissionRelations()->findById($data['uuid']);
            $permission = Model::Permissions()->findById($relations->permission_id);
           
            if ($permission->name == $this->get('access')->getControlPanelPermission()) {
                $this->error('errors.permission.admin');
                return;
            }            
            $result = $relations->delete();

            $this->setResponse((bool)$result,function() use($relations) {                  
                $this
                    ->message('permission.deny')
                    ->field('uuid',$relations->uuid);                  
            },'errors.permission.deny');                            
        });        
        $data
            ->addRule('exists:model=PermissionRelations|field=uuid','uuid')   
            ->validate();      
    }

    /**
     * Chnage permission type (remove, add)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function updatePermissionTypeController($request, $response, $data) 
    {
        $this->onDataValid(function($data) {
            $type = $data->get('type');
            $relations = Model::PermissionRelations()->findById($data['uuid']);
            $permission = Model::Permissions()->findById($relations->permission_id);
           
            if ($permission->name == $this->get('access')->getControlPanelPermission()) {
                $this->error('errors.permission.admin');
                return;
            }            

            $result = ($data['actionType'] == 'add') ? $relations->addPermisionType($type) : $relations->removePermisionType($type);
                          
            $this->setResponse((bool)$result ?? false,function() use($relations) {                  
                $this
                    ->message('permission.deny')
                    ->field('uuid',$relations->uuid);                  
            },'errors.permission.deny');                            
        });        
        $data
            ->addRule('exists:model=PermissionRelations|field=uuid','uuid')   
            ->validate();      
    }
}
