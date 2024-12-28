<?php

function ssToClash($ss_link)
{
    // 去除 ss:// 前缀
    $ss_data = substr($ss_link, 5);

    // 解析链接中的各个参数
    $parts = explode('@', $ss_data);
    // 解码SS链接
    $decoded_link = base64_decode($parts[0]);
    list($method, $password) = explode(':', $decoded_link);
    list($server, $params) = explode(':', $parts[1]);

    list($param1, $param2) = explode('#', $params);
    $contract = explode('/?', $param1);
    $name = filterEmoji(urldecode($param2));
    $port = intval($contract[0]);

    $param3 = isset($contract[1]) ? $contract[1] : '';

    $clash_config = [
        "name" => $name,
        "server" => $server,
        "port" => $port,
        "type" => "ss",
        "cipher" => $method,
        "password" => $password,
        # udp: true
    ];

    if (stripos($param3, 'plugin=obfs') !== false) {
        $clash_config['plugin'] = "obfs";
        $clash_config['plugin-opts'] = [
            //"mode" => 'http', # tls
            //"host" => 'bing.com', 
        ];
    } else if (stripos($param3, 'plugin=v2ray') !== false) {
        $clash_config['plugin'] = "v2ray-plugin";
        $clash_config['plugin-opts'] = [
            "mode" => 'websocket', # no QUIC now
            //"tls" => true, # wss
            "skip-cert-verify" => true,
            //"host" => ' bing.com',
            "path" =>  "/",
            "mux" => true,
            //"headers" => [
            //    'custom' => 'value'
            // ],
        ];
    }

    if ($param3 != '') {
        dataWrite($ss_link, LOG_DATA);
        dataWrite(yaml_emit($clash_config), LOG_DATA);
    }
    return $clash_config;
}

function vmessToClash($vmess_link)
{
    // 解码链接并解析参数
    $vmess_data = explode("://", $vmess_link)[1];
    //$vmess_data = urldecode($vmess_data);
    $vmess_data = base64_decode($vmess_data);
    $params = json_decode($vmess_data, true);

    // 构建 Clash 规则
    $clash_config = [
        "name" => filterEmoji($params["ps"]),
        "server" => $params["add"],
        "port" => intval($params["port"]),
        "type" => "vmess",
        "uuid" => $params["id"],
        "alterId" => isset($params["aid"]) ? intval($params["aid"]) : 0,
        "cipher" => isset($params["type"]) ? $params["type"] : "auto",

        // "network" => isset($params["net"]) ? $params["net"] : "tcp",
    ];
    $clash_config["cipher"] = $clash_config["cipher"] == 'none' ? "auto" : $clash_config["cipher"];

    if (isset($params["tls"])) {
        $clash_config["tls"] =  $params["tls"] == 'tls' ? true : false;
        $clash_config["skip-cert-verify"] = false; //$clash_config["tls"];
    }

    if (isset($params["net"])) {
        $clash_config["network"] = $params["net"];
        if ($clash_config["network"] == 'ws') {
            $clash_config["ws-opts"] = array(
                "path" => isset($params["path"]) ? $params["path"] : "/",
                "headers" => array(
                    "Host" => isset($params["host"]) ? $params["host"] : ""
                )
            );
            if (count($params) > 11) {
                dataWrite($vmess_link, LOG_DATA);
                dataWrite(yaml_emit($clash_config), LOG_DATA);
            }
        } else if ($clash_config["network"] != 'tcp') {
            dataWrite($vmess_link, LOG_DATA);
            dataWrite(yaml_emit($clash_config), LOG_DATA);
        }
    }
    // 返回 Clash 规则
    return $clash_config;
}

