<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
#########################Conexión a gleads.#####################################
include('/var/www/php/crones/conecta/web/conexion_gleads.php');
#########################Conexión a respaldo.###################################
//
$ambiente = 'https://gleads.desarrollo.cool/gleads/Landing/cambioPass/';
//$ambiente = 'https://crm.g-leads.com/entrada';
if (mysqli_connect_errno()) {echo 'fallo gleads_db';exit();}
################################################################################
// CONSULTA PARA SACAR LAS ZONAS HORARIAS
$query      =    "SELECT zonaHoraria
    				FROM app_user_password
    			WHERE  estado = 0
    				GROUP BY zonaHoraria;";
$result     = $gleads_db->query($query);
$salida2    = 0;
if ($result->num_rows > 0) {
    while($row = $result->fetch_array()) {
    	// PROCESO PARA SACAR EL DÍA DE HOY POR ZONA HORARIA
	    $zonaHoraria 		= $row["zonaHoraria"];
	    date_default_timezone_set($zonaHoraria);
        $date = date( "Y-m-d" );
        $ayer = date( "Y-m-d", strtotime( "-1 day", strtotime( $date ) ) );  

        $query2 = " SELECT a.mailSolicita,a.idUserPass,a.envios
                      FROM app_user_password a 
                    WHERE  a.estado = 0
                      AND DATE_FORMAT(a.fecha,'%Y-%m-%d') = '$ayer'
                    LIMIT 50;";
				$result2 = $gleads_db->query($query2);
                while($row2 = $result2->fetch_array()) {
                    $mailSolicita       = $row2["mailSolicita"];
                    $idUserPass         = $row2["idUserPass"];
                    $salida2       = $result2->num_rows;
                    $gleads_db->query(" UPDATE app_user_password 
                                            SET estado      = 2
                                        WHERE 
                                            mailSolicita    = '".$mailSolicita."'
                                            AND idUserPass = $idUserPass
                                        LIMIT 1;");
                }

	}
}

echo "[update:$salida2]<br>";
// ------------------------------------------------------
//  monitor
// ------------------------------------------------------

// monitoreo crones
$idCron = 217;
$total = $salida2;
mysqli_close($gleads_db);
include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
 ?>