<?php

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;

/*
 * The following classes are used during the
 * boot sequence before the auto_loader is available
 * and so must be included manually.
 */
require_once(FRAMEWORK_PATH . 'adapt/classes/base.php');
require_once(FRAMEWORK_PATH . 'adapt/classes/selector.php');
require_once(FRAMEWORK_PATH . 'adapt/classes/aquery.php');
require_once(FRAMEWORK_PATH . 'adapt/classes/xml.php');
require_once(FRAMEWORK_PATH . 'adapt/classes/bundle.php');
require_once(FRAMEWORK_PATH . 'adapt/classes/bundles.php');


?>