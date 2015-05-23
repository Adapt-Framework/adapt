<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /*
     * Validators
     */
    $adapt->sanitize->add_validator('tinyint', "^([0-9]|1?[0-9][0-9]|2[0-4][0-9]|25[0-5])$");
    $adapt->sanitize->add_validator('smallint', "^((-([0-9]{1,4}|[012][0-9]{4,4}|3[01][0-9]{3,3}|32[0-6][0-9]{2,2}|327[0-5][0-9]|3276[0-8]))|[0-9]{1,4}|[012][0-9]{4,4}|3[01][0-9]{3,3}|32[0-6][0-9]{2,2}|327[0-5][0-9]|3276[0-7])$");
    $adapt->sanitize->add_validator('mediumint', "^((-([0-9]{1,6}|8[0-2][0-9]{5,5}|83[0-7][0-9]{4,4}|838[0-7][0-9]{3,3}|8388[0-5][0-9]{2,2}|838860[0-8]))|[0-9]{1,6}|8[0-2][0-9]{5,5}|83[0-7][0-9]{4,4}|838[0-7][0-9]{3,3}|8388[0-5][0-9]{2,2}|838860[0-7])$");
    $adapt->sanitize->add_validator('int', "^((-([0-9]{1,9}|20[0-9]{8,8}|21[0-3][0-9]{7,7}|214[0-6][0-9]{6,6}|2147[0-3][0-9]{5,5}|21474[0-7][0-9]{4,4}|214748[0-2][0-9]{3,3}|2147483[0-5][0-9]{2,2}|21474836[0-3][0-9]|214748364[0-8]))|[0-9]{1,9}|20[0-9]{8,8}|21[0-3][0-9]{7,7}|214[0-6][0-9]{6,6}|2147[0-3][0-9]{5,5}|21474[0-7][0-9]{4,4}|214748[0-2][0-9]{3,3}|2147483[0-5][0-9]{2,2}|21474836[0-3][0-9]|214748364[0-8])$");
    $adapt->sanitize->add_validator('bigint', "^(-)?[0-9]+$");
    $adapt->sanitize->add_validator('serial', "^[0-9]+$");
    $adapt->sanitize->add_validator('bit', "^[01]$");
    $adapt->sanitize->add_validator('boolean', "^[01]$");
    $adapt->sanitize->add_validator('float', "^((-)?[0-9]+|((-)?[0-9])*\.[0-9]+)$");
    $adapt->sanitize->add_validator('double', "^((-)?[0-9]+|((-)?[0-9])*\.[0-9]+)$");
    $adapt->sanitize->add_validator('decimal', "^((-)?[0-9]+|((-)?[0-9])*\.[0-9]+)$");
    $adapt->sanitize->add_validator('char', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('binary', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('varchar', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('varbinary', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('tinyblob', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('blob', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('mediumblob', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('longblob', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('tinytext', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('text', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('mediumtext', function($value){ return true; }, "function(value){ return true; }");
    $adapt->sanitize->add_validator('longtext', function($value){ return true; }, "function(value){ return true; }");
    
    /* Dates and times */
    $adapt->sanitize->add_validator('year', "^[0-9]{4,4}$");
    
    $adapt->sanitize->add_validator(
        'date',
        function($value){
            $adapt = $GLOBALS['adapt'];
            list($year, $month, $day) = explode("-", $value);
            return \checkdate($month, $day, $year);
        },
        "alert('Someone forgot to add the date validator in frameworks/adapt/config/002-sanitize.php');"
    );
    
    $adapt->sanitize->add_validator('time', "^([0-9]|1[0-9]|2[0-3])-([0-5][0-9])-([0-5][0-9])$");
    
    $adapt->sanitize->add_validator(
        'datetime',
        function($value){
            $adapt = $GLOBALS['adapt'];
            list($date, $time) = explode(" ", $value);
            return $adapt->sanitize->validate('date', $date) && $adapt->sanitize->validate('time', $time) ? true : false;
        },
        "alert('Someone forgot to add the time validator in frameworks/adapt/config/002-sanitize.php');"
    );
    
    $adapt->sanitize->add_validator(
        'timestamp',
        function($value){
            $adapt = $GLOBALS['adapt'];
            return $adapt->sanitize->validate('datetime', $value);
        },
        "alert('Someone forgot to add the time validator in frameworks/adapt/config/002-sanitize.php');"
    );
    
    
    /*
     * Formatters
     */
    $adapt->sanitize->add_format(
        'date',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to format the data according to the setting format.date */
            $date_format = $adapt->setting('format.date');
            if (is_string($date_format) && $date_format != 'date'){
                $value = $adapt->sanitize->format($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: date formatter')"
    );
    
    $adapt->sanitize->add_format(
        'time',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to format the data according to the setting format.time */
            $date_format = $adapt->setting('format.time');
            if (is_string($date_format) && $date_format != 'time'){
                $value = $adapt->sanitize->format($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: time formatter')"
    );
    
    $adapt->sanitize->add_format(
        'datetime',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to format the data according to the setting format.datetime */
            $date_format = $adapt->setting('format.datetime');
            
            if (is_string($date_format) && $date_format != 'datetime'){
                $value = $adapt->sanitize->format($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: datetime formatter')"
    );
    
    $adapt->sanitize->add_format(
        'timestamp',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to format the data according to the setting format.datetime */
            $date_format = $adapt->setting('format.datetime');
            if (is_string($date_format) && $date_format != 'datetime'){
                $value = $adapt->sanitize->format($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: timestamp formatter')"
    );
    
    
    /*
     * Unformatters
     */
    $adapt->sanitize->add_unformat(
        'date',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to unformat the data according to the setting format.date */
            $date_format = $adapt->setting('format.date');
            if (is_string($date_format) && $date_format != 'date'){
                $value = $adapt->sanitize->unformat($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: date unformatter')"
    );
    
    $adapt->sanitize->add_unformat(
        'time',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to unformat the data according to the setting format.time */
            $date_format = $adapt->setting('format.time');
            if (is_string($date_format) && $date_format != 'time'){
                $value = $adapt->sanitize->unformat($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: time unformatter')"
    );
    
    $adapt->sanitize->add_unformat(
        'datetime',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to unformat the data according to the setting format.datetime */
            $date_format = $adapt->setting('format.datetime');
            if (is_string($date_format) && $date_format != 'datetime'){
                $value = $adapt->sanitize->unformat($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: datetime unformatter')"
    );
    
    $adapt->sanitize->add_unformat(
        'timestamp',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to unformat the data according to the setting format.datetime */
            $date_format = $adapt->setting('format.datetime');
            if (is_string($date_format) && $date_format != 'datetime'){
                $value = $adapt->sanitize->unformat($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: timestamp unformatter')"
    );
    
    
    
    
}

?>