<?php
/**
 * This file is part of MailDoDo.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    Wilson<Wilson@wasoon.cn>
 * @copyright Wilson<Wilson@wasoon.cn>
 * @link      http://www.maildodo.cn/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MailDoDo;

class SLoader
{
    private static function _autoload ($class_name)
    {
        
    }
    
    public static function setup()
    {
        spl_autoload_register(array(__CLASS__, '_autoload'));
    }
}

SLoader::setup();
