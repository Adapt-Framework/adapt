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
 */

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * Date object for manipulting dates and times.
     *
     * @property integer $year
     * The year
     * @property integer $month
     * The month
     * @property integer $day
     * The day of the month
     * @property integer $hour
     * The hour
     * @property integer $minute
     * The minutes
     * @property integer $second
     * The seconds
     */
    class date extends base{
        
        const SUNDAY = 0;
        const MONDAY = 1;
        const TUESDAY = 2;
        const WEDNESDAY = 3;
        const THURSDAY = 4;
        const FRIDAY = 5;
        const SATURDAY = 6;
        
        /** @ignore */
        protected $_year;
        
        /** @ignore */
        protected $_month;
        
        /** @ignore */
        protected $_day;
        
        /** @ignore */
        protected $_hour;
        
        /** @ignore */
        protected $_minute;
        
        /** @ignore */
        protected $_second;
        
        /**
         * Constructing
         *
         * @see self::set_date
         * @access public
         * @param string
         * A string representing the date.
         */
        public function __construct($date = null){
            parent::__construct();
            
            $this->set_date($date);
        }
        
        /*
         * Properties
         */
        /** @ignore */
        public function aget_year(){
            return $this->_year;
        }
        
        /** @ignore */
        public function aget_month(){
            return $this->_months;
        }
        
        /** @ignore */
        public function aget_day(){
            return $this->_day;
        }
        
        /** @ignore */
        public function aget_hour(){
            return $this->_hour;
        }
        
        /** @ignore */
        public function aget_minute(){
            return $this->_minute;
        }
        
        /** @ignore */
        public function aget_second(){
            return $this->_second;
        }
        
        /** @ignore */
        public function aset_year($year){
            $this->_year = $year;
        }
        
        /** @ignore */
        public function aset_month($month){
            $this->_month = $month;
        }
        
        /** @ignore */
        public function aset_day($day){
            $this->_day = $day;
        }
        
        /** @ignore */
        public function aset_hour($hour){
            $this->_hour = $hour;
        }
        
        /** @ignore */
        public function aset_minute($minute){
            $this->_minute = $minute;
        }
        
        /** @ignore */
        public function aset_second($second){
            $this->_second = $second;
        }
        
        /**
         * Set the date and or time
         *
         * When both params are missing this object is set to the current
         * date time.
         *
         * When the second param is missing the format of $date is
         * guessed.  For simple dates such as '2016-07-21' this function
         * will work just fine.  In the case of '09/08/2016' the month and day
         * could be either way around, m/d for US dates and d/m for UK dates, for
         * this reason the second param should be provided.
         *
         * @access public
         * @param string
         * The date to use.
         * @param string
         * The pattern of the date, eg, 'Y-m-d H:i:s'
         */
        public function set_date($date = null, $pattern = null){
            $this->_year = 0;
            $this->_month = 0;
            $this->_day = 0;
            $this->_hour = 0;
            $this->_minute = 0;
            $this->_second = 0;
            
            if (is_null($date)){
                $date = time();
            }
            
            if (is_string($date)){
                if (is_null($pattern)){
                    $pattern = self::make_pattern($date);
                }
                
                $date = self::convert_date($pattern, 'Y-m-d H:i:s', $date);
                list($date, $time) = explode(" ", $date);
                list($this->_year, $this->_month, $this->_day) = explode("-", $date);
                list($this->_hour, $this->_minute, $this->_second) = explode(":", $time);
                
            }elseif (is_int($date)){
                $this->_year = intval(\date("Y", $date));
                $this->_month = intval(\date("n", $date));
                $this->_day = intval(\date("j", $date));
                $this->_hour = intval(\date("G", $date));
                $this->_minute = intval(\date("i", $date));
                $this->_second = intval(\date("s", $date));
            }
        }
        
        /**
         * Returns the date/time of this object
         *
         * @access public
         * @param string
         * Optional. The pattern the returned date should conform to.
         * @return string
         * The date and or time.
         */
        public function date($pattern = null){
            $time = mktime($this->_hour, $this->_minute, $this->_second, $this->_month, $this->_day, $this->_year);
            
            if (isset($pattern)){
                return \date($pattern, $time);
            }
            
            return $time;
        }
        
        /**
         * Is the current date in the past?
         *
         * @access public
         * @param boolean
         * Should the time be taken into account?
         * @return boolean
         */
        public function is_past($include_time = false){
            $now = null;
            $date = null;
            
            if ($include_time){
                $now = mktime();
                $date = mktime($this->_hour, $this->_minute, $this->_second, $this->_month, $this->_day);
            }else{
                $now = mktime(0, 0, 0);
                $date = mktime(0, 0, 0, $this->_month, $this->_day);
            }
            
            return ($this->date('U') < \date('U', $now));
        }
        
        /**
         * Is the current date in the future?
         *
         * @access public
         * @param boolean
         * Should the time be taken into account?
         * @return boolean
         */
        public function is_future($include_time = false){
            $now = null;
            $date = null;
            
            if ($include_time){
                $now = mktime();
                $date = mktime($this->_hour, $this->_minute, $this->_second, $this->_month, $this->_day);
            }else{
                $now = mktime(0, 0, 0);
                $date = mktime(0, 0, 0, $this->_month, $this->_day);
            }
            
            return ($this->date('U') > \date('U', $now));
        }
        
        /**
         * Is the current date today?
         *
         * @access public
         * @return boolean
         */
        public function is_today(){
            return ($this->date('Ymd') == \date('Ymd'));
        }
        
        /**
         * Is the current day a working day?
         *
         * @access public
         * @return boolean
         */
        public function is_working_day(){
            return $this->is_weekday();
        }
        
        /**
         * Is the current date a weekend?
         *
         * @access public
         * @return boolean
         */
        public function is_weekend(){
            return in_array($this->date('w'), array(self::SUNDAY, self::SATURDAY));
        }
        
        /**
         * Is the current date a weekday?
         *
         * @access public
         * @return boolean
         */
        public function is_weekday(){
            return in_array($this->date('w'), array(self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY));
        }
        
        /**
         * Is the current year a leap year?
         *
         * @access public
         * @return boolean
         */
        public function is_leap_year(){
            return checkdate(2, 29, $this->_year);
        }
        
        /**
         * Returns an array containing the days in each month for
         * the current year.
         *
         * @access public
         * @return array
         */
        public function days_in_month(){
            $months = array(
                1 => 31,
                2 => $this->is_leap_year() ? 29 : 28,
                3 => 31,
                4 => 30,
                5 => 31,
                6 => 30,
                7 => 31,
                8 => 31,
                9 => 30,
                10 => 31,
                11 => 30,
                12 => 31
            );
            
            return $months[$this->_month];
        }
        
        /**
         * How many working days are in the current month?
         *
         * @access publi
         * @return boolean
         */
        public function working_days_in_month(){
            $c = new \adapt\date($this->date());
            $count = 0;
            for($i = 1; $i <= $c->days_in_month(); $i++){
                $c->day = $i;
                if ($c->is_working_day()) $count++;
            }
            
            return $count;
        }
        
        /**
         * Go to the first day of the month
         *
         * @access public
         * @param integer
         * When specified the date is moved to the first $day of the month.
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_first_day($day = null){
            $this->_day = 1;
            
            if (isset($day)){
                while ($this->date('w') != $day){
                    $this->goto_tomorrow();
                }
            }
        }
        
        /**
         * Go to the last day of the month
         *
         * @access public
         * @param integer
         * When specified the date is moved to the last $day of the month.
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_last_day($day = null){
            $this->_day = $this->days_in_month();
            
            if (isset($day)){
                while ($this->date('w') != $day){
                    $this->goto_yesterday();
                }
            }
        }
        
        /**
         * Go to the first working day of the month
         *
         * @access public
         * @param integer
         * When specified the date is moved to the first working $day of the month.
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_first_working_day($day = null){
            $this->goto_first_day($day);
            
            while(!$this->is_working_day()) $this->goto_tomorrow();
        }
        
        /**
         * Go to the last working day of the month
         *
         * @access public
         * @param integer
         * When specified the date is moved to the last working $day of the month.
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_last_working_day($day = null){
            $this->goto_last_day($day);
            
            while(!$this->is_working_day()) $this->goto_yesterday();
        }
        
        /**
         * Go to the next $day_of_week
         *
         * @access public
         * @param integer
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_next_day($day_of_week){
            $this->goto_tomorrow();
            if (($day_of_week >= self::SUNDAY) && ($day_of_week <= self::SATURDAY)){
                $current_day = $this->date('w');
                
                for ($i = 0; $i <= 6; $i++) {
                    if ($current_day == $day_of_week) {
                        break;
                    }
                    $this->goto_tomorrow();
                    $current_day = $this->date('w');
                }
            }
        }
        
        /**
         * Go to the second $day_of_week of the month
         *
         * @access public
         * @param integer
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_second_day($day_of_week){
            $this->goto_first_day($day_of_week);
            $this->goto_next_day($day_of_week);
        }
        
        /**
         * Go to the third $day_of_week of the month
         *
         * @access public
         * @param integer
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_third_day($day_of_week){
            $this->goto_second_day($day_of_week);
            $this->goto_next_day($day_of_week);
        }
        
        /**
         * Go to the second working $day_of_week of the month
         *
         * @access public
         * @param integer
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_second_working_day($day_of_week){
            $this->goto_second_day($day_of_week);
            while(!$this->is_working_day()){
                $this->goto_yesterday();
            }
        }
        
        /**
         * Go to the third working $day_of_week of the month
         *
         * @access public
         * @param integer
         * 0 = Sunday, 6 = Saturday
         */
        public function goto_third_working_day($day_of_week){
            $this->goto_third_day($day_of_week);
            while(!$this->is_working_day()){
                $this->goto_yesterday();
            }
        }
        
        /**
         * Move forward or backward the number of $days
         *
         * @access public
         * @param integer
         * When positive moves forward by the number of $days,
         * when negative moves backwords by the number of $days
         */
        public function goto_days($days){
            if ($days < 0){
                for($i = $days; $i < 0; $i++){
                    $this->goto_yesterday();
                }
            }else{
                for($i = 0; $i < $days; $i++){
                    $this->goto_tomorrow();
                }
            }
        }
        
        /**
         * Move forward or backward the number of working $days
         *
         * @access public
         * @param integer
         * When positive moves forward by the number of working $days,
         * when negative moves backwords by the number of working $days
         */
        public function goto_working_days($days){
            if ($days < 0){
                while ($days != 0){
                    $this->goto_yesterday();
                    if ($this->is_working_day()) $days++;
                }
            }else{
                while ($days != 0){
                    $this->goto_tomorrow();
                    if ($this->is_working_day()) $days--;
                }
            }
        }
        
        /**
         * Move forward or backward the number of $months
         *
         * @access public
         * @param integer
         * When positive moves forward by the number of $months,
         * when negative moves backwords by the number of $months
         */
        public function goto_months($months){
            if ($months < 0){
                for($i = $months; $i < 0; $i++){
                    $this->_month--;
                    if ($this->_month < 1){
                        $this->_month = 12;
                        $this->_year--;
                    }
                }
            }else{
                for($i = 0; $i < $months; $i++){
                    $this->_month++;
                    if ($this->_month > 12){
                        $this->_month = 1;
                        $this->_year++;
                    }
                }
            }
        }
        
        /**
         * Move forward or backward the number of $years
         *
         * @access public
         * @param integer
         * When positive moves forward by the number of $years,
         * when negative moves backwords by the number of $years
         */
        public function goto_years($years){
            if ($years < 0){
                for($i = $years; $i < 0; $i++){
                    $this->_year--;
                }
            }else{
                for($i = 0; $i < $years; $i++){
                    $this->_year++;
                }
            }
        }
        
        /**
         * Move forward one day
         *
         * @access public
         */
        public function goto_tomorrow(){
            $this->_day++;
            if ($this->_day > $this->days_in_month()){
                $this->_month++;
                $this->_day = 1;
                if ($this->_month > 12){
                    $this->_year++;
                    $this->_month = 1;
                }
            }
        }
        
        
        /**
         * Move backwards one day
         *
         * @access public
         */
        public function goto_yesterday(){
            $this->day--;
            if ($this->_day < 1){
                $this->_month--;
                if ($this->_month < 1){
                    $this->_year--;
                    $this->_month = 12;
                }
                $this->_day = $this->days_in_month();
            }
        }
        
        /**
         * Move forward one month
         *
         * @access public
         */
        public function goto_next_month(){
            $this->_month++;
            if ($this->_month > 12){
                $this->_year++;
                $this->_month = 1;
            }
        }
        
        
        /**
         * Move backwards one month
         *
         * @access public
         */
        public function goto_last_month(){
            $this->_month--;
            if ($this->_month < 1){
                $this->_year--;
                $this->_month = 12;
            }
        }
        
        /**
         * Move forward one year
         *
         * @access public
         */
        public function goto_next_year(){
            $this->_year++;
        }
        
        /**
         * Move backwards one year
         *
         * @access public
         */
        public function goto_last_year(){
            $this->_year--;
        }
        
        /**
         * Move forward one working day
         *
         * @access public
         */
        public function goto_next_working_day(){
            $this->goto_tomorrow();
            while (!$this->is_working_day()) $this->goto_tomorrow();
        }
        
        /**
         * Move forward or backward the number of $hours
         *
         * @access public
         * @param integer
         * When positive moves forward by the number of $hours,
         * when negative moves backwords by the number of $hours
         */
        public function goto_hours($hours){
            if ($hours < 0){
                if ($hours < -23){
                    $days = floor($hours / 23);
                    $hours = $hours % 23;
                    $this->goto_days($days);
                }
                for ($i = $hours; $i < 0; $i++){
                    $this->_hour--;
                    if ($this->_hour < 0){
                        //$this->goto_days(-1);
                        $this->_hour = 23;
                    }
                }
            }else{
                if ($hours > 23){
                    $days = floor($hours / 24);
                    $hours = $hours % 60;
                    $this->goto_days($days);
                }
                for($i = 0; $i < $hours; $i++){
                    $this->_hour++;
                    if ($this->_hour == 24){
                        $this->_hour = 0;
                        //$this->goto_days(1);
                    }
                }
            }
        }
        
        /**
         * Move forward or backward the number of $minutes
         *
         * @access public
         * @param integer
         * When positive moves forward by the number of $minutes,
         * when negative moves backwords by the number of $minutes
         */
        public function goto_minutes($minutes){
            if ($minutes < 0){
                if ($minutes < -59){
                    $hours = floor($minutes / 60);
                    $minutes = $minutes % 60;
                    $this->goto_hours($hours);
                }
                for ($i = $minutes; $i < 0; $i++){
                    $this->_minute--;
                    if ($this->_minute < 0){
                        //$this->goto_hours(-1);
                        $this->_minute = 59;
                    }
                }
            }else{
                if ($minutes > 59){
                    $hours = floor($minutes / 60);
                    $minutes = $minutes % 60;
                    $this->goto_hours($hours);
                }
                for($i = 0; $i < $minutes; $i++){
                    $this->_minute++;
                    if ($this->_minute == 60){
                        $this->_minute = 0;
                        //$this->goto_hours(1);
                    }
                }
            }
        }
        
        /**
         * Move forward or backward the number of $seconds
         *
         * @access public
         * @param integer
         * When positive moves forward by the number of $seconds,
         * when negative moves backwords by the number of $seconds
         */
        public function goto_seconds($seconds){
            if ($seconds < 0){
                if ($seconds < -59){
                    $minutes = floor($seconds / 60);
                    $seconds = $seconds % 60;
                    $this->goto_minutes($minutes);
                }
                for ($i = $seconds; $i < 0; $i++){
                    $this->_second--;
                    if ($this->_second < 0){
                        //$this->goto_minutes(-1);
                        $this->_second = 59;
                    }
                }
            }else{
                if ($seconds > 59){
                    $minutes = floor($seconds / 60);
                    $seconds = $seconds % 60;
                    $this->goto_minutes($minutes);
                }
                for($i = 0; $i < $seconds; $i++){
                    $this->_second++;
                    if ($this->_second == 60){
                        $this->_second = 0;
                        //$this->goto_minutes(1);
                    }
                }
            }
        }
        
        /**
         * Makes a pattern for a specified datetime.
         *
         * Providing the value **2016-07-21 17:11:17** would return
         * **Y-m-d H:i:s**
         * 
         * @access public
         * @param string
         * The date and or time to make a pattern from.
         * @return string
         * The generated pattern.
         */
        public static function make_pattern($string_datetime){ //TODO: Test
            $pattern = $string_datetime;
            $year_found = false;
            
            $pattern = preg_replace("/([0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2})/i", "Y-m-d", $pattern);
            
            $pattern = preg_replace("/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)/i", "l", $pattern);
            $pattern = preg_replace("/(Mon|Tue|Wed|Thu|Fri|Sat|Sun)/i", "D", $pattern);
            $pattern = preg_replace("/(st|nd|rd|th)/i", "S", $pattern);
            $pattern = preg_replace("/(January|February|April|May|June|July|August|September|October|November|December)/i", "F", $pattern);
            $pattern = preg_replace("/(Jan|Feb|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/i", "M", $pattern);
            
            if (preg_match("/([0-9]{4,4})/", $pattern)){
                $year_found = true;
                $pattern = preg_replace("/([0-9]{4,4})/i", "Y", $pattern);
            }
            
            
            
            $matches = array();
            if (preg_match("/(am|pm)/i", $pattern, $matches)){
                $replacement = "a";
                if (in_array($matches[0], array("AM", "PM"))) $replacement = "A";
                $pattern = preg_replace("/(am|pm)/i", $replacement, $pattern);
                
                $pattern = preg_replace("/([0-9]{2,2}:[0-9]{2,2}:[0-9]{2,2})/i", "h:i:s", $pattern);
                $pattern = preg_replace("/([0-9]{1,1}:[0-9]{2,2}:[0-9]{2,2})/i", "g:i:s", $pattern);
                $pattern = preg_replace("/([0-9]{2,2}:[0-9]{2,2})/i", "h:i", $pattern);
                $pattern = preg_replace("/([0-9]{1,1}:[0-9]{2,2})/i", "g:i", $pattern);
                
            }else{
                $pattern = preg_replace("/([0-9]{2,2}:[0-9]{2,2}:[0-9]{2,2})/i", "H:i:s", $pattern);
                $pattern = preg_replace("/([0-9]{1,1}:[0-9]{2,2}:[0-9]{2,2})/i", "G:i:s", $pattern);
                $pattern = preg_replace("/([0-9]{2,2}:[0-9]{2,2})/i", "H:i", $pattern);
                $pattern = preg_replace("/([0-9]{1,1}:[0-9]{2,2})/i", "G:i", $pattern);
            }
            
            $matches = array();
            if (preg_match("/([0-9]{1,2})(S{0,1}) (M|F)( (Y|[0-9]{2,2})){1,1}/", $pattern, $matches)){
                $day = "d";
                $year = "y";
                if ($matches[2] == "S") $day = "j";
                if ($matches[5] == "Y") $year = "Y";
                $pattern = preg_replace("/([0-9]{1,2})(S{0,1}) (M|F)( (Y|[0-9]{2,2})){1,1}/", "{$day}{$matches[2]} {$matches[3]} {$year}", $pattern);
                //print_r($matches);
            }elseif (preg_match("/([0-9]{1,2})(S{0,1}) (M|F)/", $pattern, $matches)){
                $day = "d";
                if ($matches[2] == "S") $day = "j";
                $pattern = preg_replace("/([0-9]{1,2})(S{0,1}) (M|F)/", "{$day}{$matches[2]} {$matches[3]}", $pattern);
                //print_r($matches);
            }elseif (preg_match("/(M|F) ([0-9]{1,2}),( (Y|[0-9]{2,2})){1,1}/", $pattern, $matches)){
                $day = "d";
                $year = "y";
                if ($matches[2] == "S") $day = "j";
                if ($matches[4] == "Y") $year = "Y";
                $pattern = preg_replace("/(M|F) ([0-9]{1,2})(S{0,1}),( (Y|[0-9]{2,2})){1,1}/", "{$matches[1]} {$day}, {$year}", $pattern);
                //print_r($matches);
            }elseif(preg_match("/([0-9]{1,2})([-.\/])([0-9]{1,2})([-.\/])(Y)/i", $pattern, $matches)){
                //print_r($matches);
                $pattern = preg_replace("/([0-9]{1,2})([-.\/])([0-9]{1,2})([-.\/])(Y)/i", "d{$matches[2]}m{$matches[4]}{$matches[5]}", $pattern);
            }elseif(preg_match("/(Y)([-.\/])([0-9]{1,2})([-.\/])([0-9]{1,2})/i", $pattern, $matches)){
                //print_r($matches);
                $pattern = preg_replace("/(Y)([-.\/])([0-9]{1,2})([-.\/])([0-9]{1,2})/i", "{$matches[1]}{$matches[2]}m{$matches[4]}d", $pattern);
            }elseif(preg_match("/([0-9]{1,2})([-.\/])([0-9]{1,2})([-.\/])([0-9]{2,2})/i", $pattern, $matches)){
                //print_r($matches);
                $pattern = preg_replace("/([0-9]{1,2})([-.\/])([0-9]{1,2})([-.\/])([0-9]{2,2})/i", "d{$matches[2]}m{$matches[4]}y", $pattern);
            }
            
            //$pattern = preg_replace("/()/i", "", $pattern);
            
            return $pattern;
        }
        
        /**
         * Converts a date from one format to another.
         *
         * @access public
         * @param string
         * The input pattern
         * @param string
         * The output pattern
         * @return string
         * The converted date and or time.
         */
        public static function convert_date($input_pattern, $output_pattern, $value){
            $chars = str_split($input_pattern);
            $date = array(
                'day_of_month' => null,
                'month' => null,
                'year' => null,
                'hour' => null,
                'minutes' => null,
                'seconds' => null
            );
            
            foreach($chars as  $char){
                //print "CHR: '{$char}'\n";
                //print "VALUE: '{$value}'\n";
                switch($char){
                case "d":
                    /* Day of month - two digits */
                    if (strlen($value) >= 2){
                        $val = substr($value, 0, 2);
                        $value = substr($value, 2);
                        
                        if (preg_match("/^[0-9]{2,2}$/", $val)){
                            $date['day_of_month'] = intval($val);
                        }
                    }
                    break;
                case "D":
                    /* Remove the day of the week */
                    $value = preg_replace("/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun)/i", "", $value);
                    break;
                case "j":
                    if (preg_match("/^[0-9]{1,2}/", $value)){
                        $val = substr($value, 0, 1);
                        $value = substr($value, 1);
                        
                        if ($val == "1" || $val == "2" || $val == "3"){
                            if (preg_match("/^[0-9]{1,1}/", $value)){
                                $val .= substr($value, 0, 1);
                                $value = substr($value, 1);
                                
                                $date['day_of_month'] = intval($val);
                            }
                        }
                    }
                    break;
                case "l":
                    /* Remove the day of the week */
                    $value = preg_replace("/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)/i", "", $value);
                    break;
                case "N":
                    /* Remove the day of the week number */
                    $value = preg_replace("/^([1-7])/i", "", $value);
                    break;
                case "S":
                    /* Remove the day of the month suffix */
                    $value = preg_replace("/^(st|nd|rd|th)/i", "", $value);
                    break;
                case "w":
                    /* Remove the day of the week number */
                    $value = preg_replace("/^([0-6])/i", "", $value);
                    break;
                case "z":
                    /* Remove the day of the year */
                    $value = preg_replace("/^([0-9]{1,3})/i", "", $value);
                    break;
                case "W":
                    /* Remove the week number */
                    $value = preg_replace("/^([0-9]{1,2})/i", "", $value);
                    break;
                case "F":
                    $months = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");
                    $pattern = "/^(" . implode("|", $months) . ")/i";
                    $match = array();
                    if (preg_match($pattern, $value, $match)){
                        $val = strtolower($match[0]);
                        $value = substr($value, strlen($val));
                        for($i = 0; $i < count($months); $i++){
                            if ($val == $months[$i]){
                                $date['month'] = $i + 1;
                                break;
                            }
                        }
                    }
                    break;
                case "m":
                    $months = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
                    $pattern = "/^(" . implode("|", $months) . ")/i";
                    $match = array();
                    
                    if (preg_match($pattern, $value, $match)){
                        $val = strtolower($match[0]);
                        $value = substr($value, strlen($val));
                        for($i = 0; $i < count($months); $i++){
                            if ($val == $months[$i]){
                                $date['month'] = $i + 1;
                                break;
                            }
                        }
                    }
                    break;
                case "M":
                    $months = array("jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec");
                    $pattern = "/^(" . implode("|", $months) . ")/i";
                    $match = array();
                    if (preg_match($pattern, $value, $match)){
                        $val = strtolower($match[0]);
                        $value = substr($value, strlen($val));
                        for($i = 0; $i < count($months); $i++){
                            if ($val == $months[$i]){
                                $date['month'] = $i + 1;
                                break;
                            }
                        }
                    }
                    break;
                case "n":
                    $months = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12");
                    $pattern = "/^(" . implode("|", $months) . ")/i";
                    $match = array();
                    
                    if (preg_match($pattern, $value, $match)){
                        $val = strtolower($match[0]);
                        $value = substr($value, strlen($val));
                        for($i = 0; $i < count($months); $i++){
                            if ($val == $months[$i]){
                                $date['month'] = $i + 1;
                                break;
                            }
                        }
                    }
                    break;
                case "t":
                    /* Remove the number of days in the month */
                    $value = preg_replace("/^(28|29|30|31)/i", "", $value);
                    break;
                case "L":
                    /* Remove the leap year flag */
                    $value = preg_replace("/^([0-1])/i", "", $value);
                    break;
                case "o":
                case "Y":
                    $match = array();
                    if (preg_match("/^[0-9]{4,4}/", $value, $match)){
                        $date['year'] = $match[0];
                    }
                    
                    $value = substr($value, 4);
                    break;
                case "y":
                    $match = array();
                    if (preg_match("/^[0-9]{2,2}/", $value, $match)){
                        $val = $match[0];
                        if (intval($val) >= 50){
                            $date['year'] = intval($val) + 1900;
                        }else{
                            $date['year'] = intval($val) + 2000;
                        }
                        $value = substr($value, 2);
                    }
                    break;
                case "a":
                case "A":
                    $match = array();
                    if (preg_match("/^(am|pm)/i", $value, $match)){
                        $offset = 0;
                        if (strtolower($match[0]) == "pm"){
                            $offset = 12;
                        }
                        
                        if (is_null($date['hour'])){
                            $date['hour'] = $offset;
                        }else{
                            $date['hour'] += $offset;
                            
                            if ($date['hour'] == 24) $date['hour'] = 0;
                        }
                    }
                    $value = substr($value, 2);
                    break;
                case "B":
                    /* Remove swash internet time */
                    $value = preg_replace("/^([0-9]{3,3})/i", "", $value);
                    break;
                case "g":
                case "h":
                    $match = array();
                    if (preg_match("/^([0-9]{1,2})/", $value, $match)){
                        $len = strlen($match[0]);
                        $val = intval($match[0]);
                        $value = substr($value, $len);
                        
                        if (!is_null($date['hour'])){
                            $val += $date['hour'];
                        }
                        
                        if ($val == 24) $val = 0;
                        
                        $date['hour'] = $val;
                    }
                    break;
                case "G":
                case "H":
                    $match = array();
                    if (preg_match("/^([0-9]{1,2})/", $value, $match)){
                        $len = strlen($match[0]);
                        $val = intval($match[0]);
                        $value = substr($value, $len);
                        
                        $date['hour'] = $val;
                    }
                    break;
                case "i":
                    $match = array();
                    if (preg_match("/^([0-9]{1,2})/", $value, $match)){
                        $len = strlen($match[0]);
                        $val = intval($match[0]);
                        $value = substr($value, $len);
                        
                        $date['minutes'] = $val;
                    }
                    break;
                case "s":
                    $match = array();
                    if (preg_match("/^([0-9]{1,2})/", $value, $match)){
                        $len = strlen($match[0]);
                        $val = intval($match[0]);
                        $value = substr($value, $len);
                        
                        $date['seconds'] = $val;
                    }
                    break;
                case "u":
                    /* Remove microseconds */
                    $value = preg_replace("/^([0-9]{6,6})/i", "", $value);
                    break;
                default:
                    /* Remove the char from value */
                    $value = substr($value, 1);
                }
            }
            
            date_default_timezone_set('UTC');
            return date($output_pattern, mktime($date['hour'], $date['minutes'], $date['seconds'], $date['month'], $date['day_of_month'], $date['year']));
        }
        
        /** @ignore */
        public static function convert_format($format_name){
            /*
             * TO BE REMOVED
             * The data types have been updated to carry
             * the pattern directly within them, so I guess
             * this function is obsolete
             */
            /*
             * Takes a format name such as uk_datetime
             * and returns the pattern such as
             * d/m/Y
             */
            
            $adapt = isset($GLOBALS['adapt']) && $GLOBALS['adapt'] instanceof base ? $GLOBALS['adapt'] : null;
            
            if (isset($adapt) && isset($adapt->data_source) && $adapt->data_source instanceof data_source){
                $data_type = $adapt->data_source->get_base_data_type($data_type);
                if (is_array($data_type) && isset($data_type['formatter'])){
                    return $data_type['formatter'];
                }
            }
            
            return ""; //Should we return at least something?
        }
        
    }
    
}

?>