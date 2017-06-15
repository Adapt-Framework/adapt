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
 * @copyright   2016 Matt Bruton <matt.bruton@gmail.com>
 * @license     https://opensource.org/licenses/MIT     MIT License
 * @link        http://www.adpatframework.com
 *
 */


namespace adapt{
    
    defined('ADAPT_STARTED') or die;
    
    class repository extends base{
        
        const REPOSITORY_URL = "https://repository.adaptframework.com/v1";
        
        protected $_url;
        protected $_session_token;
        protected $_user;
        protected $_permissions;
        protected $_http;
        
        public function __construct($username = null, $password = null){
            parent::__construct();
            
            $this->_http = new http();
            //$this->_url = $this->setting('repository.url');
            $this->_url = self::REPOSITORY_URL;
            
            if (is_null($username)){
                $username = $this->setting('repository.username');
            }
            
            if (is_null($password)){
                $password = $this->setting('repository.password');
            }
            
            if (isset($username) && isset($password)){
                $this->login($username, $password);
            }
        }
        
        public function pget_url(){
            return $this->_url;
        }
        
        public function pset_url($url){
            $this->_url = $url;
        }
        
        public function pget_session_token(){
            return $this->_session_token;
        }
        
        public function pset_session_token($session_token){
            $this->_session_token = $session_token;
        }
        
        public function pget_user(){
            return $this->_user;
        }
        
        public function pset_user($user){
            $this->_user = $user;
        }
        
        public function pget_permissions(){
            return $this->_permissions;
        }
        
        public function pset_permissions($permissions){
            $this->_permissions = $permissions;
        }
        
        public function pget_http(){
            return $this->_http;
        }
        
        public function pset_http($http){
            $this->_http = $http;
        }
        
        protected function _request($endpoint, $payload, $content_type = "application/json"){
            if (!is_null($this->session_token)){
                $payload['token'] = $this->session_token;
            }
            
            if ($content_type == "application/json" && is_array($payload)){
                $payload = json_encode($payload);
            }elseif ($content_type != "application/json"){
                $endpoint .= "?token=" . $this->session_token;
            }
            
            $response = $this->http->post($this->url . $endpoint, $payload, ['content-type' => $content_type]);
            
            if ($response && is_array($response)){
                switch($response['status']){
                case '200':
                    if (isset($response['headers']['content-type'])){
                        if (strtolower($response['headers']['content-type']) == "application/json"){
                            if (is_json($response['content'])){
                                $response['content'] = json_decode($response['content'], true);
                                if ($response['content']['status'] == "failed"){
                                    $this->error($response['content']['error']['message']);
                                    /* We are not returning so the the calling method can process the error */
                                }
                            }else{
                                $this->error("Invalid response from the repository");
                                return false;
                            }
                        }
                    }
                    break;
                case '403':
                    $this->error("You are not authorised to access this resource");
                    return false;
                case '404':
                    $this->error("Resource not found");
                    return false;
                case '500':
                    $this->error("Unknown repository error");
                    return false;
                }
            }else{
                $this->error($this->http->errors(true));
                return false;
            }
            
            return $response;
        }
        
        protected function _has_permission($permission){
            if (is_array($this->permissions)){
                return in_array($permission, $this->permissions);
            }
            
            return false;
        }
        
        public function login($email_address, $password){
            $payload = [
                'email_address' => $email_address,
                'password' => $password
            ];
            
            $response = $this->_request("/login", $payload);
            
            if (is_array($response) && $response['content']['status'] == 'success'){
                if (isset($response['content']['information']['token'])){
                    $this->session_token = $response['content']['information']['token'];
                    $this->user = $response['content']['information']['user'];
                    $this->permissions = $response['content']['information']['permissions'];
                    return true;
                }
            }
            
            return false;
        }
        
        public function change_password($password){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = [
                'user' => [
                    'password' => $password
                ]
            ];
            
            $response = $this->_request("/change-password", $payload);
            
            if ($response['content']['status'] == "success"){
                return true;
            }
            
            return false;
        }
        
        public function password_policy(){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $response = $this->_request("/password-policy", []);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['policy'];
            }
            
            return [];
        }
        
        public function request_password_reset($email){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = [
                'email' => $email
            ];
            
            $response = $this->_request("/request-password-reset", $payload);
            
            if (isset($response['content']['status']) && $response['content']['status'] == "success"){
                return true;
            }
            
            return false;
        }
        
