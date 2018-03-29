<?php
/**
 * This file is part of MailDoDo.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    Wilson <Wilson@wasoon.cn>
 * @copyright Wilson <Wilson@wasoon.cn>
 * @link      http://www.maildodo.cn/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @example
    use MailDoDo\Transmitter;
    $transmitter = new Transmitter('appid', 'app_secret');
    $transmitter->send([
        'title' => '邮件标题',
        'contents' => '邮件内容',
        'addressee' => '收件人，多个以英文逗号分隔',
        'secret_to_send' => '抄送，多个以英文逗号分隔'
    ]);
 */
namespace MailDoDo;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload.php';
class Transmitter extends \MailDoDo
{
}
