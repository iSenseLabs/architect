<?php
class ControllerExtensionModuleArchitect extends Controller
{
    protected $arc  = array();
    protected $i18n = array();
    protected $ocmod_refresh = false;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->config->load('architect');
        $this->arc = $this->config->get('architect');

        $this->load->model($this->arc['path_module']);
        $this->arc['model'] = $this->{$this->arc['model']};
        $this->arc['setting']['module_id'] = isset($this->request->get['module_id']) ? $this->request->get['module_id'] : 0;

        $this->i18n = $this->load->language($this->arc['path_module']);

        $this->arc['url_token']     = sprintf($this->arc['token_url'], $this->session->data[$this->arc['token_part']]);
        $this->arc['url_module']    = $this->url->link($this->arc['path_module'], $this->arc['url_token'], true);
        $this->arc['url_save']      = $this->url->link($this->arc['path_module'] . '/save', $this->arc['url_token'], true);
        $this->arc['url_extension'] = $this->url->link($this->arc['url_extension'], $this->arc['url_token'] .  $this->arc['ext_type'], true);

        $ocmod_refresh_path = $this->arc['opencart'] >= 30 ? 'marketplace/modification/refresh' : 'extension/modification/refresh';
        $this->arc['url_ocmod_refresh'] = $this->url->link($ocmod_refresh_path, $this->arc['url_token'], true);
        $this->arc['msg_ocmod_refresh'] = sprintf($this->i18n['notify_ocmod_refresh'], $this->arc['url_ocmod_refresh']);
    }

    public function index()
    {
        $this->document->setTitle($this->arc['title']);

        $this->document->addStyle('view/asset/architect/codemirror/codemirror.css');
        $this->document->addStyle('view/asset/architect/codemirror/addon/fold/foldgutter.css');
        $this->document->addScript('view/asset/architect/codemirror/codemirror.js');

        $this->document->addScript('view/asset/architect/codemirror/addon/selection/active-line.js');
        $this->document->addScript('view/asset/architect/codemirror/addon/edit/matchbrackets.js');
        $this->document->addScript('view/asset/architect/codemirror/addon/fold/foldcode.js');
        $this->document->addScript('view/asset/architect/codemirror/addon/fold/foldgutter.js');
        $this->document->addScript('view/asset/architect/codemirror/addon/fold/brace-fold.js');
        $this->document->addScript('view/asset/architect/codemirror/addon/fold/xml-fold.js');
        $this->document->addScript('view/asset/architect/codemirror/addon/fold/indent-fold.js');
        $this->document->addScript('view/asset/architect/codemirror/addon/fold/markdown-fold.js');
        $this->document->addScript('view/asset/architect/codemirror/addon/fold/comment-fold.js');

        $this->document->addScript('view/asset/architect/codemirror/mode/xml/xml.js');
        $this->document->addScript('view/asset/architect/codemirror/mode/javascript/javascript.js');
        $this->document->addScript('view/asset/architect/codemirror/mode/css/css.js');
        $this->document->addScript('view/asset/architect/codemirror/mode/htmlmixed/htmlmixed.js');
        $this->document->addScript('view/asset/architect/codemirror/mode/clike/clike.js');
        $this->document->addScript('view/asset/architect/codemirror/mode/php/php.js');

        $this->document->addStyle('view/asset/architect/style.css');
        $this->document->addScript('view/asset/architect/script.js');

        $data = array(
            'i18n'          => $this->i18n,
            'architect'     => $this->arc,
            'notifications' => array()
        );

        $data['breadcrumbs'] = array(
            array(
                'text'  => $data['i18n']['text_home'],
                'href'  => $this->url->link('common/dashboard', $this->arc['url_token'], true)
            ),
            array(
                'text'  => $data['i18n']['text_modules'],
                'href'  =>  $this->arc['url_extension']
            ),
            array(
                'text'  =>  $this->arc['title'],
                'href'  =>  $this->arc['url_module']
            )
        );

        // === Content
        if ($this->arc['setting']['module_id']) {
            $data['architect']['setting'] = array_replace_recursive(
                $this->arc['setting'],
                $this->arc['model']->getModule($this->arc['setting']['module_id'])
            );
        }

        // === Page element
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->arc['path_module'], $data));
    }

    public function save()
    {
        $post     = $this->request->post;
        $response = array(
            'module_id' => $post['module_id'],
            'error' => '',
            // 'post'  => $post
        );

        if (!isset($post['module_id'])) {
            return null;
        }

        if (!$this->user->hasPermission('modify', $this->arc['path_module'])) {
            $response['error'] = $this->i18n['error_permission'];
        }

        if (!$response['error']) {
            $response = $this->arc['model']->editModule($post);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($response));
    }

    // ================ Misc ================

    /**
     * Add module link to admin sidebar navigation
     *
     * @param  array $navs
     *
     * @return array
     */
    public function hookNav($navs)
    {
        if ($this->config->get('architect_install')) {
            $navs[] = array(
                'id'       => 'menu-architect',
                'icon'     => 'fa-buysellads fw arc-visit" style="font-size:18px;color:#fff;" attr="',
                'name'     => 'Architect',
                'href'     => $this->arc['url_module'],
                'children' => array()
            );
        }

        return $navs;
    }

    /**
     * Delete architect database entry and files
     *
     * @param  array $params
     *
     * @return null
     */
    public function hookDelete($params = array())
    {
        if ($this->config->get('architect_install') && !empty($params['module_id'])) {
            $this->arc['model']->deleteModule($params['module_id'], true);
        }
    }


    // ================ Setup ================

    public function install()
    {
        $this->arc['model']->install();
    }

    public function uninstall()
    {
        $this->arc['model']->uninstall();
    }

    public function update()
    {
    }
}
