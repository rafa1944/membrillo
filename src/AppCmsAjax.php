<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 * >> $_REQUEST: 'ajaxsv', 'sys_ajaxsv'
 */

namespace angelrove\membrillo;

use angelrove\membrillo\Login\LoginCtrl;
use angelrove\membrillo\WApp\Session;

class AppCmsAjax extends Application
{
    //-----------------------------------------------------------------
    public function __construct($document_root)
    {
        //-------
        parent::__construct($document_root);

        //----------------------------------------------------
        /* Globals */
        global $CONFIG_SECCIONES,
               $seccCtrl,
               $objectsStatus;

        //----------------------------------------------------//
        LoginCtrl::init_ajax();

        $CONFIG_SECCIONES = Session::get('CONFIG_SECCIONES');
        $seccCtrl         = Session::get('seccCtrl'); //$seccCtrl->initPage();
        $objectsStatus    = Session::get('objectsStatus');

        //----------------------------------------------------
        /* System services */
        $this->system_services();

        //----------------------------------------------------//
        /* Load on init */
        require DOCUMENT_ROOT . '/app/onInitPage.inc';

        //----------------------------------------------------//
        /* User Service */
        $secc_dir = '';
        if (isset($_REQUEST['secc']) && $_REQUEST['secc']) {
            $secc_dir = DOCUMENT_ROOT . '/app/' . $_REQUEST['secc'];
        } else {
            $secc_dir = $CONFIG_SECCIONES->getFolder($seccCtrl->secc);
        }

        $service_path        = $secc_dir . '/ajax-'   . $_REQUEST['ajaxsv'] . '.inc';
        $service_path_script = $secc_dir . '/script-' . $_REQUEST['ajaxsv'] . '.php';

        // Load service ----
        try {
            if (file_exists($service_path)) {
                include $service_path;
            } elseif (file_exists($service_path_script)) {
                include $service_path_script;
            } else {
                throw new \Exception("ajax error: Service not found [$service_path]");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    //-----------------------------------------------------------------
    private function system_services()
    {
        if (!isset($_REQUEST['sys_ajaxsv'])) {
            return true;
        }

        switch ($_REQUEST['sys_ajaxsv']) {
            case 'Messages_get':
                Messages::get();
                break;

            default:
                throw new \Exception('membrillo error: service not found');
                break;
        }

        exit();
    }
    //-----------------------------------------------------------------
}
