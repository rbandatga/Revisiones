<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Boletin extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('html');
        $this->load->library('user_agent');
        $this->load->database();
        $this->load->helper('cookie');
        session_start();

        $this->load->library('Gleads');
        $this->load->library('Gleadsventas');

        $this->load->library('Valentina');
        $this->load->library('Valentinalista');
        $this->load->library('Valentinausuarios');
        $this->load->library('pagination');
        $this->load->helper(array('form'));
        $this->load->library('form_validation');

        if(!isset($_SESSION['user'])){
            # marcar cookie para volver
            $cookie = array(
                'name'   => 'G-Leads_boletin',
                'value'  => "iniciar-si-o-si",
                'expire' => '86500',
                'domain' => '.tgapps.net',
                'path'   => '/',
                'prefix' => '',
                'secure' => FALSE
            );
            $this->input->set_cookie($cookie);
            ?>
            <script type="text/javascript">window.location="<?php echo base_url();?>entrar";</script>
            <?php
            exit;
        }else if ($_SESSION['session'] != 'reporte') {
            if ($this->valentinausuarios->tego_permiso($_SESSION['user'],'Session reportes','Session')=='gleads/reportes') {
                $_SESSION['session'] = 'reporte';
            }
        }

        if(isset($_SESSION['user'])){$session=$_SESSION['user'];}else{$session=null;}
        $this->valentinausuarios->user($session);

        if ($_SESSION['session'] != 'reporte') {
            ?>
            <script type="text/javascript">window.location="<?php echo base_url();?>mod/entrada/close_user";</script>
            <?php
            exit;
        }

        if ( $this->input->cookie('G-Leads_boletin', TRUE) == 'iniciar-si-o-si' ) {
            delete_cookie('G-Leads_boletin', '.tgapps.net', '/');
        }

    }
    public function index(){
        $data['tituloLlama']          = "G-Leads - boletin";

        // Sitio normal
        $data['urlPaginaLlama']       = "boletin/index";
        $data['CssLlama1']            = "css/sitio/boletin/boletin.css";
        # $data['jsLlama1']             = "js/sitio/boletin/exporting.js";

        $data['bootstrap']            = 1;
        $data['bootstrapTheme']       = 1;
        $data['popLlama']             = 1;
        $data['jqueryUi']             = 1;

        $data['jqueryLlama']          = 1;
        $data['jqFormLlama']          = 1;
        $data['mutate']               = 1;

        $data['autoJavaScriptLlama']  = 1;

        $data['highchartsLlama']      = 1;
        $data['cssFijo']              = 1;
        $data['cssMod']               = 1;
        $data['urlposthead']          = "configuracion/urlposthead";


        $this->load->view('valentina/paginas/llama_pagina',$data);
    }
    public function boletin(){



    }
}
