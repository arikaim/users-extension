<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Models\Schema;

use Arikaim\Core\Db\Schema;

/**
 * UserDetails db table schema
 */
class UserDetails extends Schema  
{    
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = 'user_details';

    /**
     * Create table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */ 
    public function create($table) 
    {           
        // columns
        $table->id();
        $table->prototype('uuid');  
        $table->userId();
        $table->relation('type_id','user_type',true);
        $table->string('avatar')->nullable(true);
        $table->string('first_name')->nullable(true);
        $table->string('last_name')->nullable(true);           
        $table->string('phone')->nullable(true);   
        $table->string('phone_2')->nullable(true);  
        $table->integer('email_status')->nullable(true)->default(0);  
        $table->integer('public_profile')->nullable(true);
        // indexes
        $table->unique('user_id');   
    }

    /**
     * Update table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
    */
    public function update($table) 
    {        
        if ($this->hasColumn('type_id') == false) {
            $table->relation('type_id','user_type',true);
        } 
    }
}
