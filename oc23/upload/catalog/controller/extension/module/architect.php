<?php
class ControllerExtensionModuleArchitect extends Controller
{
    protected $arc = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->config->load('architect');
        $this->arc = $this->config->get('architect');
    }

    public function index($setting = array())
    {
        return $this->load->controller('extension/module/architect/' . $setting['identifier'], $setting);
    }
}
