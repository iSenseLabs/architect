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
        $this->model_extension = $this->model_extension_module;

        $this->load->language($this->arc['path_module']);
    }

    public function getModule($module_id)
    {
        return $this->prepareItem($this->db->query("SELECT * FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'")->row);
    }

    public function editModule($data)
    {
        $error = false;

        /**
         * Part 1: Save to database
         */

        if (!$data['module_id']) {
            $this->model_extension->addModule('architect', $this->queryForm('module', $data));
            $data['module_id'] = $this->db->getLastId();
            $this->db->query("INSERT INTO `" . DB_PREFIX . "architect` SET " . $this->queryForm('architect', $data) . ", `created` = NOW()");
        } else {
            $this->model_extension->editModule($data['module_id'], $this->queryForm('module', $data));
            $this->db->query("UPDATE `" . DB_PREFIX . "architect` SET " . $this->queryForm('architect', $data) . " WHERE `module_id` = '" . (int)$data['module_id'] . "'");
        }

        /**
         * Part 2: Create related file and db entries
         */

        $codetags = array(
            '{module_id}'        => $data['module_id'],
            '{identifier}'       => $data['identifier'],
            '{author}'           => $this->user->getUserName(),
            // ---
            '{controller_class}' => 'ControllerExtensionArchitect' . str_replace('_', '', $data['identifier']),
            '{model_class}'      => 'ModelExtensionArchitect' . str_replace('_', '', $data['identifier']),
            '{model_path}'       => 'extension/architect/' . $data['identifier'],
            '{model_call}'       => 'extension_architect_' . $data['identifier'],
            '{template_path}'    => 'extension/architect/' . $data['identifier'],
            // ---
            '{ocmod_name}'       => 'Architect #' . $data['module_id'] . ' - ' . $data['name'],
            '{ocmod_code}'       => $data['identifier'],
            '{event_class}'      => 'EventArchitect' . str_replace('_', '', $data['identifier']),
            '{event_path}'       => 'event/architect/' . $data['identifier']
        );

        $tags_search  = array_keys($codetags);
        $tags_replace = array_values($codetags);

        if ($controller = trim($data['controller'])) {
            $error = !$this->saveToFile(
                ARC_CATALOG . 'controller/extension/architect/' . $data['identifier'] . '.php',
                str_replace($tags_search, $tags_replace, $controller . "\n")
            );
        }

        if (!$error && $model = trim($data['model'])) {
            $error = !$this->saveToFile(
                ARC_CATALOG . 'model/extension/architect/' . $data['identifier'] . '.php',
                str_replace($tags_search, $tags_replace, $model . "\n")
            );
        }

        if (!$error && $template = trim($data['template'])) {
            $error = !$this->saveToFile(
                ARC_CATALOG . 'view/theme/default/template/extension/architect/' . $data['identifier'] . '.tpl',
                str_replace($tags_search, $tags_replace, $template . "\n")
            );
        }

        if (!$error && $modification = trim($data['modification'])) {
            $modification = html_entity_decode(str_replace($tags_search, $tags_replace, $modification . "\n"), ENT_COMPAT, 'UTF-8');
            $ocmod        = $this->getOcmodInfo($modification);
            $error        = $ocmod['error'];

            if (!$error) {
                $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = '" . $this->db->escape($ocmod['code']) . "'");
                $this->db->query(
                    "INSERT INTO `" . DB_PREFIX . "modification`
                    SET `name`        = '" . $this->db->escape($ocmod['name']) . "',
                        `code`        = '" . $this->db->escape($ocmod['code']) . "',
                        `author`      = '" . $this->db->escape($ocmod['author']) . "',
                        `version`     = '" . $this->db->escape($ocmod['version']) . "',
                        `link`        = '" . $this->db->escape($ocmod['link']) . "',
                        `xml`         = '" . $this->db->escape($modification) . "',
                        `status`      = '" . (int)$data['status'] . "',
                        `date_added`  = NOW()"
                );
            }
        }

        if (!$error && $event = trim($data['event'])) {
            $event  = html_entity_decode(str_replace($tags_search, $tags_replace, $event . "\n"), ENT_COMPAT, 'UTF-8');
            $events = $this->getEventAnnotation($event);

            if ($events) {
                $error = !$this->saveToFile(
                    ARC_CATALOG . 'controller/extension/architect/event/' . $data['identifier'] . '.php',
                    str_replace($tags_search, $tags_replace, $event . "\n")
                );

                if (!$error) {
                    $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($data['identifier']) . "'");

                    foreach ($events as $event) {
                        if ($event['trigger'] && $event['action']) {
                            $this->db->query(
                                "INSERT INTO `" . DB_PREFIX . "event`
                                SET `code`        = '" . $this->db->escape($data['identifier']) . "',
                                    `trigger`     = '" . $this->db->escape($event['trigger']) . "',
                                    `action`      = '" . $this->db->escape($event['action']) . "',
                                    `status`      = '" . (int)$data['status'] . "',
                                    `date_added`  = now()"
                            );
                        }
                    }
                }
            }
        }

        /**
         * Part 3: "Rollback" on error
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
            $this->model_extension->deleteModule($module_id);
        }

        $arc = $this->db->query("SELECT identifier FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'")->row;

        $this->db->query("DELETE FROM `" . DB_PREFIX . "architect` WHERE `module_id` = '" . (int)$module_id . "'");

        if (!empty($arc['identifier'])) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($arc['identifier']) . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = '" . $this->db->escape($arc['identifier']) . "'");

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
                `option`        = '" . $this->db->escape(json_encode(array())) . "',
                `meta`          = '" . $this->db->escape(json_encode($data['meta'])) . "',
                `status`        = '" . (int)$data['status'] . "',
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
            FROM `" . DB_PREFIX . "module` m
                LEFT JOIN `" . DB_PREFIX . "architect` a ON a.module_id = m.module_id
            WHERE m.code = 'architect'
            ORDER BY `architect_id` ASC
            LIMIT " . (int)$param['start'] . "," . (int)$param['limit']
        );

        foreach ($results->rows as $key => $value) {
            $items[$key] = $this->prepareItem($value);
        }

        return $items;
    }

    public function getTotalItems()
    {

    }

    public function prepareItem($itemValues)
    {
        $item    = $itemValues;
        $decodes = array('option', 'meta');

        foreach ($itemValues as $key => $value) {
            if (in_array($key, $decodes)) {
                $temp_val = json_decode($value, true);
                $item[$key] = is_array($temp_val) ? $temp_val : array();
            }
        }

        $item['url_edit'] = $this->url->link($this->arc['path_module'], $this->arc['url_token'] . '&module_id=' . $item['module_id'], true);

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
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` LIKE 'arc%'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` LIKE 'arc%'");

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
        if (!$content) {
            if (file_exists($target)) {
                unlink($target);
            }
            return true; // empty content is not an error
        }

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
    public function getOcmodInfo($xml)
    {
        $data = array(
            'error' => false,
        );

        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->loadXml($xml);

            $data['name']    = $dom->getElementsByTagName('name')->item(0)->nodeValue;
            $data['version'] = $dom->getElementsByTagName('version')->item(0)->nodeValue;
            $data['author']  = $dom->getElementsByTagName('author')->item(0)->nodeValue;
            $data['link']    = $dom->getElementsByTagName('link')->item(0)->nodeValue;
            $data['code']    = $dom->getElementsByTagName('code')->item(0)->nodeValue;
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
