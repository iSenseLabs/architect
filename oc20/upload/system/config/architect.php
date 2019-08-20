<?php
defined('ARCHITECT') or define('ARCHITECT', '2.0.0-alpha.1');
defined('ARC_CATALOG') or define('ARC_CATALOG', realpath(DIR_APPLICATION . './../') . '/catalog/');

$_['architect'] = array(
    'title'           => 'Architect',
    'version'         => ARCHITECT,

    // Internal
    'model'           => 'model_module_architect',
    'path_module'     => 'module/architect',

    // Environment
    'token_part'      => 'token',
    'token_url'       => 'token=%s',
    'url_extension'   => 'extension/module',
    'ext_type'        => '',

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
        $data = $this->language();

        // Your code here..

        return $this->load->view(\'{template_path}\', $data);
    }

    protected function language($data = array())
    {
        $_[\'heading\']  = \'Architect\';
        $_[\'greeting\'] = \'Hello World\';

        return array_merge($data, $_);
    }
}',
        'model'         => '<?php
class {model_class} extends Model
{
    public function get($param = array())
    {
        $data = array();

        // Usage in controller
        // $this->load->model(\'{model_path}\');
        // $data = $this->{model_call}->get(array());

        return $data;
    }
}',
        'template'      => '<div class="module architect arc-{module_id}">
    <div class="module-heading">
        <h3><?php echo $heading; ?></h3>
    </div>
    <div class="module-body">
        <?php echo $greeting; ?>
    </div>
</div>',
        'modification'  => '<modification>
    <name>{ocmod_name}</name>
    <version>1.0.1</version>
    <link>...</link>
    <author>{author}</author>
    <code>{ocmod_code}</code>

    <file path="admin/view/template/common/header.tpl">
        <operation error="skip">
            <search><![CDATA[<ul class="nav pull-right">]]></search>
            <add position="after"><![CDATA[<li><a href="#">Arc Test #{module_id}</a></li>]]></add>
        </operation>
    </file>
</modification>',
        'event'         => '<?php
class {event_class} extends Controller
{
    /**
     * @trigger  catalog/controller/commmon/footer/before
     * @action   {event_path}/foo
     */
    public function foo(&$route, &$data)
    {
        // Your code here..
    }

    /**
     * @trigger  catalog/view/default/template/common/footer/after
     * @action   {event_path}/bar
     */
    public function bar(&$route, &$data, &$output)
    {
        // Your code here..
    }
}',
    )
);

if (version_compare(VERSION, '2.2.0', '<')) {
    $_['architect']['setting']['controller'] = '<?php
class {controller_class} extends Controller
{
    public function index($param = array())
    {
        $data = $this->language();

        // Your code here..

        if (file_exists(DIR_TEMPLATE . $this->config->get(\'config_template\') . \'/template/{template_path}.tpl\')) {
            return $this->load->view($this->config->get(\'config_template\') . \'/template/{template_path}.tpl\', $data);
        } else {
            return $this->load->view(\'default/template/{template_path}.tpl\', $data);
        }
    }

    protected function language($data = array())
    {
        $_[\'heading\']  = \'Architect\';
        $_[\'greeting\'] = \'Hello World\';

        return array_merge($data, $_);
    }
}';
    $_['architect']['setting']['event'] = '';
}
