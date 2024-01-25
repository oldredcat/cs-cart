<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//  run cron job http://site.com/index.php?dispatch=notifications.cron&cron_password=MYPASS
if ($mode == 'cron') {

    $cron_password = \Tygh\Registry::get('settings.Security.cron_password');

    // do not allow access if the passwords do not match
    if ((!isset($_REQUEST['cron_password']) || $cron_password != $_REQUEST['cron_password']) && (!empty($cron_password)))
    {
        die(__('access_denied'));
        exit;
    }
    
    fn_red_get_products_cron_job();
    exit;
}