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
use Arikaim\Core\Utils\Uuid;

/**
 * User type db table
 */
class UserType extends Schema  
{    
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = 'user_type';

    /**
     * Create table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function create($table) 
    {
        // fields
        $table->prototype('id');
        $table->prototype('uuid');
        $table->slug();
        $table->string('title')->nullable(false);
        $table->status();
        $table->string('description')->nullable(true);      
        $table->options();     
    }

    /**
     * Update table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function update($table) 
    {              
        if ($this->hasColumn('options') == false) {
            $table->options();     
        }
    }

    /**
     * Insert or update rows in table
     *
     * @param Seed $seed
     * @return void
     */
    public function seeds($seed)
    {  
        $seed->create(['slug' => 'default'],[
            'uuid'   => Uuid::create(),
            'title'  => 'Default',               
            'status' => 1
        ]); 
    }
}
