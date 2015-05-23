<?php

/* Prevent direct access */
defined('ADAPT_STARTED') or die;

$adapt->setting('xml.readable', true);
$adapt->setting('html.format', 'html5');

$adapt->setting('html.closed_tags', array(
    'img', 'link', 'meta', 'br', 'hr', 'area', 'base', 'col',
    'command', 'embed', 'input', 'link', 'meta', 'param', 'source'
));

$adapt->setting('mysql.default_engine', 'InnoDB');
$adapt->setting('mysql.default_character_set', 'utf8');
$adapt->setting('mysql.default_collation', 'utf8_general_ci');


?>