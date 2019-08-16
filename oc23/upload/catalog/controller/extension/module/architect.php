<?php
class ControllerExtensionModuleArchitect extends Controller
{
    protected $arc = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->config->load('architect');
        $this->arc = $this->config->get('architect');

        $this->load->model($this->arc['path_module']);
        $this->arc['model'] = $this->{$this->arc['model']};
    }

    public function index($setting = array())
    {
        if (empty($setting['identifier']) || !$params = $this->arc['model']->getSubModule($setting['identifier'])) {
            return null;
        }

        return $this->load->controller('extension/architect/' . $setting['identifier'], $params);
    }
}
