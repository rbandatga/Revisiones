<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('UTC');

#########################Conexión a gleads.#####################################
//include('/var/www/php/crones/conecta/web/conexion_gleads.php');
#########################Conexión a respaldo.###################################
/*$gquest_db = new mysqli('216.70.88.35','donquest','As$3d4%Re','quest');
if (mysqli_connect_errno()) {echo 'fallo quest_db';exit();}*/
################################################################################


#CONEXION DESARROLLO
// gleads
$gleads_db = new mysqli('localhost','gls_intern_p','Xr75ds_ya35d','gleadsx');
if (mysqli_connect_errno()) {echo 'fallo gleads_db';exit();}
$tipo = 0;
$query = "SELECT idEmail_unico,email,idUser,idInmobiliaria,idProyecto,donde,fechaPromesa,idPais,idPromesaIn
					FROM zz_glead_promesa
			WHERE verificaGestion = 0
					AND idConexion = 0
					AND cancelada = 0
					AND descarte = 0
					AND idSV = 0
					AND llegada = 'Panel G-Leads'
				ORDER BY fechaPromesa DESC
				LIMIT 100;";
$result = $gleads_db->query($query);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
	    $idEmail_unico 		= $row["idEmail_unico"];
	    $email 				= $row["email"];
	    $ID_INMOBILIARIA 	= $row["idInmobiliaria"];
	    $idProyecto 		= $row["idProyecto"];
	    $donde 				= $row["donde"];
	    $fechaPromesa 		= $row["fechaPromesa"];
	    $idPromesaIn 		= $row["idPromesaIn"];
	    $idUser 			= $row["idUser"];
	    $idPais 			= $row["idPais"];
	    if (lcfirst($donde) == lcfirst('categorizados')) {
	    	$tipo = 1;
	    }else if(lcfirst($donde) == lcfirst('cotizaciones')){
	    	$tipo = 2;
	    }
	    $queryValida = "SELECT 1
						FROM zz_glead_gestion_pro_master
				WHERE 	email = '$email'
						AND idInmobiliaria = $ID_INMOBILIARIA
						AND proyectos = $idProyecto
						AND creado = 2
						AND idEmail_unico = $idEmail_unico
						AND idOpcion = 17;";
		$resultValida = $gleads_db->query($queryValida);
		if ($resultValida->num_rows > 0) {	
		    $queryValidaUp = "UPDATE zz_glead_promesa
								SET verificaGestion = 1
							WHERE 	email = '$email'
									AND idInmobiliaria = $ID_INMOBILIARIA
									AND idProyecto = $idProyecto 
									AND idPromesaIn = $idPromesaIn;";
			if($gleads_db->query($queryValidaUp)){
                echo "PROMESA - INMOBILIARIA: $ID_INMOBILIARIA -- actualizada <br>";
			}
		}else{
			$fecha 				= new DateTime($fechaPromesa);
			$fechaAnio 			= date_format($fecha, 'Y');
			$fechaMes 			= date_format($fecha, 'm');
			$fechaDia 			= date_format($fecha, 'd');
			$queryValidaInsrt = "INSERT INTO zz_glead_gestion_pro_master (tipo,creado,idInmobiliaria,fecha,fechaAnio,fechaMes,fechaDia
											,fechaUtc,idEmail_unico,idUser,idOpcion,opcion,email,proyectos,idPais,typeAction,origen,procesoD)
								VALUES ($tipo, 2, $ID_INMOBILIARIA,'$fechaPromesa','$fechaAnio','$fechaMes','$fechaDia','$fechaPromesa',$idEmail_unico,$idUser,17,'Cerro negocio desde G-Leads','$email',$idProyecto,$idPais,0,0,1);";
			if($gleads_db->query($queryValidaInsrt)){
                echo "PROMESA - INMOBILIARIA: $ID_INMOBILIARIA -- insertada <br>";
				    $queryValidaUp = "UPDATE zz_glead_promesa
										SET verificaGestion = 1
									WHERE 	email = '$email'
											AND idInmobiliaria = $ID_INMOBILIARIA
											AND idProyecto = $idProyecto 
											AND idPromesaIn = $idPromesaIn
									LIMIT 1;";
					if($gleads_db->query($queryValidaUp)){
		                echo "PROMESA - INMOBILIARIA: $ID_INMOBILIARIA -- actualizada <br>";
					}
			}

		}
	}
}


mysqli_close($gleads_db);
// ------------------------------------------------------
//  monitor
// ------------------------------------------------------
$idCron = 131;
//include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');