        public function password_reset($password_reset_token, $password){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = [
                'password_reset_token' => $password_reset_token,
                'password' => $password
            ];
            
            $response = $this->_request("/password-reset", $payload);
            
            if (isset($response['content']['status']) && $response['content']['status'] == "success"){
                return true;
            }
            
            return false;
        }
        
        public function register($system_guid, $system_key, $email, $username, $password){
            // Protected via a hash (system_guid, system_key)
            $payload = [
                'contact_email' => [
                    'email' => $email
                ],
                'user' => [
                    'username' => $username,
                    'password' => $password,
                ],
                'hash' => [
                    'id' => $system_guid,
                    'sha1' => sha1($system_guid . $system_key . $email . $username . $password)
                ]
            ];
            
            $response = $this->_request("/register", $payload);
            
            if ($response['content']['status'] == "success"){
                return true;
            }
            
            return false;
        }
        
        public function installer($application_name = "adapt_setup", $settings = []){
            
        }
        
        public function list_bundle_types($page = 1, $items_per_page = 50, $order_by = 'name', $search_string = ''){
            $payload = [
                'filters' => [
                    'page' => $page,
                    'items_per_page' => $items_per_page,
                    'order_by' => $order_by,
                    //'q' => $search_string
                ]
            ];
            
            $response = $this->_request("/bundle-types", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['results'];
            }
            
            return false;
        }
        
        public function create_bundle_type($name, $label, $description){
            if (!$this->_has_permission('can_create_bundle_types')){
                $this->error('Not authorised');
                return false;
            }
            
            $payload = [
                'repository_bundle_type' => [
                    'name' => $name,
                    'label' => $label,
                    'description' => $description
                ]
            ];
            
            $response = $this->_request("/bundle-types/create", $payload);
            
            if ($response['content']['status'] == 'success'){
                return true;
            }
            
            return false;
        }
        
        public function update_bundle_type($name, $label, $description){
            if (!$this->_has_permission('can_update_bundle_types')){
                $this->error('Not authorised');
                return false;
            }
            
            $payload = [
                'repository_bundle_type' => [
                    'name' => $name,
                    'label' => $label,
                    'description' => $description
                ]
            ];
            
            $response = $this->_request("/bundle-types/update", $payload);
            
            if ($response['content']['status'] == 'success'){
                return true;
            }
            
            return false;
        }
        
        public function delete_bundle_type($name){
            if (!$this->_has_permission('can_delete_bundle_types')){
                $this->error('Not authorised');
                return false;
            }
            
            $payload = [
                'repository_bundle_type' => [
                    'name' => $name
                ]
            ];
            
            $response = $this->_request("/bundle-types/delete", $payload);
            
            if ($response['content']['status'] == 'success'){
                return true;
            }
            
            return false;
        }
        
        public function list_bundles($page = 1, $items_per_page = 50, $type = null, $search_string = null){
            $payload = [
                'filters' => [
                    'page' => $page,
                    'items_per_page' => $items_per_page,
                    'order_by' => 'rb.label',
                ]
            ];
            
            if (!is_null($type)){
                $payload['filters']['type'] = $type;
            }
            
            if (!is_null($search_string) && $search_string != ''){
                $payload['filters']['q'] = $search_string;
            }
            //print new html_pre(print_r($payload, true));
            $response = $this->_request("/bundles", $payload);
            if ($response['content']['status'] == "success"){
                return $response['content']['information'];
            }
            
            return false;
        }
        
        public function create_bundle($bundle_file_location){
            if (!$this->_has_permission('can_upload_bundles')){
                $this->error('Not authorised');
                return false;
            }
            
            if (!file_exists($bundle_file_location)){
                $this->error("Bundle not found");
                return false;
            }
            
            $response = $this->_request("/bundles/create", file_get_contents($bundle_file_location), "application/x-bundle");
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['results'];
            }
            
