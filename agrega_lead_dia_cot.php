<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
# =========================================================
$totalG = 0;
$total  = 0;
# =========================================================

$tabla       = "zz_glead_vendedor_categoriza";
$campo_id    = "idCategorizar";
$campo_email = "email";
$campo_fecha = "fechaCategorizado";
$filtro      = "";
$tipo        = 2;

 include 'agrega_lead_dia.php';

# =========================================================
# monitor

echo "total: ".$total."<br>";
$totalG = $totalG - $total;
echo "totalG: ".$totalG."<br>";

$idCron = 188;
include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
# =========================================================
?>