<?php

/**
 * Adapt Framework
 *
 * The MIT License (MIT)
 *   
 * Copyright (c) 2017 Matt Bruton
 * Authored by Matt Bruton (matt.bruton@gmail.com)
 *   
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *   
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *   
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     adapt
 * @author      Matt Bruton <matt.bruton@gmail.com>
 * @copyright   2017 Matt Bruton <matt.bruton@gmail.com>
 * @license     https://opensource.org/licenses/MIT     MIT License
 * @link        http://www.adpatframework.com
 *
 */

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;

    /**
     * Native HTTP / HTTPS support without any extensions.
     *
     * @property boolean $handle_redirects
     * Should HTTP redirects automatically be followed? Default is true.
     * @property-read array $connections
     * Returns an array of connections.
     * @property integer $timeout
     * How many seconds should we wait before declaring the connection unreachable? Default is 30.
     * @property-read array $cookie_jar
     * Returns an array of cookies used by this class.
     */
    class http extends base{

        const GET = 'GET';
        const POST = 'POST';
        const PUT = 'PUT';
        const HEAD = 'HEAD';
        const DELETE = 'DELETE';
        const PATCH = 'PATCH';
        
        /**
         * @ignore
         */
        protected $_connections;
        
        /**
         * @ignore
         */
        protected $_timeout;
        
        /**
         * @ignore
         */
        protected $_cookie_jar;
        
        /**
         * @ignore
         */
        protected $_handle_redirects = true;
        
        /**
         * Constructor
         */
        public function __construct(){
            parent::__construct();
            $this->_timeout = 30;
            $this->_cookie_jar = array();
        }
        
        
        /**
         * @ignore
         */
        public function __destruct(){
            /** @todo Consider closing all open connections */
        }
        
        /**
         * @ignore
         */
        public function pget_handle_redirects(){
            return $this->_handle_redirects;
        }
        
        /**
         * @ignore
         */
        public function pset_handle_redirects($value){
            $this->_handle_redirects = $value;
        }
        
        /**
         * @ignore
         */
        public function pget_connections(){
            return $this->_connections;
        }
        
        /**
         * @ignore
         */
        public function pget_timeout(){
            return $this->_timeout;
        }
        
        /**
         * @ignore
         */
        public function pset_timeout($timeout){
            $this->_timeout = $timeout;
        }
        
        /**
         * @ignore
         */
        public function pget_cookie_jar(){
            return $this->_cookie_jar;
        }
        
        /**
         * Performs a HTTP Get request with optional headers.
         *
         * <code>
         * $http = new http();
         *
         * $response = $http->get('http://www.example.com');
         * if ($response['status'] == 200){
         *      print $response['content'];
         * }
         * </code>
         * 
         * @access public
         * @param string $url
         * The URL to get, for example: http://www.example.com
         * @param array $headers
         * Optionally an array of headers to send with the request
         * @return array
         * Returns an array containing the status, headers and the content.
         */
        public function get($url, $headers = array()){
            return $this->request($url, self::GET, $headers);
        }
        
        /**
         * Performs a HTTP Head request with optional headers.
         *
         * <code>
         * $http = new http();
         *
         * $response = $http->head('http://www.example.com');
         * if ($response['status'] == 200){
         *      print_r($response['headers']);
         * }
         * </code>
         * 
         * @access public
         * @param string $url
         * The URL to get, for example: http://www.example.com
         * @param array $headers
         * Optionally an array of headers to send with the request
         * @return array
         * Returns an array containing the status and headers.
         */
        public function head($url, $headers = array()){
            return $this->request($url, self::HEAD, $headers);
        }
        
        /**
         * Performs a HTTP Post request.
         *
         * <code>
         * $http = new http();
         *
         * $response = $http->post('http://www.example.com', '<xml></xml>', ['content-type: text/xml']);
         * if ($response['status'] == 200){
         *      print $response['content'];
         * }
         * </code>
         * 
         * @access public
         * @param string $url
         * The URL to get, for example: http://www.example.com
         * @param mixed $data
         * The data to post.
         * @param array $headers
         * Optionally an array of headers to send with the request
         * @return array
         * Returns an array containing the status, headers and content.
         */
        public function post($url, $data, $headers = array()){
            return $this->request($url, self::POST, $headers, $data);
        }

        /**
         * Performs a HTTP Put request.
         *
         * <code>
         * $http = new http();
         *
         * $response = $http->put('http://example.com/post/1', json_encode(['foo' => 'bar']), ['Content-Type: application/json']);
         * if ($response['status'] == 200) {
         *      print $response['content'];
         * }
         * </code>
         *
         * @param $url
         * @param $data
         * @param array $headers
         * @return array
         */
        public function put($url, $data, $headers = array()){
            return $this->request($url, self::PUT, $headers, $data);
        }

        /**
         * Performs a HTTP Delete request.
         *
         * <code>
         * $http = new http();
         *
         * $response = $http->delete('http://example.com/post/1', ['auth_token' => 'example'], ['Content-Type: application/json']);
         * if ($response['status'] == 200) {
         *      print $response['content'];
         * }
         * </code>
         *
         *
         * @param $url
         * @param $data
         * @param array $headers
         * @return array
         */
        public function delete($url, $data = '', $headers = array()){
            return $this->request($url, self::DELETE, $headers, $data);
        }

        /**
         * Performs a HTTP Patch request.
         *
         * <code>
         * $http = new http();
         *
         * $response = $http->patch('http://example.com/post/1', ['auth_token' => 'example'], ['Content-Type: application/json']);
         * if ($response['status'] == 200) {
         *      print $response['content'];
         * }
         * </code>
         *
         *
         * @param $url
         * @param $data
         * @param array $headers
         * @return array
         */
        public function patch($url, $data, $headers = array()){
            return $this->request($url, self::PATCH, $headers, $data);
        }
        
        /**
         * Performs a HTTP request
         *
         * @access public
         * @param string $url
         * The URL to make the request against
         * @param string $type
         * The HTTP request type, eg, get, post or head.
         * @param array $headers
         * Optionally include and array of headers
         * @param mixed $data
         * Optionally data to post.
         * @param integer $redirect_count
         * Used internally to track redirects and prevents redirect loops
         * from occuring.
         * @return array
         * Returns an array containing the status, content and headers.
         */
        public function request($url, $type = GET, $headers = array(), $data = null, $redirect_count = 0){
            $url = $this->parse_url($url);
            
            if ($socket = $this->get_connection($url['host'], $url['port'], $url['protocol'] == 'https' ? true : false)){
                
                if (in_array(strtoupper($type), array(self::GET, self::POST, self::PUT, self::HEAD, self::DELETE, self::PATCH))){
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
                            if ($cookie['expires']){
                                $date = new date($cookie['expires']);
                                
                                if ($date->is_future(true)){
                                    $expired = false;
                                }
                            }else{
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
                    
                    /* Are we posting? */
                    $payload = "";
                    
                    if (in_array($type, [self::POST, self::PUT, self::PATCH])){
                        /* Add the data */
                        if(is_assoc($data)){
                            $first = true;
                            foreach($data as $key => $value){
                                $key = urlencode($key);
                                $value = urlencode($value);
                                if (!$first){
                                    $payload .= "&";
                                }
                                $payload .= "{$key}={$value}";
                                $first = false;
                            }
                        }else{
                            $payload .= $data;
                        }
                        
                        if (strlen($payload)){
                            $headers['Content-Length'] = strlen($payload);
                        }
                    }
                    
                    /* Add the headers to the request */
                    foreach($headers as $key => $value){
                        $request .= "{$key}: {$value}\r\n";
                    }
                    
                    $request .= "\r\n" . $payload;
                    
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
                                    'value' => $value,
                                    'domain' => $url['host']
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
                            
                            $output['content'] = "";
                            if ($length > 0){
                                while ($length > 0){
                                    $stream_data = fread($socket, $length);
                                    $length -= strlen($stream_data);
                                    $output['content'] .= $stream_data;
                                }
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
                        
                        /* Connection has been close from the server*/
                        if(isset($output['headers']['connection']) && $output['headers']['connection'] == 'close'){
                            $this->close_connection($url['host'], $url['port']);
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
                                        /* Handle complete URL's */
                                        $redirect_url = $location;
                                    }elseif(substr($location, 0, 1) == '/'){
                                        /* Handle absoulte paths */
                                        $redirect_url = $url['protocol'] . '://' . $url['host'] . ':' . $url['port'] . $location;
                                    }else{
                                        /* Handle relative paths */
                                        $redirect_url = $url['protocol'] . '://' . $url['host'] . ':' . $url['port'] . $url['path'] . '/' . $location;
                                    }
                                    
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
        
        /**
         * Opens a connection to a server and returns the socket handle.
         *
         * @access public
         * @param string $host
         * The host name you wish to connect to.
         * @param integer $port
         * The port to open the connection on
         * @param boolean $use_ssl
         * Should the connection be made over SSL?
         * @return null|resource
         * When successful the socket handle is returned.
         */
        public function get_connection($host, $port, $use_ssl = false){
            $key = $host . ":" . $port;
            
            if (isset($this->_connections[$key])){
                return $this->_connections[$key];
            }else{
                $error_number = null;
                $error_string = null;

                $handle = fsockopen($host, $port, $error_number, $error_string, $this->timeout);

                if ($use_ssl){
                    if (false == stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_ANY_CLIENT)){
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
        /**
         * Closes a connection to a server.
         *
         * @access public
         * @param string $host
         * The host name you wish to close your connection to.
         * @param integer $port
         * The port to close the connection on
         * @return null|resource
         * When successful the socket handle is returned.
         */
        public function close_connection($host, $port){
            $key = $host . ":" . $port;
            if(isset($this->_connections[$key])){
                unset($this->_connections[$key]);
            }
        }

        /**
         * Breaks a URL down into it's composite parts and returns
         * as an array.
         *
         * @access public
         * @param string $url
         * The URL to be parsed
         * @return array
         * Returns an array of the URL parts.
         */
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
