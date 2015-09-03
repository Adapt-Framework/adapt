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
    $adapt->sanitize->add_validator('char', ".*");
    $adapt->sanitize->add_validator('binary', ".*");
    $adapt->sanitize->add_validator('varchar', ".*");
    $adapt->sanitize->add_validator('varbinary', ".*");
    $adapt->sanitize->add_validator('tinyblob', ".*");
    $adapt->sanitize->add_validator('blob', ".*");
    $adapt->sanitize->add_validator('mediumblob', ".*");
    $adapt->sanitize->add_validator('longblob', ".*");
    $adapt->sanitize->add_validator('tinytext', ".*");
    $adapt->sanitize->add_validator('text', ".*");
    $adapt->sanitize->add_validator('mediumtext', ".*");
    $adapt->sanitize->add_validator('longtext', ".*");
    
    /* Dates and times */
    $adapt->sanitize->add_validator('year', "^[0-9]{4,4}$");
    
    $adapt->sanitize->add_validator(
        'date',
        function($value){
            $adapt = $GLOBALS['adapt'];
            list($year, $month, $day) = explode("-", $value);
            return \checkdate($month, $day, $year);
        },
        "function(value){
            console.log('Validating (date): ' + value);
            value = value.replace(/[^0-9]/g, '');
            var day = 0;
            var month = 0;
            var year = 0;
            var valid = false;
            
            if (/^[0-9]{8,8}\$/.test(value)){
                day = parseInt(value.substring(6,8), 10);
                month = parseInt(value.substring(4,6), 10);
                year = parseInt(value.substring(0,4), 10);
            }else{
                if (/^[0-9]{6,6}\$/.test(value)){
                    day = parseInt(value.substring(4,6), 10);
                    month = parseInt(value.substring(2,4), 10);
                    year = parseInt(value.substring(0,2), 10);
                    if (year < 50){
                        year = year + 2000;
                    }else{
                        year = year + 1900;
                    }
                }else{
                    valid = false;
                }
            }
                
            var days = Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
            if (new Date(year,1,29).getDate() == 29){
                days[1] = 29;
            }
            
            if ((month >= 1) && (month <= 12)){
                if ((day >= 1) && (day <= days[month-1])){
                    valid = true;
                }
            }
            
            return valid;
        }
        "
    );
    
    $adapt->sanitize->add_validator('time', "^([0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$");
    
    $adapt->sanitize->add_validator(
        'datetime',
        function($value){
            $adapt = $GLOBALS['adapt'];
            list($date, $time) = explode(" ", $value);
            return $adapt->sanitize->validate('date', $date) && $adapt->sanitize->validate('time', $time) ? true : false;
        },
        "function(value){
            var parts = value.split(' ');
            return adapt.sanitize.validate('date', parts[0]) && adapt.sanitize.validate('time', parts[1]);
        }"
    );
    
    $adapt->sanitize->add_validator(
        'timestamp',
        function($value){
            $adapt = $GLOBALS['adapt'];
            return $adapt->sanitize->validate('datetime', $value);
        },
        "function(value){
            return adapt.sanitize.validate('datetime', value);
        }"
    );
    
    
    /*
     * Formatters
     */
    //$adapt->sanitize->add_format(
    //    'date',
    //    function($value){
    //        $adapt = $GLOBALS['adapt'];
    //        
    //        /* We are going to format the data according to the setting locales.default_date_format */
    //        $date_format = $adapt->setting('adapt.default_date_format');
    //        if (is_string($date_format) && $date_format != 'Y-m-d'){
    //            $value = $adapt->sanitize->format($date_format, $value);
    //        }
    //        
    //        return $value;
    //    },
    //    "function(value){
    //        value = adapt.sanitize.unformat('date', value);
    //        console.log('IN: date.format with value: ' + value);
    //        if (value.length == 8){
    //            if (adapt.setting('adapt.default_date_format') != 'Y-m-d'){
    //                value = adapt.date.convert_date('Ymd', adapt.setting('adapt.default_date_format'), value);
    //            }
    //        }
    //        
    //        return value;
    //    }"
    //);
    
    $adapt->sanitize->add_format(
        'time',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to format the data according to the setting format.time */
            $date_format = $adapt->setting('adapt.default_time_format');
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
            $date_format = $adapt->setting('adapt.default_datetime_format');
            
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
            $date_format = $adapt->setting('adapt.default_datetime_format');
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
    //$adapt->sanitize->add_unformat(
    //    'date',
    //    function($value){
    //        //return preg_replace("/[^0-9]/", '', $value);
    //        $adapt = $GLOBALS['adapt'];
    //        
    //        /* We are going to unformat the data according to the setting format.date */
    //        $date_format = $adapt->setting('adapt.default_date_format');
    //        if (is_string($date_format) && $date_format != 'date'){
    //            $value = $adapt->sanitize->unformat($date_format, $value);
    //        }
    //        
    //        return $value;
    //    }, "function(value){
    //        value = value.replace(/[^0-9]/g, '');
    //        if (adapt.setting('adapt.default_date_format') != 'Y-m-d'){
    //            console.log(value);
    //            var pattern = adapt.setting('adapt.default_date_format');
    //            pattern = pattern.replace(/[^dDjlNSwzNFmMntLoYyaABghGHisu]/g, '');
    //            value = adapt.date.convert_date(pattern, 'Ymd', value);
    //            console.log(value);
    //        }
    //        return value;
    //    }"
    //);
    
    $adapt->sanitize->add_unformat(
        'time',
        function($value){
            $adapt = $GLOBALS['adapt'];
            
            /* We are going to unformat the data according to the setting format.time */
            $date_format = $adapt->setting('adapt.default_time_format');
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
            $date_format = $adapt->setting('adapt.default_datetime_format');
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
            $date_format = $adapt->setting('adapt.default_datetime_format');
            if (is_string($date_format) && $date_format != 'datetime'){
                $value = $adapt->sanitize->unformat($date_format, $value);
            }
            
            return $value;
        },
        "alert('TODO: timestamp unformatter')"
    );
    
    
    
    
}

?>