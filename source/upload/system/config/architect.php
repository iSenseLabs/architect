<?php
defined('ARCHITECT') or define('ARCHITECT', '3.1.0');

$_['architect'] = array(
    'title'           => 'Architect',
    'version'         => ARCHITECT,

    // Internal
    'model'           => 'model_extension_module_architect',
    'path_module'     => 'extension/module/architect',

    // Environment
    'token_part'      => 'user_token',
    'token_url'       => 'user_token=%s',
    'url_extension'   => 'marketplace/extension',
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
            'oc_compatible' => true,
            'editor'    => array(
                'controller'       => 0,
                'model'            => 0,
                'template'         => 0,
                'modification'     => 0,
                'event'            => 0,
                'admin_controller' => 0,
                'option'           => 0,
            )
        ),
        'publish'       => date('Y-m-d'),
        'unpublish'     => '',

        // Editor
        'controller'    => '<?php
class {controller_class} extends Controller
{
    public function index($setting = array())
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
        'admin_controller' => '<?php
class {admin_controller_class} extends Controller
{

}',
    )
);
