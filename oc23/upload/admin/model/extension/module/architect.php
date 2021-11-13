<?php
class ModelExtensionModuleArchitect extends Model
{
    protected $arc = array();
    protected $model_extension;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->config->load('architect');
        $this->arc = $this->config->get('architect');

        $this->arc['url_token'] = sprintf($this->arc['token_url'], $this->session->data[$this->arc['token_part']]);

        $this->load->language($this->arc['path_module']);
    }

    public function getModule($module_id)
    {
        return $this->prepareItem($this->db->query("SELECT * FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'")->row);
    }

    public function getItems($param)
    {
        $items = array();

        $results = $this->db->query(
            "SELECT *
            FROM `" . DB_PREFIX . "architect` a
                LEFT JOIN `" . DB_PREFIX . "module` m ON m.module_id = a.module_id
            WHERE m.code = 'architect'
            ORDER BY a.architect_id ASC
            LIMIT " . (int)$param['start'] . "," . (int)$param['limit']
        );

        foreach ($results->rows as $key => $value) {
            $items[$key] = $this->prepareItem($value);
        }

        return $items;
    }

    public function getTotalItems()
    {
        return $this->db->query("SELECT COUNT(DISTINCT architect_id) AS total FROM `" . DB_PREFIX . "architect`")->row['total'];
    }

    /**
     * Standarize returned item result
     *
     * @param  array $item
     *
     * @return array
     */
    protected function prepareItem($item)
    {
        if ($item) {
            $item['option']     = json_decode($item['option'], true);
            $item['meta']       = json_decode($item['meta'], true);
            $item['url_edit']   = $this->url->link($this->arc['path_module'], $this->arc['url_token'] . '&module_id=' . $item['module_id'], true);

            $item['publish']    = $item['publish'] != '0000-00-00 00:00:00' ? $item['publish'] : date('Y-m-d H:i:s');
            $item['unpublish']  = $item['unpublish'] != '0000-00-00 00:00:00' ? $item['unpublish'] : '';

            $item['publish_format']   = $item['publish'] ? date('M d, Y', strtotime($item['publish'])) : '';
            $item['unpublish_format'] = $item['unpublish']   ? date('M d, Y', strtotime($item['unpublish'])) : '';

            $item = array_replace_recursive($this->arc['setting'], $item);
        }

        return $item;
    }

    public function editModule($data)
    {
        $data  = array_replace_recursive($this->arc['setting'], $data);
        $error = array(
            'status'  => false,
            'message' => array()
        );

        /**
         * Part 1: Preparation
         */

        $codetags = array(
            '{module_id}'        => $data['module_id'] ?: '{module_id}',
            '{identifier}'       => $data['identifier'],
            '{author}'           => $this->user->getUserName(),
            '{controller_class}' => 'ControllerExtensionArchitect' . $data['identifier'],
            '{model_class}'      => 'ModelExtensionArchitect' . $data['identifier'],
            '{model_path}'       => 'extension/architect/' . $data['identifier'],
            '{model_call}'       => 'model_extension_architect_' . $data['identifier'],
            '{template_path}'    => 'extension/architect/' . $data['identifier'],
            '{ocmod_name}'       => 'Architect #' . ($data['module_id'] ?: '{module_id}') . ' - ' . $data['name'],
            '{ocmod_code}'       => $data['identifier'],
            '{event_class}'      => 'ControllerExtensionArchitectEvent' . $data['identifier'],
            '{event_path}'       => 'extension/architect/event/' . $data['identifier'],
            '{admin_controller_class}' => 'ControllerExtensionArchitect' . $data['identifier']
        );

        $tags_search    = array_keys($codetags);
        $tags_replace   = array_values($codetags);

        // === Validate

        // Editor content
        $controller       = $data['meta']['editor']['controller']       ? str_replace($tags_search, $tags_replace, trim($data['controller']) . "\n") : '';
        $model            = $data['meta']['editor']['model']            ? str_replace($tags_search, $tags_replace, trim($data['model']) . "\n") : '';
        $template         = $data['meta']['editor']['template']         ? str_replace($tags_search, $tags_replace, trim($data['template']) . "\n") : '';
        $modification     = $data['meta']['editor']['modification']     ? str_replace($tags_search, $tags_replace, trim($data['modification']) . "\n") : '';
        $event            = $data['meta']['editor']['event']            ? str_replace($tags_search, $tags_replace, trim($data['event']) . "\n") : '';
        $admin_controller = $data['meta']['editor']['admin_controller'] ? str_replace($tags_search, $tags_replace, trim($data['admin_controller']) . "\n") : '';

        // Class name
        if ($controller && strpos($controller, $codetags['{controller_class}']) === false) {
            $error['message'][] = $this->language->get('error_controller_class');
        }
        if ($model && strpos($model, $codetags['{model_class}']) === false) {
            $error['message'][] = $this->language->get('error_model_class');
        }
        if ($event && strpos($event, $codetags['{event_class}']) === false) {
            $error['message'][] = $this->language->get('error_event_class');
        }
        if ($admin_controller && strpos($admin_controller, $codetags['{admin_controller_class}']) === false) {
            $error['message'][] = $this->language->get('error_admin_controller_class');
        }

        $this->load->model('extension/module');

        if (empty($error['message'])) {
            /**
             * Part 2: Save
             */

            if (!$data['module_id']) {
                $data['module_id'] = $this->model_extension_module->addModule('architect', $this->queryForm('module', $data));
                $this->db->query("INSERT INTO `" . DB_PREFIX . "architect` SET " . $this->queryForm('architect', $data) . ", `created` = NOW()");

                // Repeat to update module_id
                $codetags['{module_id}'] = $data['module_id'];

                $tags_search    = array_keys($codetags);
                $tags_replace   = array_values($codetags);

                $controller       = $controller       ? str_replace($tags_search, $tags_replace, trim($controller) . "\n") : '';
                $model            = $model            ? str_replace($tags_search, $tags_replace, trim($model) . "\n") : '';
                $template         = $template         ? str_replace($tags_search, $tags_replace, trim($template) . "\n") : '';
                $modification     = $modification     ? str_replace($tags_search, $tags_replace, trim($modification) . "\n") : '';
                $event            = $event            ? str_replace($tags_search, $tags_replace, trim($event) . "\n") : '';
                $admin_controller = $admin_controller ? str_replace($tags_search, $tags_replace, trim($admin_controller) . "\n") : '';
            } else {
                $this->model_extension_module->editModule($data['module_id'], $this->queryForm('module', $data));
                $this->db->query("UPDATE `" . DB_PREFIX . "architect` SET " . $this->queryForm('architect', $data) . " WHERE `module_id` = '" . (int)$data['module_id'] . "'");
            }

            // Controller
            $path_controller = DIR_CATALOG . 'controller/extension/architect/' . $data['identifier'] . '.php';

            if ($controller) {
                $result = $this->saveToFile($path_controller, $controller);

                if ($result['error']) {
                    $error['message'][] = $result['error'];
                }
            } elseif (file_exists($path_controller)) {
                unlink($path_controller);
            }

            // Model
            $path_model = DIR_CATALOG . 'model/extension/architect/' . $data['identifier'] . '.php';

            if ($model) {
                $result = $this->saveToFile($path_model, $model);

                if ($result['error']) {
                    $error['message'][] = $result['error'];
                }
            } elseif (file_exists($path_model)) {
                unlink($path_model);
            }

            // Template
            $path_template = DIR_CATALOG . 'view/theme/default/template/extension/architect/' . $data['identifier'] . '.tpl';

            if ($template) {
                $result = $this->saveToFile($path_template, $template);

                if ($result['error']) {
                    $error['message'][] = $result['error'];
                }
            } elseif (file_exists($path_template)) {
                unlink($path_template);
            }

            // Modification
            $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'architect_" . $this->db->escape($data['identifier']) . "'");

            if ($modification) {
                $modification = html_entity_decode($modification, ENT_COMPAT, 'UTF-8');
                $ocmod        = $this->getOcmodInfo($modification, $data['identifier']);

                if ($ocmod['error']) {
                    $error['message'][] = $ocmod['error'];
                } else {
                    $this->db->query(
                        "INSERT INTO `" . DB_PREFIX . "modification`
                        SET `name`        = '" . $this->db->escape($ocmod['name']) . "',
                            `code`        = 'architect_" . $this->db->escape($ocmod['code']) . "',
                            `author`      = '" . $this->db->escape($ocmod['author']) . "',
                            `version`     = '" . $this->db->escape($ocmod['version']) . "',
                            `link`        = '" . $this->db->escape($ocmod['link']) . "',
                            `xml`         = '" . $this->db->escape($modification) . "',
                            `status`      = '" . (int)$data['status'] . "',
                            `date_added`  = NOW()"
                    );
                }
            }

            // Event
            $path_event = DIR_CATALOG . 'controller/extension/architect/event/' . $data['identifier'] . '.php';
            $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = 'architect_" . $this->db->escape($data['identifier']) . "'");

            if ($event) {
                $events = $this->getEventAnnotation($event);

                if ($events) {
                    $result = $this->saveToFile($path_event, $event);

                    if ($result['error']) {
                        $error['message'][] = $result['error'];
                    } else {
                        foreach ($events as $event) {
                            if ($event['trigger'] && $event['action']) {
                                $this->db->query(
                                    "INSERT INTO `" . DB_PREFIX . "event`
                                    SET `code`        = 'architect_" . $this->db->escape($data['identifier']) . "',
                                        `trigger`     = '" . $this->db->escape($event['trigger']) . "',
                                        `action`      = '" . $this->db->escape($event['action']) . "',
                                        `status`      = '" . (int)$data['status'] . "',
                                        `date_added`  = now()"
                                );
                            }
                        }
                    }
                }
            } elseif (file_exists($path_event)) {
                unlink($path_event);
            }

            // Admin Controller
            $path_admin_controller = DIR_APPLICATION . 'controller/extension/architect/' . $data['identifier'] . '.php';

            if ($admin_controller) {
                $result = $this->saveToFile($path_admin_controller, $admin_controller);

                if ($result['error']) {
                    $error['message'][] = $result['error'];
                }
            } elseif (file_exists($path_admin_controller)) {
                unlink($path_admin_controller);
            }

            $this->load->controller(
                'extension/architect/' . $data['identifier'] . '/onSave',
                array(
                    'module_id'  => $data['module_id'],
                    'identifier' => $data['identifier'],
                )
            );
        }

        /**
         * Part 3: Handling error
         */

        $error['status'] = !empty($error['message']);

        if ($error['status']) {
            $data['status'] = 0;

            $this->model_extension_module->editModule($data['module_id'], $this->queryForm('module', $data));
            $this->deleteModuleContent($data['module_id'], $data['identifier']);
        }

        return array(
            'module_id' => (int)$data['module_id'],
            'error'     => $error
        );
    }

    /**
     * Standarize insert and update query
     *
     * @param  string $type     Database table: module or architect
     * @param  array  $data
     *
     * @return mixed
     */
    protected function queryForm($type, $data)
    {
        if ($type == 'module') {
            return array(
                'identifier' => $data['identifier'],
                'name'       => $data['name'],
                'note'       => $data['meta']['note'],
                'status'     => $data['status']
            );
        }

        if ($type == 'architect') {
            $data['meta']['author'] = $this->user->getUserName();

            return "
                `module_id`        = '" . (int)$data['module_id'] . "',
                `identifier`       = '" . $this->db->escape($data['identifier']) . "',
                `name`             = '" . $this->db->escape($data['name']) . "',
                `controller`       = '" . $this->db->escape($data['controller']) . "',
                `model`            = '" . $this->db->escape($data['model']) . "',
                `template`         = '" . $this->db->escape($data['template']) . "',
                `modification`     = '" . $this->db->escape($data['modification']) . "',
                `event`            = '" . $this->db->escape($data['event']) . "',
                `admin_controller` = '" . $this->db->escape($data['admin_controller']) . "',
                `option`           = '" . $this->db->escape(json_encode($data['option'])) . "',
                `meta`             = '" . $this->db->escape(json_encode($data['meta'])) . "',
                `status`           = '" . (int)$data['status'] . "',
                `publish`          = '" . $this->db->escape($data['publish']) . "',
                `unpublish`        = '" . $this->db->escape($data['unpublish']) . "',
                `updated`          = NOW()
            ";
        }

        return false;
    }

    /**
     * Delete architect database entry and files
     *
     * @param  int  $module_id
     *
     * @return bool             True on success
     */
    public function deleteModule($module_id)
    {
        $arc = $this->db->query("SELECT identifier FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'")->row;

        $this->db->query("DELETE FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'");

        if (!empty($arc['identifier'])) {
            $this->deleteModuleContent($module_id, $arc['identifier']);
        }

        return true;
    }

    protected function deleteModuleContent($module_id, $identifier)
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = 'architect_" . $this->db->escape($identifier) . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'architect_" . $this->db->escape($identifier) . "'");

        $files = array(
            DIR_CATALOG . 'model/extension/architect/' . $identifier . '.php',
            DIR_CATALOG . 'controller/extension/architect/' . $identifier . '.php',
            DIR_CATALOG . 'controller/extension/architect/event/' . $identifier . '.php',
            DIR_CATALOG . 'view/theme/default/template/extension/architect/' . $identifier . '.tpl',
        );

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->load->controller(
            'extension/architect/' . $identifier . '/onDelete',
            array(
                'module_id'  => $module_id,
                'identifier' => $identifier,
            )
        );

        $file = DIR_APPLICATION . 'controller/extension/architect/' . $identifier . '.php';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function getGist($file)
    {
        return $this->gistParser(DIR_CONFIG . 'architect/' . $file . '.arcgist.xml');
    }

    public function getGists()
    {
        $gists = array();
        $files = glob(DIR_CONFIG . 'architect/*.arcgist.xml');

        foreach ($files as $file) {
            $gists[] = $this->gistParser($file);
        }

        return array_filter($gists);
    }

    protected function gistParser($file)
    {
        $data = array();

        if (!file_exists($file)) {
            return $data;
        }

        $xml = file_get_contents($file);
        if (empty($xml)) {
            return $data;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($xml);

        $ocSupported = array_map('trim', explode(',', $this->getDOMTag($dom, 'opencart')));
        $isCompatible = true;
        if (!in_array(VERSION, $ocSupported)) {
            $isCompatible = false;
        }

        $codename = str_replace('.arcgist.xml', '', basename($file));
        $data     = array(
            'name'             => $this->getDOMTag($dom, 'name'),
            'codename'         => $codename,
            'version'          => $this->getDOMTag($dom, 'version'),
            'author'           => $this->getDOMTag($dom, 'author'),
            'link'             => $this->getDOMTag($dom, 'link'),
            'note'             => substr($this->getDOMTag($dom, 'note'), 0, 140),
            'image'            => $this->getDOMTag($dom, 'image', false),
            'description'      => substr(strip_tags($this->getDOMTag($dom, 'description', false), '<a><p><br>'), 0, 360),
            'opencart'         => $ocSupported,
            'oc_compatible'    => $isCompatible,
            'controller'       => $this->getDOMTag($dom, 'controller', false),
            'model'            => $this->getDOMTag($dom, 'model', false),
            'template'         => $this->getDOMTag($dom, 'template', false),
            'modification'     => $this->getDOMTag($dom, 'modification', false),
            'event'            => $this->getDOMTag($dom, 'event', false),
            'admin_controller' => $this->getDOMTag($dom, 'admin_controller', false),
            'option'           => json_decode($this->getDOMTag($dom, 'option', false), true),
        );

        return $data;
    }

    protected function getDOMTag($dom, $tag, $stripTags = true)
    {
        $value = '';

        if ($dom->getElementsByTagName($tag)->item(0)) {
            $value = $dom->getElementsByTagName($tag)->item(0)->textContent;
        }

        if ($value && $stripTags) {
            $value = strip_tags($value);
        }

        return $value;
    }


    // ================ Setup ================

    public function install($drop = false)
    {
        if ($drop) {
            $this->uninstall();
        }

        if (!$this->checkTable('architect')) {
            $this->db->query(
                "CREATE TABLE `" . DB_PREFIX . "architect` (
                    `architect_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `module_id` INT(11) UNSIGNED NOT NULL,
                    `identifier` VARCHAR(255) NOT NULL,
                    `name` VARCHAR(255) NOT NULL,
                    `controller` MEDIUMTEXT NOT NULL,
                    `model` MEDIUMTEXT NOT NULL,
                    `template` MEDIUMTEXT NOT NULL,
                    `modification` MEDIUMTEXT NOT NULL,
                    `event` MEDIUMTEXT NOT NULL,
                    `admin_controller` MEDIUMTEXT NOT NULL,
                    `option` TEXT NOT NULL COMMENT 'encoded',
                    `meta` TEXT NOT NULL COMMENT 'encoded',
                    `status` TINYINT(1) NOT NULL,
                    `publish` DATETIME NULL DEFAULT NULL,
                    `unpublish` DATETIME NULL DEFAULT NULL,
                    `created` DATETIME NULL DEFAULT NULL,
                    `updated` DATETIME NULL DEFAULT NULL,
                    PRIMARY KEY (`architect_id`),
                    INDEX `module_status` (`module_id`, `status`)
                )
                COLLATE='utf8mb4_general_ci' ENGINE=MyISAM"
            );
        }

        // Used to simplify check if Architect is installed
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting(
            'architect',
            array(
                'architect_install' => true,
                'architect_version' => $this->arc['version'],
            )
        );
    }

    public function uninstall()
    {
        $architects = $this->db->query("SELECT module_id FROM `" . DB_PREFIX . "architect`")->rows;

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('architect');

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "architect`");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` LIKE 'architect_arc%'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` LIKE 'architect_arc%'");

        $this->load->model('extension/module');
        foreach ($architects as $widget) {
            $this->model_extension_module->deleteModule($widget['module_id']);
        }
    }

    public function update()
    {
        // v2.1.0
        if (!$this->config->get('architect_version') && !$this->checkTableColumn('architect', 'admin_controller')) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "architect` ADD COLUMN `admin_controller` MEDIUMTEXT NOT NULL AFTER `event`");

            $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `code` = 'architect', `key` = 'architect_version', `value` = '" . $this->db->escape($this->arc['version']) . "'");
        }

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSettingValue('architect', 'architect_version', $this->arc['version']);
    }


    // ================ Helper ================

    /**
     * Check if database table available
     *
     * @param  string $table
     *
     * @return bool
     */
    protected function checkTable($table)
    {
        $tables = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $this->db->escape($table) . "';");

        if ($tables->num_rows) {
            return true;
        }

        return false;
    }

    /**
     * Check if column is available in database table
     *
     * @param  string $table
     * @param  string $column
     *
     * @return bool
     */
    protected function checkTableColumn($table, $column)
    {
        if ($this->checkTable($table)) {
            $results = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . $this->db->escape($table) . "` LIKE '" . $this->db->escape($column) . "';");

            if ($results->num_rows) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check path exist then save to file
     *
     * @param  string $path
     * @param  string $file
     * @param  string $content
     *
     * @return array
     */
    protected function saveToFile($target, $content)
    {
        $data = array(
            'error' => '',
        );

        if (!@file_put_contents($target, html_entity_decode($content, ENT_QUOTES, 'UTF-8'))) {
            if (file_exists($target)) {
                unlink($target);
            }

           $data['error'] = $this->language->get('error_save_file');
        };

        return $data;
    }

    /**
     * Get OcMod information from xml string
     *
     * @param  string $xml
     *
     * @return array
     */
    protected function getOcmodInfo($xml, $identifier)
    {
        $data = array(
            'code'  => $identifier,
            'error' => '',
        );

        $libXmlError = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');

        if (@$dom->loadXml($xml) !== false && $dom->getElementsByTagName('name')->length && $dom->getElementsByTagName('version')->length
            && $dom->getElementsByTagName('author')->length && $dom->getElementsByTagName('link')->length) {
            $data['name']    = $dom->getElementsByTagName('name')->item(0)->nodeValue;
            $data['version'] = $dom->getElementsByTagName('version')->item(0)->nodeValue;
            $data['author']  = $dom->getElementsByTagName('author')->item(0)->nodeValue;
            $data['link']    = $dom->getElementsByTagName('link')->item(0)->nodeValue;
        } else {
            $xmlError = libxml_get_last_error();
            $data['error'] = sprintf($this->language->get('error_ocmod_xml'), $xmlError->message, $xmlError->line);
        }
        libxml_use_internal_errors($libXmlError);

        return $data;
    }

    /**
     * Get annotation info for Events setting
     *
     * @param  string $string
     *
     * @return array
     */
    protected function getEventAnnotation($string)
    {
        $i = 0;
        $events = array();
        $lines  = explode("\n", $string);

        foreach ($lines as $line) {
            if (strpos($line, '/**') !== false) {
                $i++;
                $events[$i] = array(
                    'trigger' => '',
                    'action'  => ''
                );
            }

            if (($offset = strpos($line, '* @trigger')) !== false) {
                $events[$i]['trigger'] = trim(substr($line, $offset + 10));
            }
            if (($offset = strpos($line, '* @action')) !== false) {
                $events[$i]['action'] = trim(substr($line, $offset + 9));
            }
        }

        return $events;
    }
}
