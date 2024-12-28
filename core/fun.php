<?php

function exceptionHandler()
{
    error_reporting(E_ALL ^ E_NOTICE);
    date_default_timezone_set('Asia/Shanghai'); //设置时区
    ini_set('display_errors', 0); //将错误记录到日志
    //ini_set('error_log', 'D:' . date('Y-m-d') . '_weblog.txt');
    ini_set('error_log',  LOG_PATH . date('Y-m-d') . '_weblog.txt');
    ini_set('log_errors', 1); //开启错误日志记录
    ini_set('ignore_repeated_errors', 1); //不重复记录出现在同一个文件中的同一行代码上的错误信息。
    $user_defined_err = error_get_last();
    if (isset($user_defined_err['type'])) {
        if ($user_defined_err['type'] > 0) {
            switch ($user_defined_err['type']) {
                case 1:
                    $user_defined_errType = '致命的运行时错误(E_ERROR)';
                    break;
                case 2:
                    $user_defined_errType = '非致命的运行时错误(E_WARNING)';
                    break;
                case 4:
                    $user_defined_errType = '编译时语法解析错误(E_PARSE)';
                    break;
                case 8:
                    $user_defined_errType = '运行时提示(E_NOTICE)';
                    break;
                case 16:
                    $user_defined_errType = 'PHP内部错误(E_CORE_ERROR)';
                    break;
                case 32:
                    $user_defined_errType = 'PHP内部警告(E_CORE_WARNING)';
                    break;
                case 64:
                    $user_defined_errType = 'Zend脚本引擎内部错误(E_COMPILE_ERROR)';
                    break;
                case 128:
                    $user_defined_errType = 'Zend脚本引擎内部警告(E_COMPILE_WARNING)';
                    break;
                case 256:
                    $user_defined_errType = '用户自定义错误(E_USER_ERROR)';
                    break;
                case 512:
                    $user_defined_errType = '用户自定义警告(E_USER_WARNING)';
                    break;
                case 1024:
                    $user_defined_errType = '用户自定义提示(E_USER_NOTICE)';
                    break;
                case 2048:
                    $user_defined_errType = '代码提示(E_STRICT)';
                    break;
                case 4096:
                    $user_defined_errType = '可以捕获的致命错误(E_RECOVERABLE_ERROR)';
                    break;
                case 8191:
                    $user_defined_errType = '所有错误警告(E_ALL)';
                    break;
                default:
                    $user_defined_errType = '未知类型';
                    break;
            }
            $msg = sprintf('%s %s %s %s %s', date("Y-m-d H:i:s"), $user_defined_errType, $user_defined_err['message'], $user_defined_err['file'], $user_defined_err['line']);
            error_log($msg, 0);
        }
    }
}

register_shutdown_function('exceptionHandler');
//写入文件
function dataWrite($str, $file)
{
    $fp = fopen($file, 'a'); //opens file in append mode  
    fwrite($fp, $str . "\n");
    fclose($fp);
}
// 过滤emoji表情的函数
function filterEmoji($str)
{
    return $str;
    $str = preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str
    );
    return $str;
}


function curl_down($url, $timeout = 15)
{
    // 初始化 cURL
    $curl = curl_init($url);

    // 设置选项
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // 如果服务器证书无效，可忽略 SSL 验证（不推荐在生产环境中使用）
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // 设置超时时间秒

    // 执行请求并获取响应
    $response = curl_exec($curl);
    // 检查是否出现错误
    if ($response === false) {
        dataWrite($url . '  cURL Error: ' . curl_error($curl), LOG_PATH . date('Y-m-d') . '_curlerror.txt');
        //exit;
    }

    // 关闭 cURL 资源
    curl_close($curl);
    return $response;
}

function curl_post($url, $data = array(), $timeout = 15)
{
    // 创建一个 cURL 资源
    $curl = curl_init();
    // 设置请求的 URL
    curl_setopt($curl, CURLOPT_URL, $url);
    // 设置为 POST 请求
    curl_setopt($curl, CURLOPT_POST, true);
    // 设置 POST 数据
    if (!empty($data)) {
        if (is_array($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    // 如果服务器证书无效，可忽略 SSL 验证（不推荐在生产环境中使用）
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // 设置超时时间秒

    // 执行请求并获取响应
    $response = curl_exec($curl);
    // 检查是否出现错误
    if ($response === false) {
        dataWrite($url . '  cURL Error: ' . curl_error($curl), LOG_PATH . date('Y-m-d') . '_curlerror.txt');
        //exit;
    }

    // 关闭 cURL 资源
    curl_close($curl);
    return $response;
}
//url替换为base
function linkToBase64($link)
{
    return str_replace(array('-', '_'), array('+', '/'), $link);
}

function printBr($data)
{
    print_r($data);
    echo "\n<br>";
}

function formatBandwidth($v)
{
    if ($v <= 0) {
        return "";
    }
    if ($v < 1024) {
        return sprintf("%.02fB/s", $v);
    }
    $v /= 1024;
    if ($v < 1024) {
        return sprintf("%.02fKB/s", $v);
    }
    $v /= 1024;
    if ($v < 1024) {
        return sprintf("%.02fMB/s", $v);
    }
    $v /= 1024;
    if ($v < 1024) {
        return sprintf("%.02fGB/s", $v);
    }
    $v /= 1024;
    return sprintf("%.02fTB/s", $v);
}

function analyzeLinks($data)
{
    $re = array();
    $arr = explode("\n", $data);
    foreach ($arr as $key => $rs) {
        if ($rs != '') {
            $kt = substr($rs, 0, 10);
            if (stripos($kt, 'vmess://') !== false) {
                $re[] = vmessToClash($rs);
            } else if (stripos($kt, 'ss://') !== false) {
                $re[] = ssToClash($rs);
            } else if (stripos($kt, 'ssr://') !== false) {
                $re[] = ssrToClash($rs);
            } else if (stripos($kt, 'trojan://') !== false) {
                $re[] = trojanToClash($rs);
            } else {
                dataWrite('什么协议都没有：' . $rs, LOG_DATA);
            }
        }
    }
    return $re;
}
