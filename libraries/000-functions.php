<?php

/*
 * The MIT License (MIT)
 *   
 * Copyright (c) 2015 Adapt Framework (www.adaptframework.com)
 * Authored by Matt Bruton (matt@adaptframework.com)
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
 */

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;

/*
 * Array functions
 */

function is_assoc($array){
    if (is_array($array)){
        return array_keys($array) !== range(0, count($array) - 1);
    }
    return false;
}

function array_remove($array, $index){
    if ((is_array($array)) && (!is_assoc($array))){
        $output = array();
        for($i = 0; $i < count($array); $i++){
            if ($i != $index) $output[] = $array[$i];
        }
        return $output;
    }
    
    return $array;
}



/*
 * JSON functions
 */
function is_json($data){
    json_decode($data);
    return (json_last_error() == JSON_ERROR_NONE);
}

/*
 * Text functions
 */
function mb_trim($string, $trim_chars = '\s'){
    return preg_replace('/^['.$trim_chars.']*(?U)(.*)['.$trim_chars.']*$/u', '\\1',$string);
}


function q($string){
    if (is_string($string)){
        $adapt = $GLOBALS['adapt'];
        if ($adapt && $adapt instanceof \frameworks\adapt\base){
            if (isset($adapt->data_source) && $adapt->data_source instanceof \frameworks\adapt\data_source_sql){
                return "\"" . $adapt->data_source->escape($string) . "\"";
            }else{
                return "\"" . addcslashes($string, "\"") . "\"";
            }
        }
    }
    
    return "\"\"";
}



?>