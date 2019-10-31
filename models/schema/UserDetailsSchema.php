<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Models\Schema;

use Arikaim\Core\Db\Schema;

/**
 * UserDetails db table schema
 */
class UserDetailsSchema extends Schema  
{    
    /**
     * Table name
     *
     * @var string
     */
    protected $table_name = "user_details";

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
        $table->string('first_name')->nullable(true);
        $table->string('last_name')->nullable(true);           
        $table->string('phone')->nullable(true);   
        $table->string('phone_2')->nullable(true);   
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
    }
}
