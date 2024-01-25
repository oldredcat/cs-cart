<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//  run cron job http://site.com/index.php?dispatch=notifications.cron&cron_password=MYPASS
if ($mode == 'cron') {
    fn_red_get_products_cron_job();
    exit;
}