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

use Arikaim\Core\Controllers\Controller;
use Arikaim\Core\Arikaim;

/**
 * Users pages controler
*/
class Users extends Controller
{
    /**
     * User area page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function userAreaPage($request, $response, $data) 
    { 
        // get current auth user
        $data['user'] = Arikaim::access()->getUser();
        if (empty($data['user']) == true) {
            $this->get('errors')->addError('ACCESS_DENIED');
            return $this->pageSystemError($response);
        }              
    }

    /**
     * Change password page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function changePasswordPage($request, $response, $data)
    {                    
    }
}
