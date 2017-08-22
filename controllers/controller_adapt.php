<?php

/**
 * Adapt Framework
 *
 * The MIT License (MIT)
 *   
 * Copyright (c) 2016 Matt Bruton
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
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * Adapt View Controller
     *
     * This controller is automatically mounted on the URL **\/_adapt**.  This controller
     * is used to make validators, formatters and unformatters available to the front
     * end.
     *
     * Calling **\/_adapt/sanitizers** will return a javascript page containing the
     * sanitizer functions.  This link is automatically added to the DOM so no futher
     * action is required.
     * 
     */
    class controller_adapt extends controller{
        
        public function __construct(){
            parent::__construct();
        }
        /**
         * Changes the content type to javascript and returns a page in javascript containing all the
         * validators, formatters and unformatters.
         *
         * @access public
         * @return string
         */
        public function view_sanitizers(){
            $this->dom->cache_time = 0;
            $this->content_type = 'text/javascript';
            
            $store = $this->store('adapt.sanitizer.data');
            
            $validators = array();
            
            if (isset($store['validators'])){
                foreach($store['validators'] as $name => $validator){
                    $validators[$name] = array(
                        'pattern' => $validator['pattern'],
                        'function' => $validator['js_function']
                    );
                }
            }
            
            $output = "var _adapt_validators = " . json_encode($validators) . ";\n";
            
            $formatters = array();
            
            if (isset($store['formatters'])){
                foreach($store['formatters'] as $name => $formatter){
                    $formatters[$name] = array(
                        'pattern' => $formatter['pattern'],
                        'function' => $formatter['js_function']
                    );
                }
            }
            
            $output .= "var _adapt_formatters = " . json_encode($formatters) . ";\n";
            
            $unformatters = array();
            
            if (isset($store['unformatters'])){
                foreach($store['unformatters'] as $name => $unformatter){
                    $unformatters[$name] = array(
                        'pattern' => $unformatter['pattern'],
                        'function' => $unformatter['js_function']
                    );
                }
            }
            
            $output .= "var _adapt_unformatters = " . json_encode($unformatters) . ";\n";
            
            if (!isset($this->request['actions'])){
                $this->cache->javascript($_SERVER['REQUEST_URI'], $output);
            }
            
            return $output;
        }
        
        /** @ignore */
        public function view_about(){
	    return;
            print 'foo';
        }
    }
}

?>
