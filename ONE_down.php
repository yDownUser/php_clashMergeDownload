<?php

require_once('core/core.php');

$cur_data_path = $year . '/' . $month . '/' . $cur_data;

//$subLink = 'target=clash&new_name=true&url=https%3A%2F%2Fraw.githubusercontent.com%2FJsnzkpg%2FJsnzkpg%2FJsnzkpg%2FJsnzkpg%7Chttps%3A%2F%2Fraw.githubusercontent.com%2FPawdroid%2FFree-servers%2Fmain%2Fsub&insert=false&config=https%3A%2F%2Fcdn.jsdelivr.net%2Fgh%2FSleepyHeeead%2Fsubconverter-config%40master%2Fremote-config%2Fspecial%2Fbasic.ini&emoji=false&list=true&tfo=false&scv=false&fdn=false&sort=false'; //订阅转换


$subLink = array(
    "https://raw.githubusercontent.com/Jsnzkpg/Jsnzkpg/Jsnzkpg/Jsnzkpg",
"https://github.com/aiboboxx/clashfree/raw/refs/heads/main/clash.yml",
    //"https://raw.githubusercontent.com/Pawdroid/Free-servers/main/sub",
    //'https://raw.githubusercontent.com/ermaozi/get_subscribe/main/subscribe/v2ray.txt',
    'https://raw.githubusercontent.com/peasoft/NoMoreWalls/master/list.yml',
    //'https://raw.githubusercontent.com/peasoft/NoMoreWalls/master/list.txt',
    'https://raw.githubusercontent.com/chengaopan/AutoMergePublicNodes/master/list.txt',
    'https://raw.githubusercontent.com/mfuu/v2ray/master/v2ray',
    //'https://x.nsa.cc/api/v1/client/subscribe?token=888fa1629a70b5492ba21d17b1f14177',
    // 'https://clashnode.com/wp-content/uploads/' . $cur_data_path . '.txt',
    //'https://v2rayshare.com/wp-content/uploads/' . $cur_data_path . '.txt',
);

$subParameter = array(
    "target" => "clash",
    "new_name" => "true",
    "url" => implode('|', $subLink),
    "insert" => "false",
    "emoji" => "false",
    "list" => "true",
    "tfo" => "false",
    "scv" => "false",
    "fdn" => "false",
    "sort" => "false",
    //"config"=>"https://cdn.jsdelivr.net/gh/SleepyHeeead/subconverter-config@master/remote-config/special/basic.ini",
);

$subUrlme = "";

$subQuery = http_build_query($subParameter);

$subWebsite = array(
    'https://api.wcc.best/sub?',
    'https://sub.xeton.dev/sub?',
    'https://api.dler.io/sub?',
    'https://sub.maoxiongnet.com/sub?',
);

function down_error($response)
{
    if (
        empty($response) || $response == 'No nodes were found!' || strlen($response) < 300 || strpos($response, 'Attention Required! | Cloudflare') !== false
    ) {
        return true;
    }
    return false;
}

//
//
$savePath = "one/cache_" . $cur_data . ".txt";

$redata = false;
foreach ($subWebsite as $key => $rs) {
    $subUrl = $rs . $subQuery;
    if ($redata == false) {
        $response = curl_down($subUrl, 0, 30);
        if (down_error($response)) {
            $response = curl_down($subUrl, 2, 30);
        }
        if (down_error($response)) {
            echo $subUrl . '文件下载失败！' . "\n";
            echo "\n";
            echo $response . "\n";
            echo "\n";
            echo "\n";
        } else {
            // 保存文件
            if (file_put_contents($savePath, $response)) {
                echo $subUrl . '文件成功！' . "\n";
                $redata = true;
            } else {
                echo $subUrl . '文件保存失败！' . "\n";
            }
        }
    }
}

if ($redata == false) {
    $response = curl_down($subUrlme, 1, 30);
    if (empty($response) || $response == 'No nodes were found!' || strlen($response) < 300) {
        echo $subUrlme . '文件下载失败！' . "\n";
        echo "\n";
        echo $response . "\n";
        echo "\n";
        echo "\n";
    } else {
        // 保存文件
        if (file_put_contents($savePath, $response)) {
            echo $subUrlme . '文件成功！' . "\n";
            $redata = true;
        } else {
            echo $subUrlme . '文件保存失败！' . "\n";
        }
    }
}




echo "\n";
echo '本次 下载任务 执行完毕';
echo "\n";
