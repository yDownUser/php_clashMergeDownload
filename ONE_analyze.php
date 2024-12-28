<?php
//header("Content-type: text/html; charset=utf-8"); 
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once('core/core.php');


$proxieDB = array();
$file = "one/cache_" . $cur_data . ".txt";
/* - name: ss_20230706_d36c7b142364d0c6429999d39a442b11_30033
  server: 103.177.33.180
  port: 443
  type: ss
  cipher: chacha20-ietf-poly1305
  password: 9e44e9ffe5c2
  plugin: v2ray-plugin
  plugin-opts:
    mode: ""
    host: ""
    path: ""
    tls: false
    mux: false
    skip-cert-verify: false
  udp: true
- name: ss_20230706_0a15546fb934938f7acc2c6d1a5b317f_61942
  server: 103.177.33.180
  port: 443
  type: ss
  cipher: chacha20-ietf-poly1305
  password: 9e44e9ffe5c2
  plugin: v2ray-plugin
  plugin-opts:
    mode: ""
    host: ""
    path: ""
    tls: false
    mux: false
    skip-cert-verify: false
  udp: true 
    - {name: 7|🇭🇰 香港 01 | 1x HK, server: free.2weradf.xyz, port: 36141, type: ss, cipher: ss, password: //Y2hhY2hhMjAtaWV0Zi1wb2x5MTMwNTo3MmE1MjRjNi1jZDg5LTQ5N2UtYTQ4Yy05MTYwNDA4OWQ3MzQ}
  
  
  */
$content = file_get_contents($file);

if ($content != false) {
    $content = str_replace(array(': !', '%'), array(': ！!', 'O百分比号A'), $content);
    try {
        $yamldata = Yaml::parse($content);
        print_r($yamldata);
    } catch (ParseException $exception) {
        $yamldata = array();
        print_r('Unable to parse the YAML string: %s', $exception->getMessage());
        echo  "\n";
    }
    if (isset($yamldata['proxies'])) {
        $proxieDB = $yamldata['proxies'];

        $proxiesJSON = array();
        $proxiesYAML = array();
        $i = 0;
        $t = 0;
        $proxiesDel = array();

        foreach ($proxieDB as $rss) {
            $st = 1;
            if ($rss["type"] == 'ss') {
                if (isset($rss["plugin"])) {
                    if ($rss["plugin"] == 'v2ray-plugin') {
                        $st = 0;
                    }
                }
                if (isset($rss["password"]) && isset($rss["cipher"])) {
                    if ($rss["cipher"] == 'ss' && strpos($rss["password"], "//Y") !== false) {
                        $ss_pwd = str_replace("//", "", $rss["password"]);
                        $ss_pswd = base64_decode($ss_pwd);
                        $ss_cf = explode(":", $ss_pswd);
                        $rss["cipher"] = $ss_cf[0];
                        $rss["password"] = $ss_cf[1];
                    }
                }
            }
            if ($st == 1) {
                if (isset($rss["password"])) {
                    $rss["password"] = str_replace(array('！!', 'O百分比号A', "://"), array('!', '%', ""), $rss["password"]);
                }
                unset($rss["name"]);
                $proxiesDel[] = $rss;
                $i++;
            }
        }
        // 使用 array_unique 函数去重复节点
        $uniqueNodes = array_unique($proxiesDel, SORT_REGULAR);

        foreach ($uniqueNodes as $rts) {
            $rts["name"] = $rts["type"] . '_' . $cur_data . '_' . $t . '_' . mt_rand(10001, 99999);
            $proxiesYAML[] = $rts;
            $proxiesJSON[$rts["name"]] = $rts;
            $t++;
        }

        $json = json_encode($proxiesJSON);
        $yaml = yaml_emit(array('proxies' => $proxiesYAML), YAML_UTF8_ENCODING);
        file_put_contents('one/Acache.json', $json);
        file_put_contents('one/Acache.yaml', $yaml);

        echo  "\n";
        echo "合计 $t 个,去重 " . $i - $t . " 个,总计 $i 个" . "\n";
        echo '搞定 下一步 测速';
        echo  "\n";
    } else {

        echo  '没找到proxies   ' . "\n";
        print_r($yamldata);
        echo "\n";
        echo "\n";
    }
} else {
    echo  $file . '打不开' . "\n";
}

echo "\n";
echo '本次 提取任务 执行完毕';
echo "\n";
