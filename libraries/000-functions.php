<?php

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