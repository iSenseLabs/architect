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

        $this->load->model('extension/module');
        $this->load->language($this->arc['path_module']);
    }

    public function getModule($module_id)
    {
        return $this->prepareItem($this->db->query("SELECT * FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'")->row);
    }

    public function editModule($data)
    {
        $error = false;
        $data  = array_replace_recursive($this->arc['setting'], $data);

        /**
         * Part 1: Save to database
         */

        if (!$data['module_id']) {
            $this->model_extension_module->addModule('architect', $this->queryForm('module', $data));
            $data['module_id'] = $this->db->getLastId();
            $this->db->query("INSERT INTO `" . DB_PREFIX . "architect` SET " . $this->queryForm('architect', $data) . ", `created` = NOW()");
        } else {
            $this->model_extension_module->editModule($data['module_id'], $this->queryForm('module', $data));
            $this->db->query("UPDATE `" . DB_PREFIX . "architect` SET " . $this->queryForm('architect', $data) . " WHERE `module_id` = '" . (int)$data['module_id'] . "'");
        }

        /**
         * Part 2: Create related file and db entries
         */

        $codetags = array(
            '{module_id}'        => $data['module_id'],
            '{identifier}'       => $data['identifier'],
            '{author}'           => $this->user->getUserName(),
            '{controller_class}' => 'ControllerExtensionArchitect' . $data['identifier'],
            '{model_class}'      => 'ModelExtensionArchitect' . $data['identifier'],
            '{model_path}'       => 'extension/architect/' . $data['identifier'],
            '{model_call}'       => 'model_extension_architect_' . $data['identifier'],
            '{template_path}'    => 'extension/architect/' . $data['identifier'],
            '{ocmod_name}'       => 'Architect #' . $data['module_id'] . ' - ' . $data['name'],
            '{ocmod_code}'       => $data['identifier'],
            '{event_class}'      => 'ControllerExtensionArchitectEvent' . $data['identifier'],
            '{event_path}'       => 'extension/architect/event/' . $data['identifier']
        );

        $tags_search  = array_keys($codetags);
        $tags_replace = array_values($codetags);

        // Validate editor state

        if (!$data['meta']['editor']['controller']) {
            $data['controller'] = '';
        }
        if (!$data['meta']['editor']['model']) {
            $data['model'] = '';
        }
        if (!$data['meta']['editor']['template']) {
            $data['template'] = '';
        }
        if (!$data['meta']['editor']['modification']) {
            $data['modification'] = '';
        }
        if (!$data['meta']['editor']['event']) {
            $data['event'] = '';
        }

        // === Save

        // Controller
        $path_controller = ARC_CATALOG . 'controller/extension/architect/' . $data['identifier'] . '.php';

        if ($controller = trim($data['controller'])) {
            $error = !$this->saveToFile(
                $path_controller,
                str_replace($tags_search, $tags_replace, $controller . "\n")
            );
        } elseif (file_exists($path_controller)) {
            unlink($path_controller);
        }

        // Model
        $path_model = ARC_CATALOG . 'model/extension/architect/' . $data['identifier'] . '.php';

        if (!$error && $model = trim($data['model'])) {
            $error = !$this->saveToFile(
                $path_model,
                str_replace($tags_search, $tags_replace, $model . "\n")
            );
        } elseif (file_exists($path_model)) {
            unlink($path_model);
        }

        // Template
        $path_template = ARC_CATALOG . 'view/theme/default/template/extension/architect/' . $data['identifier'] . '.tpl';

        if (!$error && $template = trim($data['template'])) {
            $error = !$this->saveToFile(
                $path_template,
                str_replace($tags_search, $tags_replace, $template . "\n")
            );
        } elseif (file_exists($path_template)) {
            unlink($path_template);
        }

        // Modification
        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'architect_" . $this->db->escape($data['identifier']) . "'");

        if (!$error && $modification = trim($data['modification'])) {
            $modification = html_entity_decode(str_replace($tags_search, $tags_replace, $modification . "\n"), ENT_COMPAT, 'UTF-8');
            $ocmod        = $this->getOcmodInfo($modification, $data['identifier']);

            if (!$ocmod['error']) {
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
        $path_event = ARC_CATALOG . 'controller/extension/architect/event/' . $data['identifier'] . '.php';
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = 'architect_" . $this->db->escape($data['identifier']) . "'");

        if (!$error && $event = trim($data['event'])) {
            $event  = html_entity_decode(str_replace($tags_search, $tags_replace, $event . "\n"), ENT_COMPAT, 'UTF-8');
            $events = $this->getEventAnnotation($event);

            if ($events) {
                $error = !$this->saveToFile(
                    $path_event,
                    str_replace($tags_search, $tags_replace, $event . "\n")
                );

                if (!$error) {
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

        /**
         * Part 3: Remove on error
         */

        if ($error) {
            $this->deleteModule($data['module_id']);

            $data['module_id'] = 0;
            $error = $this->language->get('error_save_file');
        }

        return array(
            'module_id' => (int)$data['module_id'],
            'error'     => $error
        );
    }

    /**
     * Delete architect database entry and files
     *
     * @param  int  $module_id
     * @param  bool $chain      Delete module before architect sub-module
     *
     * @return bool             True on success
     */
    public function deleteModule($module_id, $chain = true)
    {
        if ($chain) {
            $this->model_extension_module->deleteModule($module_id);
        }

        $arc = $this->db->query("SELECT identifier FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'")->row;

        $this->db->query("DELETE FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'");

        if (!empty($arc['identifier'])) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = 'architect_" . $this->db->escape($arc['identifier']) . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'architect_" . $this->db->escape($arc['identifier']) . "'");

            $files = array(
                ARC_CATALOG . 'model/extension/architect/' . $arc['identifier'] . '.php',
                ARC_CATALOG . 'controller/extension/architect/' . $arc['identifier'] . '.php',
                ARC_CATALOG . 'controller/extension/architect/event/' . $arc['identifier'] . '.php',
                ARC_CATALOG . 'view/theme/default/template/extension/architect/' . $arc['identifier'] . '.tpl',
            );

            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        return true;
    }

    /**
     * Standarize insert and update query
     *
     * @param  string $type     Database table: module or architect
     * @param  array  $data
     *
     * @return mixed
     */
    public function queryForm($type, $data)
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
                `module_id`     = '" . (int)$data['module_id'] . "',
                `identifier`    = '" . $this->db->escape($data['identifier']) . "',
                `name`          = '" . $this->db->escape($data['name']) . "',
                `controller`    = '" . $this->db->escape($data['controller']) . "',
                `model`         = '" . $this->db->escape($data['model']) . "',
                `template`      = '" . $this->db->escape($data['template']) . "',
                `modification`  = '" . $this->db->escape($data['modification']) . "',
                `event`         = '" . $this->db->escape($data['event']) . "',
                `option`        = '" . $this->db->escape(json_encode($data['option'])) . "',
                `meta`          = '" . $this->db->escape(json_encode($data['meta'])) . "',
                `status`        = '" . (int)$data['status'] . "',
                `publish`       = '" . $this->db->escape($data['publish']) . "',
                `unpublish`     = '" . $this->db->escape($data['unpublish']) . "',
                `updated`       = NOW()
            ";
        }

        return false;
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

    public function prepareItem($item)
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
                    `event` TEXT NOT NULL COMMENT 'encoded',
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
                'architect_install' => true
            )
        );
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->deleteSetting('architect');

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "architect`");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` LIKE 'architect_arc%'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` LIKE 'architect_arc%'");

        $paths = array(
            ARC_CATALOG . 'model/extension/architect/*.php',
            ARC_CATALOG . 'controller/extension/architect/*.php',
            ARC_CATALOG . 'controller/extension/architect/event/*.php',
            ARC_CATALOG . 'view/theme/default/template/extension/architect/*.tpl',
        );

        foreach ($paths as $path) {
            $files = glob($path);

            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }


    // ================ Helper ================

    /**
     * Check if database table available
     *
     * @param  string $table
     *
     * @return bool
     */
    public function checkTable($table)
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
    public function checkTableColumn($table, $column)
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
     * @return bool            True on success, false otherwise
     */
    public function saveToFile($target, $content)
    {
        if (!@file_put_contents($target, html_entity_decode($content, ENT_QUOTES, 'UTF-8'))) {
            if (file_exists($target)) {
                unlink($target);
            }
            return false;
        };

        return true;
    }

    /**
     * Get OcMod information from xml string
     *
     * @param  string $xml
     *
     * @return array
     */
    public function getOcmodInfo($xml, $identifier)
    {
        $data = array(
            'code'  => $identifier,
            'error' => false,
        );

        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->loadXml($xml);

            $data['name']    = $dom->getElementsByTagName('name')->item(0)->nodeValue;
            $data['version'] = $dom->getElementsByTagName('version')->item(0)->nodeValue;
            $data['author']  = $dom->getElementsByTagName('author')->item(0)->nodeValue;
            $data['link']    = $dom->getElementsByTagName('link')->item(0)->nodeValue;
        } catch (\Exception $exception) {
            $data['error'] = true;
        }

        return $data;
    }

    public function getEventAnnotation($string)
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
