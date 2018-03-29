<?php
/**
 * MailDoDo 主类
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    Wilson<Wilson@wasoon.cn>
 * @copyright Copyright (C) 2018, WaSoon Inc. All rights reserved.
 * @link      http://www.maildodo.cn/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
//namespace MailDoDo;

class MailDoDo
{
    /** 
     * API地址 
     **/
    protected static $_apiUrl = 'http://api.maildodo.cn/';
    
    /** 
     * 版本号 
     **/
    protected static $_version = 'v1';
    
    /** 
     * APP ID 
     **/
    protected static $_appid = '';
    
    /** 
     * AP SECRET 
     **/
    protected static $_appSecret = '';
    
    public function __construct($app_id, $app_secret)
    {
        self::$_appid = $app_id;
        self::$_appSecret = $app_secret;
    }
    
    public function send(array $params)
    {
        if (empty($params['timeStamp'])) 
        {
            $params['timeStamp'] = time();
        }
        
        $sign = self::createSign($params);
        $url = (self::$_apiUrl . '?a=' . self::$_version . '&api=send&a=' . self::$_version . '&app_id=' . self::$_appid . "&sign={$sign}");
        #var_dump($params, $url);
        $contents = self::getResources(
            array( 'urls' => $url,
                   'timeOut' => 5,
                   'method' => 'POST',
                   'postData' => $params )
        );
        
        #var_dump($contents);
        
        return $contents;
    }
    
    /** 
     * 生成授权码 
     **/
    public static function createSign(array $params)
    {
		ksort($params);
		$queryString = http_build_query($params);
        $sign = strtoupper(md5(self::$_appSecret . $queryString . self::$_appSecret));
        #var_dump($queryString, $sign);
        
        return $sign;
    }


    /**
     * cURL并发获取资源，功能有点类似于多线程，但要注意这与多线程是不同的
     *
     * @param  mixed $params  要获取资源的链接信息参数
     * @param  int   $timeOut 超时时间值
     * @param  bool  $getInfo 是否返回连接资源句柄信息，TRUE 返回，FALSE 不返回
     * @return array          返回获取到的资源链接
     *
     * @example $contents = MailDoDo::getResources('http://www.maildodo.cn/', 1);
     *
                $contents = MailDoDo::getResources(array('http://www.maildodo.cn/', 'http://www.maildo.cn/'), 1);

                // 该调用方法参数可以不对应，但urls必须存在
                // 未定义'method'时默认为 GET, postData 可以是GET或DELETE方法时的URL，也可以是POST时的数据
                // timeOut按urls对齐，如果未设置则自动使用getResources方法参数二的值
                $contents = MailDoDo::getResources(
                    array( 'urls' => array('http://www.maildodo.cn/', 'http://www.maildo.cn/'),
                           'timeOut' => array(5),
                           'method' => 'POST',
                           'postData' => array('test' => 'postdatas') )
                );
     *
     * @since 1.0
     */
    public static function getResources( $params, $timeOut = 5, $getInfo = false )
    {
        $responses = array();
        if ( $_params = self::getCurlParams($params) )
        {
            $queue = curl_multi_init();
            $map = $urls = array();

            foreach ($_params['urls'] as $key => $url)
            {
                if ( empty($url) ) { continue; }

                $method = (isset($_params['method'][$key]) ? strtoupper($_params['method'][$key]) : 'GET');
                $ch = curl_init();

                if( !empty($_params['postData'][$key]) )
                {
                   # var_dump($_params['postData'][$key]);exit;
                    if ( $method == 'POST' )
                    {
                        curl_setopt($ch, CURLOPT_POST, 1);
                        if ( is_array($_params['postData'][$key]) )
                        {
                           curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_params['postData'][$key]));
                        }
                        else
                        {
                        	curl_setopt($ch, CURLOPT_POSTFIELDS, $_params['postData'][$key]);
                        }
                    }
                    else if ( in_array($method, array('GET', 'DELETE')) )
                    {
                        /** 
                         * HTTP 请求时，使用自定义的 Method 来代替"GET"或"HEAD"。
                         * 对 "DELETE" 或者其他更隐蔽的 HTTP 请求有用。 
                         * 有效值如 "GET"，"POST"，"CONNECT"等等；
                         * 也就是说，不要在这里输入整行 HTTP 请求。
                         * 例如输入"GET /index.html HTTP/1.0\r\n\r\n"是不正确的。
                         * Note:
                         * 不确定服务器支持这个自定义方法则不要使用它
                         **/
                        $method == 'DELETE' && curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                        $url .= (strpos($url, '?') ? '&' : '?') .
                                (is_array($_params['postData'][$key]) ? http_build_query($_params['postData'][$key]) : $_params['postData'][$key]);
                    }
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_TIMEOUT, isset($_params['timeOut'][$key]) ? (int)$_params['timeOut'][$key] : $timeOut);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                
                
                // 启用时忽略所有的curl传递给php进行的信号。在SAPI多线程传输时此项被默认启用。
                curl_setopt($ch, CURLOPT_NOSIGNAL, true);

                /** 
                 * 连接结束后，比如，调用 curl_close 后，保存 cookie 信息的文件。
                 **/
                if (isset($_params['cookiejar'][$key]))
                {
                	curl_setopt($ch, CURLOPT_COOKIEJAR, $_params['cookiejar'][$key]);
                }
                
                /** 
                 * 启用时会将头文件的信息作为数据流输出。 
                 **/
                if (isset($_params['showHeader'][$key]))
                {
                	curl_setopt($ch, CURLOPT_HEADER, $_params['showHeader'][$key]);
                }
                
                /** 
                 * 包含 cookie 数据的文件名，cookie 文件的格式可以是 Netscape 格式，
                 * 或者只是纯 HTTP 头部风格，存入文件。如果文件名是空的，不会加载 cookie，
                 * 但 cookie 的处理仍旧启用
                 **/
                if (isset($_params['cookiefile'][$key]))
                {
                	curl_setopt($ch, CURLOPT_COOKIEFILE, $_params['cookiefile'][$key]);
                }

                // 在HTTP请求中包含一个"User-Agent: "头的字符串
                if (isset($_params['userAgent'][$key]))
                {
                	curl_setopt($ch, CURLOPT_USERAGENT, $_params['userAgent'][$key]);
                }

                /** 
                 * 设定 HTTP 请求中"Cookie: "部分的内容。
                 * 多个 cookie 用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
                 **/
                if (isset($_params['cookie'][$key]))
                {
                	curl_setopt($ch, CURLOPT_COOKIE, $_params['cookie'][$key]);
                }

                // 在HTTP请求头中"Referer: "的内容。
                if (isset($_params['referer'][$key]))
                {
                	curl_setopt($ch, CURLOPT_REFERER, $_params['referer'][$key]);
                }
                
                /** 
                 *  CURL_HTTP_VERSION_NONE (默认值，让 cURL 自己判断使用哪个版本)，
                 *  CURL_HTTP_VERSION_1_0 (强制使用 HTTP/1.0)或CURL_HTTP_VERSION_1_1 (强制使用 HTTP/1.1)。
                 **/ 
                if (isset($_params['http_version'][$key]))
                {
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, $_params['http_version'][$key]);
                }

                // HTTP 代理通道。 
                if (isset($_params['curlopt_proxy'][$key]))
                {
                	curl_setopt($ch, CURLOPT_PROXY, $_params['proxy'][$key]);
                }

                /** 
                 * 设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100') 
                 **/
                if (isset($_params['header'][$key]))
                {
                    if ( Sun_Array::isAssociativeArray($_params['header'][$key]) )
                    {
                        $_params['header'][$key] = Sun_Array::keyAndValueMerge($_params['header'][$key]);
                    }
                    
                	curl_setopt($ch, CURLOPT_HTTPHEADER, $_params['header'][$key]);
                }

                // 传递一个连接中需要的用户名和密码，格式为："[username]:[password]"。
                if ( isset($_params['userPwd'][$key]) )
                {
                    curl_setopt($ch, CURLOPT_USERPWD, $_params['userPwd'][$key]);
                }

                if ( 0 === stripos($url, 'https') )
                {
                    /**
                     * 禁用后cURL将终止从服务端进行验证。使用CURLOPT_CAINFO选项设置证书使用CURLOPT_CAPATH选项设置证书目录
                     * 如果CURLOPT_SSL_VERIFYPEER(默认值为2)被启用，CURLOPT_SSL_VERIFYHOST需要被设置成TRUE否则设置为FALSE。
                     **/
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    /**
                     * 1 检查服务器SSL证书中是否存在一个公用名(common name)。
                     *   译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。
                     * 2 检查公用名是否存在，并且是否与提供的主机名匹配。
                     **/
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                    
                    #curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
                    /*
                    //使用的SSL版本(2 或 3)。默认情况下PHP会自己检测这个值，尽管有些情况下需要手动地进行设置。
                    curl_setopt($ch, CURLOPT_SSLVERSION, 1);
                    // 一个SSL的加密算法列表。例如RC4-SHA和TLSv1都是可用的加密列表。
                    curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');*/
                }

                # 向curl批处理会话中添加单独的curl句柄
                curl_multi_add_handle($queue, $ch);
                $map[(string) $ch] = md5($url);
                $urls[(string) $ch] = $url;
            }

            do
            {
                while ( ($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM )
                {
                    continue;
                }

                if ($code != CURLM_OK) { break; }

                # 对刚刚完成的请求进行相关传输信息分析
                while ($done = curl_multi_info_read($queue))
                {
                    if ( $getInfo )
                    {
                        # 获取一个cURL连接资源句柄信息
                        $info = curl_getinfo($done['handle']);
                        $responses[$map[(string) $done['handle']]]['info'] = $info;
                    }

                    # 返回一个保护当前会话最近一次错误的字符串
                    $error = curl_error($done['handle']);
                    if ( !empty($error) )
                    {
                        $responses[$map[(string) $done['handle']]]['error'] = $error;
                    }

                    $results = curl_multi_getcontent($done['handle']);
                    
                    if (!empty($_params['callback']))
                    {
                        #var_dump($urls[(string) $done['handle']] . '----');
                    	$responses[$map[(string) $done['handle']]]['results'] = call_user_func($_params['callback'], $results, $urls[(string) $done['handle']]);
                    }
                    else
                    {
                        $responses[$map[(string) $done['handle']]]['results'] = $results;
                    }

                    # 移除刚刚完成的句柄资源
                    curl_multi_remove_handle($queue, $done['handle']);
                    curl_close($done['handle']);
                }

                // 等待所有cURL批处理中的活动连接
                if ($active > 0)
                {
                    curl_multi_select($queue, 0.5);
                }
            }
            while ($active);

            curl_multi_close($queue);
        }

        if (isset($responses[key($responses)]['error']))
        {
        	$responses = $responses[key($responses)]['error'];
        }
        else
        {
            # 如果只有数据资源时，只返回资源数据
            if ( count($responses) == 1 )
            {
                $responses = $responses[key($responses)]['results'];
            }
        }

        return $responses;
    }

    /**
     * 获取CURL处理参数
     *
     * @param mixed $params 传递
     */
    private static function getCurlParams( $params )
    {
        if ( empty($params) )
        {
            return false;
        }

        $_params = array();
        if ( is_array($params) )
        {
            if ( isset($params['urls']) )
            {
                # 一次获取多个网址的资源
                if ( is_array($params['urls']) )
                {
                    $_params = $params;
                }
                else
                {
                    # 一个网址中有多个参数
                    foreach ( $params as $key => $param )
                    {
                        if ( is_array($param) )
                        {
                            $_params[$key][] = $param;
                        }
                        else
                        {
                        	$_params[$key] = (array) $param;
                        }
                    }
                }
            }
            else # 传递一维数组的多个网址
            {
                $_params['urls'] = $params;
            }
        }
        else # 传递字符串型的单个网址
        {
            $_params['urls'][] = (string) $params;
        }

        return $_params;
    }
}
