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
        $this->arc['setting']['module_id'] = isset($this->request->get['module_id']) ? (int)$this->request->get['module_id'] : 0;

        $this->i18n = $this->load->language($this->arc['path_module']);

        $this->arc['url_token']         = sprintf($this->arc['token_url'], $this->session->data[$this->arc['token_part']]);
        $this->arc['url_module']        = $this->url->link($this->arc['path_module'], $this->arc['url_token'], true);
        $this->arc['url_module_manage'] = $this->url->link($this->arc['path_module'] . '/manage', $this->arc['url_token'], true);
        $this->arc['url_module_save']   = $this->url->link($this->arc['path_module'] . '/save', $this->arc['url_token'], true);
        $this->arc['url_extension']     = $this->url->link($this->arc['url_extension'], $this->arc['url_token'] .  $this->arc['ext_type'], true);

        $this->arc['url_ocmod_refresh'] = $this->url->link('extension/modification/refresh', $this->arc['url_token'], true);
        $this->arc['msg_ocmod_refresh'] = sprintf($this->i18n['notify_ocmod_refresh'], $this->arc['url_ocmod_refresh']);
    }

    /**
     * Sub-module editor
     */
    public function index()
    {
        if (!isset($this->request->get['module_id'])) {
            $this->response->redirect($this->arc['url_module_manage']);
        }

        $this->document->setTitle($this->arc['title']);

        $this->document->addStyle('view/javascript/architect/codemirror/codemirror.css');
        $this->document->addStyle('view/javascript/architect/codemirror/addon/fold/foldgutter.css');
        $this->document->addScript('view/javascript/architect/codemirror/codemirror.js');

        $this->document->addScript('view/javascript/architect/codemirror/addon/selection/active-line.js');
        $this->document->addScript('view/javascript/architect/codemirror/addon/edit/matchbrackets.js');
        $this->document->addScript('view/javascript/architect/codemirror/addon/fold/foldcode.js');
        $this->document->addScript('view/javascript/architect/codemirror/addon/fold/foldgutter.js');
        $this->document->addScript('view/javascript/architect/codemirror/addon/fold/brace-fold.js');
        $this->document->addScript('view/javascript/architect/codemirror/addon/fold/xml-fold.js');
        $this->document->addScript('view/javascript/architect/codemirror/addon/fold/indent-fold.js');
        $this->document->addScript('view/javascript/architect/codemirror/addon/fold/markdown-fold.js');
        $this->document->addScript('view/javascript/architect/codemirror/addon/fold/comment-fold.js');

        $this->document->addScript('view/javascript/architect/codemirror/mode/xml/xml.js');
        $this->document->addScript('view/javascript/architect/codemirror/mode/javascript/javascript.js');
        $this->document->addScript('view/javascript/architect/codemirror/mode/css/css.js');
        $this->document->addScript('view/javascript/architect/codemirror/mode/htmlmixed/htmlmixed.js');
        $this->document->addScript('view/javascript/architect/codemirror/mode/clike/clike.js');
        $this->document->addScript('view/javascript/architect/codemirror/mode/php/php.js');

        $this->document->addStyle('view/javascript/architect/style.css?v=' . $this->arc['version']);
        $this->document->addScript('view/javascript/architect/script.js?v=' . $this->arc['version']);

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
                'href'  => $this->arc['url_extension']
            ),
            array(
                'text'  => $this->arc['title'],
                'href'  => $this->arc['url_module_manage']
            ),
            array(
                'text'  => $this->arc['setting']['module_id'] ? $data['i18n']['text_edit'] . ' #' . $this->arc['setting']['module_id'] : $data['i18n']['text_insert'],
                'href'  => $this->arc['url_module'] . '&module_id=' . $this->arc['setting']['module_id']
            )
        );

        // === Content

        // Quick Reference
        $data['docs'] = array(
            'module_id'  => '0',
            'author'     => 'johndoe',
            'identifier' => $this->arc['setting']['identifier']
        );

        // Edit Sub-module
        if ($this->arc['setting']['module_id']) {
            $data['architect']['setting'] = array_replace_recursive(
                $this->arc['setting'],
                $module = $this->arc['model']->getModule($this->arc['setting']['module_id'])
            );

            if (empty($module['module_id'])) {
                $this->response->redirect($this->arc['url_module_manage']);
            }

            $data['docs'] = array(
                'module_id'  => $data['architect']['setting']['module_id'],
                'author'     => $data['architect']['setting']['meta']['author'],
                'identifier' => $data['architect']['setting']['identifier']
            );

        // New Sub-module
        } else {
            $data['architect']['setting']['meta']['editor']['controller'] = 1;
            $data['architect']['setting']['meta']['editor']['model'] = 1;
            $data['architect']['setting']['meta']['editor']['template'] = 1;

            if (isset($this->request->get['gist'])) {
                $gist = $this->arc['model']->getGist($this->request->get['gist']);

                if ($gist) {
                    $editors = array('controller', 'model', 'template', 'modification', 'event', 'admin_controller');

                    foreach ($editors as $editor) {
                        $data['architect']['setting']['meta']['editor'][$editor] = 0;

                        if ($gist[$editor]) {
                            $data['architect']['setting'][$editor] = $gist[$editor];
                            $data['architect']['setting']['meta']['editor'][$editor] = 1;
                        }
                    }
                }
            }
        }

        // Options
        $this->load->model('customer/customer_group');

        $data['default_cust_group'] = $this->config->get('config_customer_group_id');
        $data['customer_groups']    = $this->model_customer_customer_group->getCustomerGroups();

        $data['tab_option'] = $this->load->view($this->arc['path_module'] . '/editor_tab_option', $data);
        $data['quick_reference'] = $this->load->view($this->arc['path_module'] . '/editor_reference', $data);

        // === Page element
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->arc['path_module'] . '/editor', $data));
    }

    public function save()
    {
        $post     = $this->request->post;
        $response = array(
            'module_id' => $post['module_id'],
            'error' => ''
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

    /**
     * Module dashboard
     */
    public function manage()
    {
        $this->update();

        $this->document->setTitle($this->arc['title']);

        $this->document->addStyle('view/javascript/architect/style.css');
        $this->document->addScript('view/javascript/architect/script.js');

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
                'href'  => $this->arc['url_extension']
            ),
            array(
                'text'  => $this->arc['title'],
                'href'  => $this->arc['url_module_manage']
            )
        );

        // === Content

        $data['urlTicketSupport']   = 'https://isenselabs.com/tickets/open/' . base64_encode('Support Request').'/'.base64_encode('414').'/'. base64_encode($_SERVER['SERVER_NAME']);

        // Tab content
        $data['tab_manage']  = $this->load->view($this->arc['path_module'] .'/tab_manage', $data);
        $data['tab_gist']    = $this->load->view($this->arc['path_module'] .'/tab_gist', $data);
        $data['tab_help']    = $this->load->view($this->arc['path_module'] .'/tab_help', $data);

        // === Page element
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->arc['path_module'], $data));
    }

    public function manageList()
    {
        $limit      = 25;
        $page       = isset($this->request->get) && (int)$this->request->get > 0 ? (int)$this->request->get : 1;
        $response   = array();
        $params     = array(
            'page'  => $page,
            'limit' => $limit,
            'start' => ($page - 1) * $limit,
        );
        $data       = array(
            'i18n'  => $this->i18n,
            'items' => $this->arc['model']->getItems($params),
        );

        $total_item = $this->arc['model']->getTotalItems($params);

        $pagination        = new Pagination();
        $pagination->total = $total_item;
        $pagination->page  = $page;
        $pagination->limit = $limit;
        $pagination->url   = $this->url->link($this->arc['path_module'] . '/manageList', $this->arc['url_token'] . '&page={page}', true);

        $response['items']           = count($data['items']);
        $response['output']          = $this->load->view($this->arc['path_module'] . '/manage_list', $data);
        $response['pagination']      = $pagination->render();
        $response['pagination_info'] = sprintf($this->language->get('text_pagination'), ($total_item) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total_item - $limit)) ? $total_item : ((($page - 1) * $limit) + $limit), $total_item, ceil($total_item / $limit));

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($response));
    }

    public function manageUpdate()
    {
        $post = $this->request->post;
        $response = $post;
        $response['error'] = '';

        if (!$this->user->hasPermission('modify', $this->arc['path_module'])) {
            $response['error'] = $this->i18n['error_permission'];
        }

        if (!$response['error']) {
            switch ($post['action']) {
                case 'delete':
                    $this->load->model('extension/module');
                    $this->model_extension_module->deleteModule($post['module_id']);
                    break;

                default:
                    $response['error'] = $this->i18n['error_action_type'];
                    break;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($response));
    }

    public function gistList()
    {
        $response   = array();
        $data       = array(
            'i18n'      => $this->i18n,
            'architect' => $this->arc,
            'gists'     => $this->arc['model']->getGists(),
        );

        $response['items']  = count($data['gists']);
        $response['output'] = $this->load->view($this->arc['path_module'] . '/gist_list', $data);

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
    public function hookNav($navs = array())
    {
        if ($this->config->get('architect_install')) {
            $navs[] = array(
                'id'       => 'menu-architect',
                'icon'     => 'hidden"></i>
                    <span class="arc-visit" style="
                        display: inline-block;
                        color: #e8e8e8;
                        background: rgba(0,0,0,.3);
                        font-weight: 600;
                        font-size: 17px;
                        font-family: \'Open Sans\', sans-serif;
                        text-align: center;
                        line-height: 17px;
                        width: 21px;
                        height: 21px;
                        margin-left: -2px;
                        padding: 1px 2px 3px 2px;
                        border-radius: 36px;
                        text-shadow: 0 1px 1px rgba(0,0,0,.2);
                    ">A</span>
                    <style>a[href*="' . $this->arc['path_module'] . '"]:hover .arc-visit { color: #ffde06 !important; }</style>
                    <i class="hidden',
                'name'     => 'Architect',
                'href'     => $this->arc['url_module_manage'] . '" title="Architect by iSenseLabs',
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
            $this->arc['model']->deleteModule($params['module_id'], false);
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
        $this->arc['model']->update();
    }
}
