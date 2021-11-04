<?php
class ModelModuleArchitect extends Model
{
    protected $arc = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->config->load('architect');
        $this->arc = $this->config->get('architect');
    }

    public function getWidget($identifier)
    {
        $data   = array();
        $result = $this->db->query(
            "SELECT *
            FROM `" . DB_PREFIX . "architect`
            WHERE `identifier` = '" . $this->db->escape($identifier) . "'
                AND status = 1
                AND CURDATE() >= publish
                AND (unpublish >= CURDATE() or unpublish = 0)"
        )->row;

        if ($result) {
            $data = $this->prepareItem($result);

            if ($data['option']['customer_group'] && !in_array((int)$this->customer->getGroupId(), $data['option']['customer_group_ids'])) {
                $data = array();
            }
        }

        return $data;
    }

    public function prepareItem($item)
    {
        if ($item) {
            $item['option']    = json_decode($item['option'], true);
            $item['meta']      = json_decode($item['meta'], true);

            $item['publish']   = $item['publish'] != '0000-00-00 00:00:00' ? $item['publish'] : date('Y-m-d H:i:s');
            $item['unpublish'] = $item['unpublish'] != '0000-00-00 00:00:00' ? $item['unpublish'] : '';

            $item = array_replace_recursive($this->arc['setting'], $item);

            unset($item['controller'], $item['model'], $item['template'], $item['modification'], $item['event']);
        }

        return $item;
    }
}
