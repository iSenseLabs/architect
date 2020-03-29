<?php
defined('ARCHITECT') or define('ARCHITECT', '2.0.0');
defined('ARC_CATALOG') or define('ARC_CATALOG', realpath(DIR_APPLICATION . './../') . '/catalog/');

$_['architect'] = array(
    'title'           => 'Architect',
    'version'         => ARCHITECT,

    // Internal
    'model'           => 'model_extension_module_architect',
    'path_module'     => 'extension/module/architect',

    // Environment
    'token_part'      => 'token',
    'token_url'       => 'token=%s',
    'url_extension'   => 'extension/extension',
    'ext_type'        => '&type=module',

    // Default Setting
    'setting'         => array(
        'module_id'     => 0,
        'identifier'    => uniqid('arc'),
        'name'          => '',
        'status'        => 0,
        'option'        => array(
            'customer_group'     => 0,
            'customer_group_ids' => array()
        ),
        'meta'          => array(
            'author'    => '',
            'note'      => '',
            'gist'      => '',
            'editor'    => array(
                'controller'   => 0,
                'model'        => 0,
                'template'     => 0,
                'modification' => 0,
                'event'        => 0
            )
        ),
        'publish'       => date('Y-m-d'),
        'unpublish'     => '',

        // Editor
        'controller'    => '<?php
class {controller_class} extends Controller
{
    public function index($param = array())
    {
        // Your code here..
    }
}',
        'model'         => '<?php
class {model_class} extends Model
{

}',
        'template'      => '<div class="module architect arc-{module_id}">

</div>',
        'modification'  => '',
        'event'         => '<?php
class {event_class} extends Controller
{

}',
    )
);
