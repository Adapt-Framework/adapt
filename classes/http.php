<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined(ADAPT_STARTED) or die;
    
    class http extends base{
        
        protected $_connections;
        protected $_timeout;
        protected $_cookie_jar;
        protected $_handle_redirects = true;
        
        public function __construct(){
            parent::__construct();
            $this->_timeout = 30;
            $this->_cookie_jar = array();
        }
        
        public function __destruct(){
            
        }
        
        public function pget_handle_redirects(){
            return $this->_handle_redirects;
        }
        
        public function pset_handle_redirects($value){
            $this->_handle_redirects = $value;
        }
        
        public function pget_connections(){
            return $this->_connections;
        }
        
        public function pget_timeout(){
            return $this->_timeout;
        }
        
        public function pset_timeout($timeout){
            $this->_timeout = $timeout;
        }
        
        public function pget_cookie_jar(){
            return $this->_cookie_jar;
        }
        
        public function get($url, $headers = array()){
            return $this->request($url, 'get', $headers);
        }
        
        public function head($url, $headers = array()){
            return $this->request($url, 'head', $headers);
        }
        
        public function post($url, $data, $headers = array()){
            return $this->request($url, 'post', $headers, $data);
        }
        
        public function request($url, $type = 'get', $headers = array(), $data = null, $redirect_count = 0){
            $url = $this->parse_url($url);
            
            if ($socket = $this->get_connection($url['host'], $url['port'], $url['protocol'] == 'https' ? true : false)){
                
                if (in_array(strtolower($type), array('get', 'post', 'head'))){
                    $path = $url['path'];
                    if ($url['query_string'] != ""){
                        $path .= "?" . $url['query_string'];
                    }
                    $request = strtoupper($type) . " {$path} HTTP/1.1\r\n";
                    $headers = array_merge(
                        array(
                            'Host' => $url['host'],
                            'Accept-Encoding' => 'gzip, deflate'
                        ),
                        $headers
                    );
                    $headers['Host'] = $url['host'];
                    
                    /* Add Cookies */
                    foreach($this->_cookie_jar as $cookie){
                        $domain = $cookie['domain'];
                        $correct_domain = false;
                        $correct_path = false;
                        $expired = true;
                        
                        if (strlen($domain) < strlen($url['host'])){
                            if (substr($url['host'], strlen($url['host']) - strlen($domain)) == $domain){
                                $correct_domain = true;
                            }
                        }elseif(strlen($domain) == strlen($url['host'])){
                            if ($url['host'] == $domain){
                                $correct_domain = true;
                            }
                        }
                        
                        if (strlen($cookie['path']) <= strlen($url['path'])){
                            if (substr($url['path'], 0, strlen($cookie['path'])) == $cookie['path']){
                                $correct_path = true;
                            }
                        }
                        
                        if ($correct_domain && $correct_path){
                            /* Check the exipry time */
                            $date = new date($cookie['expires']);
                            
                            if ($date->is_future(true)){
                                $expired = false;
                            }
                        }
                        
                        if ($expired === false){
                            /* This is a valid cookie, we need to append it to our headers */
                            if (!isset($headers['Cookie'])){
                                $headers['Cookie'] = "{$cookie['name']}={$cookie['value']}";
                            }else{
                                $headers['Cookie'] .= "; {$cookie['name']}={$cookie['value']}";
                            }
                            
                        }
                    }
                    
                    /* Add the headers to the request */
                    foreach($headers as $key => $value){
                        $request .= "{$key}: {$value}\r\n";
                    }
                    
                    $request .= "\r\n";
                    
                    /* Add the data */
                    if (is_string($data)){
                        $request .= $data;
                    }elseif(is_assoc($data)){
                        //TODO: Url encode key pairs
                        $first = true;
                        foreach($data as $key => $value){
                            $key = urlencode($key);
                            $value = urlencode($value);
                            if (!$first){
                                $request .= "&";
                            }
                            $request .= "{$key}={$value}";
                        }
                    }
                    
                    
                    /* Send the request */
                    fwrite($socket, $request, strlen($request));
                    
                    /* Read the first line of the response */
                    $status = fgets($socket);
                    list($protocol_version, $status, $message) = explode(" ", $status, 3);
                    
                    if (in_array(strtoupper($protocol_version), array('HTTP/1.0', 'HTTP/1.1'))){
                        
                        $output = array(
                            'status' => $status,
                            'headers' => array(),
                            'content' => null
                        );
                        
                        /* Lets get the headers from the response */
                        $data = "";
                        while("\r\n" != ($data = fgets($socket))){
                            list($key, $value) = explode(":", $data, 2);
                            $key = trim(strtolower($key));
                            $value = trim($value);
                            $output['headers'][$key] = $value;
                        }
                        
                        /* Parse and store any cookies in the cookie jar */
                        if (isset($output['headers']['set-cookie'])){
                            $cookies = $output['headers']['set-cookie'];
                            if (!is_array($cookies)) $cookies = array($cookies);
                            foreach($cookies as $cookie){
                                $parts = explode(";", $cookie);
                                
                                list($name, $value) = explode("=", $parts[0], 2);
                                $name = trim($name);
                                $value = trim($value);
                                $meta = array(
                                    'name' => $name,
                                    'value' => $value
                                );
                                if (count($parts) > 0){
                                    for($i = 1; $i < count($parts); $i++){
                                        $parts[$i] = trim($parts[$i]);
                                        list($key, $value) = explode("=", $parts[$i], 2);
                                        $meta[$key] = $value;
                                    }
                                }
                                
                                if (isset($meta['expires'])){
                                    $d = new date();
                                    $d->set_date($meta['expires'], "D, d-M-y H:i:s");
                                    $meta['expires'] = $d->date('Y-m-d H:i:s');
                                }
                                
                                $found = false;
                                
                                for($i = 0; $i < count($this->_cookie_jar); $i++){
                                    $c = $this->_cookie_jar[$i];
                                    if ($c['domain'] == $meta['domain'] && $c['path'] == $meta['path'] && $c['name'] == $meta['name']){
                                        $this->_cookie_jar[$i] = $meta;
                                        $found = true;
                                    }
                                }
                                
                                if (!$found){
                                    $this->_cookie_jar[] = $meta;
                                }
                            }
                        }
                        
                        /* Retreive the content body if there is one */
                        $output['content'] = null;
                        
                        if (isset($output['headers']['content-length'])){
                            $length = intval($output['headers']['content-length']);
                            if ($length > 0){
                                $output['content'] = fread($socket, $length);
                            }
                            
                        }elseif(isset($output['headers']['transfer-encoding']) && strtolower($output['headers']['transfer-encoding']) == 'chunked'){
                            $output['content'] = "";
                            
                            while("\r\n" != ($data = fgets($socket))){
                                $data = trim($data);
                                $length = hexdec($data);
                                if (is_int($length) && $length > 0){
                                    while ($length > 0){
                                        $stream_data = fread($socket, $length);
                                        $length -= strlen($stream_data);
                                        $output['content'] .= $stream_data;
                                    }
                                    /* Remove the end of line sequence */
                                    fread($socket, 2);
                                }
                            }
                        }
                        
                        /* Decompress the content if needed */
                        if (!is_null($output['content'])){
                            if (isset($output['headers']['content-encoding'])){
                                switch(strtolower($output['headers']['content-encoding'])){
                                case "gzip":
                                    $output['content'] = gzdecode($output['content']);
                                    break;
                                case "deflate":
                                    $output['content'] = gzdeflate($output['content']);
                                    break;
                                }
                            }
                        }
                        
                        if ($this->_handle_redirects){
                            if ($redirect_count >= 5){
                                $this->error('Too many redirects');
                            }elseif (isset($output['headers']['location'])){
                                $redirect_count++;
                                
                                if (in_array($output['status'], array(301, 302, 303, 307, 308))){
                                    
                                    $location = $output['headers']['location'];
                                    $redirect_url = "";
                                    
                                    if (substr(strtolower($location), 0, 4) == 'http'){
                                        $redirect_url = $location;
                                    }elseif(substr($location, 0, 1) == '/'){
                                        $redirect_url = $url['protocol'] . '://' . $url['host'] . ':' . $url['port'] . $location;
                                    }else{
                                        //TODO: 
                                    }
                                    //print $redirect_url . "\n";
                                    if ($output['status'] == 303){
                                        $output = $this->request($redirect_url, 'get', $headers, $data, $redirect_count);
                                    }else{
                                        $output = $this->request($redirect_url, $type, $headers, $data, $redirect_count);
                                    }
                                }
                            }
                        }
                        
                        return $output;
                        
                    }else{
                        $this->error('Unknown response');
                    }
                    
                    
                }else{
                    $this->error('Unsupported request type');
                }
            }else{
                $this->error('Unable to connect to host');
            }
            
            return null;
        }
        
        
        
        public function get_connection($host, $port, $use_ssl = false){
            $key = $host . ":" . $port;
            
            if (isset($this->_connections[$key])){
                return $this->_connections[$key];
            }else{
                $error_number = null;
                $error_string = null;
                $handle = fsockopen($host, $port, $error_number, $error_string, $this->timeout);
                
                if ($use_ssl){
                    if (false == stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_SSLv3_CLIENT)){
                        $this->error('Failed to initialise SSL');
                        return null;
                    }
                }
                
                if (!$handle){
                    if ($error_number == 0){
                        $this->error("Unable to initialise socket");
                    }else{
                        $this->error("{$error_number}: {$error_string}");
                    }
                }else{
                    $this->_connections[$key] = $handle;
                    return $handle;
                }
            }
            
            return null;
        }
        
        public function parse_url($url){
            $output = array(
                'protocol' => 'http',
                'username' => null,
                'password' => null,
                'host' => null,
                'port' =>  80,
                'path' => "/",
                'query_string' => "",
                'params' => array(),
                'url' => $url
            );
            
            if (strpos($url, "?") !== false){
                list($url, $query_string) = explode("?", $url, 2);
                
                $output['query_string'] = $query_string;
                
                if (isset($query_string) && $query_string != ""){
                    $pairs = explode("&", $query_string);
                    foreach($pairs as $pair){
                        list($key, $value) = explode("=", $pair);
                        $output['params'][$key] = $value;
                    }
                }
            }
            
            $pattern = "/^(([A-Za-z]+):\/\/)?(([-_A-Za-z0-9]+)(:([^@]+))?@)?([^\/:]*)(:([0-9]+))?([^?]*)/";
            $matches = array();
            
            if (preg_match($pattern, $url, $matches)){
                if (isset($matches[2]) && $matches[2] != ""){
                    $output['protocol'] = strtolower($matches[2]);
                }
                
                if (isset($matches[4]) && $matches[4] != ""){
                    $output['username'] = $matches[4];
                }
                
                if (isset($matches[6]) && $matches[6] != ""){
                    $output['password'] = $matches[6];
                }
                
                if (isset($matches[7]) && $matches[7] != ""){
                    $output['host'] = strtolower($matches[7]);
                }
                
                if (isset($matches[9]) && $matches[9] != ""){
                    $output['port'] = $matches[9];
                }else{
                    if ($output['protocol'] == "https"){
                        $output['port'] = 443;
                    }
                }
                
                if (isset($matches[10]) && $matches[10] != ""){
                    $output['path'] = $matches[10];
                }
            }
            
            return $output;
        }
    }
    
    
}

?>