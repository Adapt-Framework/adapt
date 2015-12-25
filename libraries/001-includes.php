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
 * The following classes are used during the
 * boot sequence before the auto_loader is available
 * and so must be included manually.
 */
require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/base.php");
//require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/selector.php");
//require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/aquery.php");
//require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/xml.php");
//require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/sanitizer.php");
//require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/storage_file_system.php");
//require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/cache.php");
//require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/bundle.php");
require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/bundles.php");
//require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/classes/bundle_adapt.php");

//require_once(FRAMEWORK_PATH . 'adapt/classes/base.php');
//require_once(FRAMEWORK_PATH . 'adapt/classes/selector.php');
//require_once(FRAMEWORK_PATH . 'adapt/classes/aquery.php');
//require_once(FRAMEWORK_PATH . 'adapt/classes/xml.php');
//require_once(FRAMEWORK_PATH . 'adapt/classes/bundle.php');
//require_once(FRAMEWORK_PATH . 'adapt/classes/bundles.php');


?>