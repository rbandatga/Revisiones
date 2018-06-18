<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
# =========================================================
$totalG = 0;
$total  = 0;
# =========================================================

$tabla       = "zz_glead_respuestas";
$campo_id    = "idRepuesta";
$campo_email = "Correo";
$campo_fecha = "fechaPuntos";
$filtro      = "AND a.estado != 'H'";
$tipo        = 1;

 include 'agrega_lead_dia.php';

# =========================================================
# monitor

echo "total: ".$total."<br>";
$totalG = $totalG - $total;
echo "totalG: ".$totalG."<br>";

$idCron = 189;
include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
# =========================================================
?>
