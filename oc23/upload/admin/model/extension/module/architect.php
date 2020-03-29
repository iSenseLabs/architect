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
            'message' => ''
        );

        /**
         * Part 1: Preparation
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

        $tags_search    = array_keys($codetags);
        $tags_replace   = array_values($codetags);

        // === Validate

        // Editor content
        $controller     = $data['meta']['editor']['controller']   ? str_replace($tags_search, $tags_replace, trim($data['controller']) . "\n") : '';
        $model          = $data['meta']['editor']['model']        ? str_replace($tags_search, $tags_replace, trim($data['model']) . "\n") : '';
        $template       = $data['meta']['editor']['template']     ? str_replace($tags_search, $tags_replace, trim($data['template']) . "\n") : '';
        $modification   = $data['meta']['editor']['modification'] ? str_replace($tags_search, $tags_replace, trim($data['modification']) . "\n") : '';
        $event          = $data['meta']['editor']['event']        ? str_replace($tags_search, $tags_replace, trim($data['event']) . "\n") : '';

        // Class name
        if ($controller && strpos($controller, $codetags['{controller_class}']) === false) {
            $error = array(
                'status'  => true,
                'message' => $this->language->get('error_controller_class')
            );
        }
        if (!$error['status'] && $model && strpos($model, $codetags['{model_class}']) === false) {
            $error = array(
                'status'  => true,
                'message' => $this->language->get('error_model_class')
            );
        }
        if (!$error['status'] && $event && strpos($event, $codetags['{event_class}']) === false) {
            $error = array(
                'status'  => true,
                'message' => $this->language->get('error_event_class')
            );
        }

        if (!$error['status']) {
            /**
             * Part 2: Save
             */

            if (!$data['module_id']) {
                $data['module_id'] = $this->model_extension_module->addModule('architect', $this->queryForm('module', $data));
                $this->db->query("INSERT INTO `" . DB_PREFIX . "architect` SET " . $this->queryForm('architect', $data) . ", `created` = NOW()");
            } else {
                $this->model_extension_module->editModule($data['module_id'], $this->queryForm('module', $data));
                $this->db->query("UPDATE `" . DB_PREFIX . "architect` SET " . $this->queryForm('architect', $data) . " WHERE `module_id` = '" . (int)$data['module_id'] . "'");
            }

            // Controller
            $path_controller = ARC_CATALOG . 'controller/extension/architect/' . $data['identifier'] . '.php';

            if (!$error['status'] && $controller) {
                $error = $this->saveToFile($path_controller, $controller);
            } elseif (file_exists($path_controller)) {
                unlink($path_controller);
            }

            // Model
            $path_model = ARC_CATALOG . 'model/extension/architect/' . $data['identifier'] . '.php';

            if (!$error['status'] && $model) {
                $error = $this->saveToFile($path_model, $model);
            } elseif (file_exists($path_model)) {
                unlink($path_model);
            }

            // Template
            $path_template = ARC_CATALOG . 'view/theme/default/template/extension/architect/' . $data['identifier'] . '.tpl';

            if (!$error['status'] && $template) {
                $error = $this->saveToFile($path_template, $template);
            } elseif (file_exists($path_template)) {
                unlink($path_template);
            }

            // Modification
            $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'architect_" . $this->db->escape($data['identifier']) . "'");

            if (!$error['status'] && $modification) {
                $modification = html_entity_decode($modification, ENT_COMPAT, 'UTF-8');
                $ocmod        = $this->getOcmodInfo($modification, $data['identifier']);
                $error        = $ocmod['error'];

                if (!$error['status']) {
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

            if (!$error['status'] && $event) {
                $events = $this->getEventAnnotation($event);

                if ($events) {
                    $error = $this->saveToFile($path_event, $event);

                    if (!$error['status']) {
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
             * Part 3: Handling error
             */

            if ($error['status']) {
                $data['status'] = 0;

                $this->model_extension_module->editModule($data['module_id'], $this->queryForm('module', $data));
                $this->deleteModuleContent($data['identifier']); // Safety first, remove sub-module content
            }
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
            $this->deleteModuleContent($arc['identifier']);
        }

        return true;
    }

    protected function deleteModuleContent($identifier)
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = 'architect_" . $this->db->escape($identifier) . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'architect_" . $this->db->escape($identifier) . "'");

        $files = array(
            ARC_CATALOG . 'model/extension/architect/' . $identifier . '.php',
            ARC_CATALOG . 'controller/extension/architect/' . $identifier . '.php',
            ARC_CATALOG . 'controller/extension/architect/event/' . $identifier . '.php',
            ARC_CATALOG . 'view/theme/default/template/extension/architect/' . $identifier . '.tpl',
        );

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function getGist($file)
    {
        $gist = array();
        $file = DIR_APPLICATION . '/controller/' . $this->arc['path_module'] . '/gist/' . $file . '.arcgist.xml';

        if (file_exists($file)) {
            $xml  = file_get_contents($file);
            $gist = $this->getGistInfo($xml);

            if (!$gist) {
                return;
            }
        }

        return $gist;
    }

    public function getGists($count = false)
    {
        $gists = array();
        $files = glob(DIR_APPLICATION . '/controller/' . $this->arc['path_module'] . '/gist/*.arcgist.xml');

        if ($count) {
            return count($files);
        }

        if ($files) {
            foreach ($files as $file) {
                $xml  = file_get_contents($file);
                $gist = $this->getGistInfo($xml);

                if (!empty($gist)) {
                    $gist['codename'] = $gist['file'] = str_replace('.arcgist.xml', '', basename($file));
                    $gists[] = $gist;
                }
            }
        }

        return $gists;
    }

    protected function getGistInfo($xml)
    {
        $gist = array();

        if (empty($xml)) {
            return $gist;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($xml);

        $ocCompatible = array_map('trim', explode(',', $this->getDOMTag($dom, 'opencart')));
        if (!in_array(VERSION, $ocCompatible)) {
            return $gist;
        }

        $gist = array(
            'name'         => $this->getDOMTag($dom, 'name'),
            'version'      => $this->getDOMTag($dom, 'version'),
            'author'       => $this->getDOMTag($dom, 'author'),
            'link'         => $this->getDOMTag($dom, 'link'),
            'description'  => substr(strip_tags($this->getDOMTag($dom, 'description')), 0, 200),
            'opencart'     => $ocCompatible,
            'controller'   => $this->getDOMTag($dom, 'controller'),
            'model'        => $this->getDOMTag($dom, 'model'),
            'template'     => $this->getDOMTag($dom, 'template'),
            'modification' => $this->getDOMTag($dom, 'modification'),
            'event'        => $this->getDOMTag($dom, 'event'),
        );

        return $gist;
    }

    protected function getDOMTag($dom, $tag)
    {
        if ($dom->getElementsByTagName($tag)->item(0)) {
            return $dom->getElementsByTagName($tag)->item(0)->textContent;
        }

        return '';
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
        $error = array(
            'status'  => false,
            'message' => ''
        );

        if (!@file_put_contents($target, html_entity_decode($content, ENT_QUOTES, 'UTF-8'))) {
            if (file_exists($target)) {
                unlink($target);
            }

            $error = array(
                'status'  => true,
                'message' => $this->language->get('error_save_file')
            );
        };

        return $error;
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
            'error' => array(
                'status'  => false,
                'message' => ''
            )
        );

        $dom = new DOMDocument('1.0', 'UTF-8');

        if (@$dom->loadXml($xml) !== false) {
            $data['name']    = $dom->getElementsByTagName('name')->item(0)->nodeValue;
            $data['version'] = $dom->getElementsByTagName('version')->item(0)->nodeValue;
            $data['author']  = $dom->getElementsByTagName('author')->item(0)->nodeValue;
            $data['link']    = $dom->getElementsByTagName('link')->item(0)->nodeValue;
        } else {
            $data['error'] = array(
                'status'  => true,
                'message' => $this->language->get('error_ocmod_xml')
            );
        }

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