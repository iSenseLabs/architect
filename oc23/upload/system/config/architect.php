<?php
/**
 * Architect - OpenCart extension prototyping tool
 * Copyright (C) 2019 Mudzakkir

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

defined('ARCHITECT') or define('ARCHITECT', '2.0.0-alpha.1');
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
    public function get($setting = array())
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
            <add position="after"><![CDATA[<li><a href="#">Arc Test</a></li>]]></add>
        </operation>
    </file>
</modification>',
        'event'         => '',
    )
);
