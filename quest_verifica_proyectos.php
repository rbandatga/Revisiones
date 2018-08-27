<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
#########################Conexión a gquest.#####################################
/*$quest_db = new mysqli('216.70.88.35','donquest','As$3d4%Re','quest');
if (mysqli_connect_errno()) {echo 'fallo quest_db';exit();}*/
################################################################################

#CONEXION DESARROLLO
// quest
$quest_db = new mysqli('localhost','quest_crond','s5CdZS&_','quest');
if (mysqli_connect_errno()) {echo 'fallo quest_db';exit();}

$total = 0;
################################################################################
$res0 = $quest_db->query("SELECT a.idInmobiliaria
						    FROM zz_glead_inmobiliaria a
						   WHERE a.estado 	   = 1
						     AND a.gquest  	   = 1
						     AND a.sinProyecto = 0
						         LIMIT 1;");
if($res0->num_rows > 0){
	echo "hay datos a procesar <br><br>";
	$row0 			 = mysqli_fetch_array($res0);
	$res1 			 = _getRespuestas($row0);
	#$totalEncuestas	 = _getTotalEncuestas();
	if($res1->num_rows > 0){
		echo "> hay respuestas <br>";
		$totalInsert = 0;
		while($row1 = mysqli_fetch_array($res1)){
			if(!_getExisteProyecto($row1)){
				$row1 = _getDonde($row1);
				_setInsRespuesta($row1);
				echo ">> inserta <br>";
				$totalInsert++;
				$total++;
			}else{
				echo ">> no inserta, duplicado <br>";
			}
		}
	}else{
		echo "> no hay datos a procesar <br>";
		echo ">>> actualiza campo sin proyecto <br>";
		_setUpdSinProyecto($row0);
	}
}else{
	echo "> ACTUALIZA ALL <br>";
	_setUpdAllSinProyecto();
}
function _getDonde($row0){
	$row0['idDonde'] = $row0['idDonde'] == "Survey" ? 4 : $row0['idDonde'] ;
	$row0['donde']   = "Quest no encontro donde";
	$res1 = $GLOBALS['quest_db']->query("SELECT a.nombre AS donde
                                            FROM tt_tema_donde a
                                           WHERE a.idDonde = ".$row0['idDonde']."
                                             AND a.idPais  = ".$row0['idPais'].";");
	if(!$res1){echo "> SENTENCIA _getDonde INVALIDA";exit;}
	
	if($res1->num_rows > 0){
		$row1 = mysqli_fetch_array($res1);
		$row0['donde'] = $row1['donde'];
	}
	return $row0;
}
function _getExisteProyecto($row){
	$idRespuestaEncuesta  = $row['idRespuestaEncuesta'];
	$ID_INMOBILIARIA      = $row['idInmobiliaria'];
	$idProyecto           = $row['idProyecto'];
	$res0 = $GLOBALS['quest_db']->query("SELECT 1
										   FROM zz_glead_respuestas     a
										  WHERE a.idRespuestaEncuesta   = $idRespuestaEncuesta
										    AND a.idInmobiliaria 	    = $ID_INMOBILIARIA
										    AND a.idProyecto     	    = $idProyecto
										        LIMIT 1;");
	if(!$res0){echo "> SENTENCIA _getExisteProyecto($row) INVALIDA";exit;}
	return ($res0->num_rows > 0)?true:false;
}

function _getRespuestas($row){
	$ID_INMOBILIARIA = $row['idInmobiliaria'];
	$res0 = $GLOBALS['quest_db']->query("
									SELECT a.idEncuesta AS idRespuestaEncuesta, a.idInmobiliaria, b.idProyecto, a.email, a.fechaTermino, a.donde AS idDonde, a.idPais, a.idUser, a.idEmail_unico
									  FROM zz_glead_encuesta a
								 LEFT JOIN proyectos_quest b ON b.idInmobiliaria = a.idInmobiliaria AND b.estado = 1
								 LEFT JOIN zz_glead_respuestas c ON c.idRespuestaEncuesta = a.idEncuesta AND c.idInmobiliaria = a.idInmobiliaria AND c.Correo = a.email AND c.idProyecto = b.idProyecto
								     WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.avance         = 999
									   AND a.estado         = 2
									   AND a.X1             = 1
									   AND a.X10            > 0
									   AND a.proceso        = 3
									   AND a.fechaTermino   > DATE_ADD(NOW(), INTERVAL - 8 MONTH)
									   AND b.idInmobiliaria      IS NOT NULL
									   AND c.idRespuestaEncuesta IS NULL
									       LIMIT 30;");
	if(!$res0){echo "> SENTENCIA _getEncuestas INVALIDA";exit;}
	return $res0;
}
/*function _getTotalEncuestas(){
	$res0 = $GLOBALS['quest_db']->query("SELECT FOUND_ROWS() AS total;");
	if(!$res0){echo "> SENTENCIA _getTotalEncuestas INVALIDA";exit;}
	$row0 			 = mysqli_fetch_array($res0);
	return $row0['total'];
}*/
function _setInsRespuesta($row){
	$res0 = $GLOBALS['quest_db']->query("INSERT INTO zz_glead_respuestas
													( idRespuestaEncuesta
													, idPais
													, idInmobiliaria
													, idProyecto
													, idUser
													, idEmail_unico
													, Correo
													, idDonde
													, donde
													, X1
													, fechaPuntos
													, activo
													, estado)
							                  VALUES (".$row['idRespuestaEncuesta']."
							                        , ".$row['idPais']."
							                        , ".$row['idInmobiliaria']."
							                        , ".$row['idProyecto']."
							                        , ".$row['idUser']."
							                        , ".$row['idEmail_unico']."
							                        , '".$row['email']."'
							                        , ".$row['idDonde']."
							                        , '".$row['donde']."'
							                        , 1
							                        , '".$row['fechaTermino']."'
							                        , 1
							                        , 'X'
							                    	);");
	if(!$res0){echo "la insersión _setInsRespuesta es incorrecto <br><br>";exit;}
	if($GLOBALS['quest_db']->affected_rows == 0){echo "No se pudo insertar en _setInsRespuesta";}
}
function _setUpdSinProyecto($row){
	$ID_INMOBILIARIA = $row['idInmobiliaria'];
	$res0 = $GLOBALS['quest_db']->query("UPDATE zz_glead_inmobiliaria a
										     SET a.sinProyecto      = 1
										   WHERE a.idInmobiliaria   = $ID_INMOBILIARIA
									  	         LIMIT 1;");
	if(!$res0){echo "El update _setUpdSinProyecto es incorrecto <br><br>";exit;}
	if($GLOBALS['quest_db']->affected_rows == 0){echo "No se pudo update en _setUpdSinProyecto";}
}
function _setUpdAllSinProyecto(){
	$res0 = $GLOBALS['quest_db']->query("UPDATE zz_glead_inmobiliaria a
										    SET a.sinProyecto   = 0
										  WHERE a.estado        = 1
										    AND a.gquest        = 1
										    AND a.sinProyecto   = 1;");
	if(!$res0){echo "El update _setUpdAllSinProyecto es incorrecto <br><br>";exit;}
}
mysqli_close($quest_db);
# ------------------------------------------------------
#  monitor
# ------------------------------------------------------
$idCron = 183;exit;
include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
?>