function trojanToClash($trojan_link)
{
    // 解码 Trojan 链接
    $trojan_data = $trojan_link; //linkToBase64($trojan_link);
    $trojan_decoded = substr($trojan_data, 9); // base64_decode(substr($trojan_data, 9));

    // 提取参数
    $parts = explode('@', $trojan_decoded);
    $password = $parts[0];
    $server_port = $parts[1];
    $server_parts = explode(':', $server_port);
    $server = $server_parts[0];
    $query = parse_url($server_parts[1]);
    $port = intval($query["path"]); //intval($server_parts[1]);
    $name = filterEmoji(urldecode($query["fragment"]));
    parse_str($query['query'], $params);
    // 构建 Clash 配置
    $clash_config = [
        "name" => $name,
        'server' => $server,
        'port' => $port,
        "type" => "trojan",
        'password' => $password,
    ];

    if (isset($params["type"])) {
        $clash_config["network"] = $params["type"];
        if ($clash_config["network"] == 'ws') {
            $clash_config["ws-opts"] = array(
                "path" => isset($params["path"]) ? $params["path"] : "/",
                "headers" => array(
                    "host" => isset($params["host"]) ? $params["host"] : ""
                )
            );
        } else if ($clash_config["network"] == 'grpc') {
            $clash_config["grpc-opts"] = array(
                "grpc-service-name" => isset($params["serviceName"]) ? $params["serviceName"] : ""
            );
        }
    }

    if (!empty($params["sni"])) {
        $clash_config["sni"] = $params["sni"];
    }

    if (isset($params["allowInsecure"])) {
        $clash_config["skip-cert-verify"] = empty($params["allowInsecure"]) ? true : false;
    }

    if (count($params) > 2) { //$clash_config["network"] != 'tcp' && 
        dataWrite($trojan_link, LOG_DATA);
        dataWrite(yaml_emit($clash_config), LOG_DATA);
    }

    return $clash_config;
}

function ssrToClash($ssr_link)
{
    // 解码 SSR 链接
    $ssr_data = linkToBase64($ssr_link);
    $ssr_decoded = base64_decode(substr($ssr_data, 6));

    // 提取参数
    $parts = explode(':', $ssr_decoded);
    $server = $parts[0];
    $port = intval($parts[1]);
    $protocol = $parts[2];
    $method = $parts[3];
    $obfs = $parts[4];

    $data = explode('/?', $parts[5]);

    $password = $data[0];
    //$params = isset($data[1]) ? $data[1] : '';
    //$obfs_param = '';
    //$protocol_param = '';
    //解析链接
    // $parsedUrl = parse_url($url);
    // 解析参数
    /* $params_parts = explode('&', $params);
    foreach ($params_parts as $param) {
        if (strpos($param, 'obfsparam=') === 0) {
            $obfs_param = str_replace('obfsparam=', '', $param);
            $clash_config['obfs_param'] = $obfs_param;
        } elseif (strpos($param, 'protoparam=') === 0) {
            $protocol_param = str_replace('protoparam=', '', $param);
            $clash_config['protocol_param'] = $protocol_param;
        }
    } */
    $query = isset($data[1]) ? parse_url('http://127.0.0.1/?' . $data[1])['query'] : '';
    parse_str($query, $params);

    $name = filterEmoji(base64_decode(linkToBase64($params["remarks"])));
    // 构建 Clash 配置
    $clash_config = [
        "name" => $name,
        'server' => $server,
        'port' => $port,
        'type' => "ssr",
        'password' => $password,
        'cipher' => $method,
        'protocol' => $protocol,
        'obfs' => $obfs,
        //'udp' => true
    ];
    if (!empty($params["obfsparam"])) {
        $clash_config["obfsparam"] = $params["obfsparam"];
    }
    if (!empty($params["protoparam"])) {
        $clash_config["protoparam"] = $params["protoparam"];
    }

    return $clash_config;
}