            return false;
        }
        
        public function read_bundle($bundle_name_or_guid){
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $response = $this->_request("/bundles/read", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['results'];
            }
            
            return false;
        }
        
        public function update_bundle($hash){
            // Move to the bundle "repository_api"
        }
        
        public function delete_bundle($bundle_name_or_guid){
            // Move to the bundle "repository_api"
        }
        
        public function read_bundle_version($bundle_name_or_guid, $version = 'latest'){
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            if ($version != 'latest'){
                $payload['repository_bundle_version'] = ['version' => $version];
            }
            
            $response = $this->_request("/bundles/versions", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['results'];
            }
            
            return false;
        }
        
        public function download_bundle_version($bundle_name_or_guid, $version = 'latest'){
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            if ($version != 'latest' && $version != ''){
                $payload['repository_bundle_version'] = ['version' => $version];
            }
            
            $response = $this->_request("/bundles/versions/download", $payload);
            if ($response['status'] == '200'){
                $key = "repository/bundles/" . $bundle_name_or_guid;
                $this->file_store->set($key, $response['content'], "application/x-bundle");
                return $key;
            }
            
            return false;
        }
        
        public function read_bundle_version_schema($bundle_name_or_guid, $version = 'latest'){
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            if ($version != 'latest'){
                $payload['repository_bundle_version'] = ['version' => $version];
            }
            
            $response = $this->_request("/bundles/versions/schema", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['data'];
            }
            
            return false;
        }
        
        public function read_bundle_version_depends_on($bundle_name_or_guid, $version = 'latest'){
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            if ($version != 'latest'){
                $payload['repository_bundle_version'] = ['version' => $version];
            }
            
            $response = $this->_request("/bundles/versions/depends-on", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['data'];
            }
            
            return false;
        }
        
        public function list_bundle_users($bundle_name_or_guid, $page = 1, $items_per_page = 50, $search_string = null){
            $payload = [
                'filters' => [
                    'page' => $page,
                    'items_per_page' => $items_per_page,
                    'order_by' => $order_by,
                ]
            ];
            
            if (!is_null($type)){
                $payload['filters']['type'] = $type;
            }
            
            if (!is_null($search_string)){
                $payload['filters']['q'] = $search_string;
            }
            
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $response = $this->_request("/bundles/versions/schema", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['results'];
            }
            
            return false;
        }
        
        public function read_bundle_user($bundle_name_or_guid, $username){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $payload['repository_bundle_user']['username'] = $username;
            
            $response = $this->_request("/bundles/users/read", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['repository_bundle_user'];
            }
            
            return false;
        }
        
        public function create_bundle_user($bundle_name_or_guid, $username){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $payload['repository_bundle_user']['username'] = $username;
            
            $response = $this->_request("/bundles/users/create", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['repository_bundle_user'];
            }
            
            return false;
        }
        
        public function update_bundle_user($bundle_name_or_guid, $username){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $payload['repository_bundle_user']['username'] = $username;
            
            $response = $this->_request("/bundles/users/update", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['repository_bundle_user'];
            }
            
            return false;
        }
        
        public function delete_bundle_user($bundle_name_or_guid, $username){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $payload['repository_bundle_user']['username'] = $username;
            
            $response = $this->_request("/bundles/users/delete", $payload);
            
            if ($response['content']['status'] == "success"){
                return true;
            }
            
            return false;
        }
        
        public function list_bundle_user_permissions($bundle_name_or_guid, $username, $page = 1, $items_per_page = 50, $search_string = null){
            $payload = [
                'filters' => [
                    'page' => $page,
                    'items_per_page' => $items_per_page,
                    'order_by' => $order_by,
                ]
            ];
            
            if (!is_null($search_string)){
                $payload['filters']['q'] = $search_string;
            }
            
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $payload['repository_bundle_user']['username'] = $username;
            
            $response = $this->_request("/bundles/users/permissions", $payload);
            
            if ($response['content']['status'] == "success"){
                return $response['content']['information']['results'];
            }
            
            return false;
        }
        
        public function create_bundle_user_permission($bundle_name_or_guid, $usename, $permission_name){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $payload['repository_bundle_user']['username'] = $username;
            $payload['repository_bundle_user_permission']['permission'] = $permission_name;
            
            $response = $this->_request("/bundles/users/permissions/create", $payload);
            
            if ($response['content']['status'] == "success"){
                return true;
            }
            
            return false;
        }
        
        public function delete_bundle_user_permission($bundle_name_or_guid, $username, $permission_name){
            if (is_null($this->session_token)){
                $this->error("Not logged in");
                return false;
            }
            
            $payload = ['repository_bundle' => []];
            
            if (preg_match("/^[a-zA-Z0-9]{8,8}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{4,4}-[a-zA-Z0-9]{12,12}$/", $bundle_name_or_guid)){
                $payload['repository_bundle']['guid'] = $bundle_name_or_guid;
            }else{
                $payload['repository_bundle']['name'] = $bundle_name_or_guid;
            }
            
            $payload['repository_bundle_user']['username'] = $username;
            $payload['repository_bundle_user_permission']['permission'] = $permission_name;
            
            $response = $this->_request("/bundles/users/permissions/delete", $payload);
            
            if ($response['content']['status'] == "success"){
                return true;
            }
            
            return false;
        }
    }
}
