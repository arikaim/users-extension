<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Users\Classes;

use Arikaim\Core\Extension\Extension;
use Arikaim\Core\Utils\Uuid;
use Arikaim\Core\Db\OptionType;
use Arikaim\Core\Db\Seed;

/**
 * User type options
*/
class UserType 
{
    /**
     * Create user type
     *
     * @param string $title
     * @param string $slug
     * @return void
     */
    public function create(string $title, string $slug)
    {
        // Add user type
        Seed::withModel('UserType','users',function($seed) use($title, $slug) {
            $seed->create(['slug' => $slug],[
                'uuid'   => Uuid::create(),
                'title'  => $title,               
                'status' => 1
            ]); 
        });
    }

    /**
     * Create options definition
     *
     * @param string $configFile
     * @param string $extensionName
     * @return void
     */
    public function createOptionsDefinition(string $configFile, string $extensionName) 
    {
        // Add options type definition
        $items = Extension::loadJsonConfigFile($configFile,$extensionName);

        Seed::withModel('UserOptionType','users',function($seed) use($items) {
            $seed->createFromArray(['key'],$items,function($item) {
                $item['uuid'] = Uuid::create();
                $item['type'] = OptionType::getOptionTypeId($item['type']);
                return $item;
            });   
        });
    }
}