/*
   # Shadowsocks
  # The supported ciphers (encryption methods):
  #   aes-128-gcm aes-192-gcm aes-256-gcm
  #   aes-128-cfb aes-192-cfb aes-256-cfb
  #   aes-128-ctr aes-192-ctr aes-256-ctr
  #   rc4-md5 chacha20-ietf xchacha20
  #   chacha20-ietf-poly1305 xchacha20-ietf-poly1305
  - name: "ss1"
    type: ss
    server: server
    port: 443
    cipher: chacha20-ietf-poly1305
    password: "password"
    # udp: true

  - name: "ss2"
    type: ss
    server: server
    port: 443
    cipher: chacha20-ietf-poly1305
    password: "password"
    plugin: obfs
    plugin-opts:
      mode: tls # or http
      # host: bing.com

  - name: "ss3"
    type: ss
    server: server
    port: 443
    cipher: chacha20-ietf-poly1305
    password: "password"
    plugin: v2ray-plugin
    plugin-opts:
      mode: websocket # no QUIC now
      # tls: true # wss
      # skip-cert-verify: true
      # host: bing.com
      # path: "/"
      # mux: true
      # headers:
      #   custom: value

  # vmess
  # cipher support auto/aes-128-gcm/chacha20-poly1305/none
  - name: "vmess"
    type: vmess
    server: server
    port: 443
    uuid: uuid
    alterId: 32
    cipher: auto
    # udp: true
    # tls: true
    # skip-cert-verify: true
    # servername: example.com # priority over wss host
    # network: ws
    # ws-opts:
    #   path: /path
    #   headers:
    #     Host: v2ray.com
    #   max-early-data: 2048
    #   early-data-header-name: Sec-WebSocket-Protocol

  - name: "vmess-h2"
    type: vmess
    server: server
    port: 443
    uuid: uuid
    alterId: 32
    cipher: auto
    network: h2
    tls: true
    h2-opts:
      host:
        - http.example.com
        - http-alt.example.com
      path: /

  - name: "vmess-http"
    type: vmess
    server: server
    port: 443
    uuid: uuid
    alterId: 32
    cipher: auto
    # udp: true
    # network: http
    # http-opts:
    #   # method: "GET"
    #   # path:
    #   #   - '/'
    #   #   - '/video'
    #   # headers:
    #   #   Connection:
    #   #     - keep-alive

  - name: vmess-grpc
    server: server
    port: 443
    type: vmess
    uuid: uuid
    alterId: 32
    cipher: auto
    network: grpc
    tls: true
    servername: example.com
    # skip-cert-verify: true
    grpc-opts:
      grpc-service-name: "example"

  # socks5
  - name: "socks"
    type: socks5
    server: server
    port: 443
    # username: username
    # password: password
    # tls: true
    # skip-cert-verify: true
    # udp: true

  # http
  - name: "http"
    type: http
    server: server
    port: 443
    # username: username
    # password: password
    # tls: true # https
    # skip-cert-verify: true
    # sni: custom.com

  # Snell
  # Beware that there's currently no UDP support yet
  - name: "snell"
    type: snell
    server: server
    port: 44046
    psk: yourpsk
    # version: 2
    # obfs-opts:
      # mode: http # or tls
      # host: bing.com

  # Trojan
  - name: "trojan"
    type: trojan
    server: server
    port: 443
    password: yourpsk
    # udp: true
    # sni: example.com # aka server name
    # alpn:
    #   - h2
    #   - http/1.1
    # skip-cert-verify: true

  - name: trojan-grpc
    server: server
    port: 443
    type: trojan
    password: "example"
    network: grpc
    sni: example.com
    # skip-cert-verify: true
    udp: true
    grpc-opts:
      grpc-service-name: "example"

  - name: trojan-ws
    server: server
    port: 443
    type: trojan
    password: "example"
    network: ws
    sni: example.com
    # skip-cert-verify: true
    udp: true
    # ws-opts:
      # path: /path
      # headers:
      #   Host: example.com

  # ShadowsocksR
  # The supported ciphers (encryption methods): all stream ciphers in ss
  # The supported obfses:
  #   plain http_simple http_post
  #   random_head tls1.2_ticket_auth tls1.2_ticket_fastauth
  # The supported supported protocols:
  #   origin auth_sha1_v4 auth_aes128_md5
  #   auth_aes128_sha1 auth_chain_a auth_chain_b
  - name: "ssr"
    type: ssr
    server: server
    port: 443
    cipher: chacha20-ietf
    password: "password"
    obfs: tls1.2_ticket_auth
    protocol: auth_sha1_v4
    # obfs-param: domain.tld
    # protocol-param: "#"
    # udp: true
*/