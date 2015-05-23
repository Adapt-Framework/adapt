<?php

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;


$adapt->store('adapt.handlers',
    array(
        'model_' => "namespace {{NAMESPACE}}{ class {{CLASS}} extends \\{{NAMESPACE}}\\model{ public function __construct(\$id = null){ parent::__construct('{{NAME}}', \$id); } } }",
        'xml_' => "namespace {{NAMESPACE}}{ class {{CLASS}} extends \\{{NAMESPACE}}\\xml{ public function __construct(\$data = null, \$attr = array()){ parent::__construct('{{NAME}}', \$data, \$attr); } } }",
        'html_' => "namespace {{NAMESPACE}}{ class {{CLASS}} extends \\{{NAMESPACE}}\\html{ public function __construct(\$data = null, \$attr = array()){ parent::__construct('{{NAME}}', \$data, \$attr); } } }",
        'model' => "namespace {{NAMESPACE}}{ class {{CLASS}} extends \\frameworks\\adapt\\model{} }",
        'xml' => "namespace {{NAMESPACE}}{ class {{CLASS}} extends \\frameworks\\adapt\\xml{} }",
        'html' => "namespace {{NAMESPACE}}{ class {{CLASS}} extends \\frameworks\\adapt\\html{} }"
    )
);

$adapt->store('adapt.handlers_without_namespace',
    array(
        'model_' => "class {{CLASS}} extends \\frameworks\\adapt\\model{ public function __construct(\$id = null){ parent::__construct('{{NAME}}', \$id); } }",
        'xml_' => "class {{CLASS}} extends \\frameworks\\adapt\\xml{ public function __construct(\$data = null, \$attr = array()){ parent::__construct('{{NAME}}', \$data, \$attr); } }",
        'html_' => "class {{CLASS}} extends \\frameworks\\adapt\\html{ public function __construct(\$data = null, \$attr = array()){ parent::__construct('{{NAME}}', \$data, \$attr); } }",
        'model' => "class {{CLASS}} extends \\frameworks\\adapt\\model{}",
        'xml' => "class {{CLASS}} extends \\frameworks\\adapt\\xml{}",
        'html' => "class {{CLASS}} extends \\frameworks\\adapt\\html{}"
    )
);

?>