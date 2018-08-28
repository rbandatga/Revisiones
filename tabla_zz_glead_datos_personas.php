<?php
date_default_timezone_set("America/Santiago");
$hora           = time();
$fecha          = date ("Y-m-d H:i:s", $hora);
$salida         = 0;
$TotalG         = 0;
$TotalRegistros = 0;
$total          = 0;
$correr         = 0;
$update         = 0;
$falla          = 0;
$idOriginal     = array();
include('/var/www/php/crones/conecta/web/conexion_gleads_respaldo.php');

$tabla          = 'zz_glead_datos_personas';
$idCron         = 89;
$totalLista     = 30;
$id             = 'idPersonas';
$compoOrden     = 'idPersonas';
$campos         = 'idPersonas,idInmobiliaria,idPuente,idEmail_unico,mail,nombre,apellido,comuna,telefono,rut,fecNac,IFNULL(monto,0) as monto,fechaIngreso,activo,idConexion,prueba,respaldo,IFNULL(idPais,0) as idPais,IFNULL(idRegion,0) as idRegion,IFNULL(idCiudad,0) as idCiudad,IFNULL(idComuna,0) as idComuna,sexo,respaldoTable'; /*Para Select*/
$camposins      = 'idPersonas,idInmobiliaria,idPuente,idEmail_unico,mail,nombre,apellido,comuna,telefono,rut,fecNac,monto,fechaIngreso,activo,idConexion,prueba,respaldo,idPais,idRegion,idCiudad,idComuna,sexo,respaldoTable'; /*Para Insert*/
# =======================================================
# PASO 1: ver si tiene registros
# =======================================================
$query = "SELECT SQL_CALC_FOUND_ROWS 1
			FROM $tabla
		   WHERE estadoRegistro = 0
		   LIMIT 1;";
$result = $gleads_db->query($query);

$query2  = "SELECT FOUND_ROWS() AS total;";
$result2 = $gleads_db->query($query2);
if ($result2->num_rows > 0) {
    $row2 = $result2->fetch_assoc();
    $TotalRegistros = $row2["total"];
    $TotalG = $row2["total"];
}
if ($TotalRegistros>0) {$correr = 1;}
$comillas = "'";

# =======================================================
# PASO 2: guardar registro y marcar = 2
# =======================================================
if ($correr == 1) {
	# ----------------- inicio
	$query = "SELECT $campos
				FROM $tabla
			   WHERE estadoRegistro = 0
			ORDER BY $compoOrden DESC
			   LIMIT $totalLista;";
	$result = $gleads_db->query($query);

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$idID              =  $row['idPersonas'];
			$idPersonas        =  $row['idPersonas'];
			$idInmobiliaria    =  $row['idInmobiliaria'];
			$idPuente          =  $row['idPuente'];
			$idEmail_unico     =  $row['idEmail_unico'];
			$mail              =  $row['mail'];
			$nombre            =  $row['nombre'];
			$apellido          =  $row['apellido'];
			$comuna            =  $row['comuna'];
			$telefono          =  $row['telefono'];
			$rut               =  $row['rut'];
			$fecNac            =  $row['fecNac'];
			$monto             =  $row['monto'];
			$fechaIngreso      =  $row['fechaIngreso'];
			$activo            =  $row['activo'];
			$idConexion        =  $row['idConexion'];
			$prueba            =  $row['prueba'];
			$respaldo          =  $row['respaldo'];
			$idPais            =  $row['idPais'];
			$idRegion          =  $row['idRegion'];
			$idCiudad          =  $row['idCiudad'];
			$idComuna          =  $row['idComuna'];
			$sexo              =  $row['sexo'];
			$respaldoTable     =  $row['respaldoTable'];

			# verificar que el registro no existe
			$query2  = "SELECT 1
						  FROM $tabla
						 WHERE $id = $idID;";
			$result2 = $gleads_rsp->query($query2);

			if ($result2->num_rows == 0) {
				# Insertar tabla
		        $sql = "INSERT INTO $tabla ($camposins)
				             VALUES ($idPersonas,
									$idInmobiliaria,
									$idPuente,
									$idEmail_unico,
									'$mail',
									'$nombre',
									'$apellido',
									'$comuna',
									'$telefono',
									'$rut',
									'$fecNac',
									$monto,
									'$fechaIngreso',
									$activo,
									$idConexion,
									$prueba,
									$respaldo,
									$idPais,
									$idRegion,
									$idCiudad,
									$idComuna,
									$sexo,
									$respaldoTable);";
		        if ($gleads_rsp->query($sql)==true) {
		        	$total  = $total + 1;
		        	$update = 1;
		        }else{
		        	$falla = $falla+1;
		        }
				# ------
			}else{
				$update = 1;
			}
			# ACTUALIZAR
			if ($update == 1) {
				$sql = " UPDATE $tabla
			                SET estadoRegistro = 2
			              WHERE $id            = $idID;";
				$gleads_db->query($sql);
			}
		}
    }
	# ----------------- fin
}

# =======================================================
# PASO 3: MONITOR
# =======================================================
$salida = $total;
echo "fallas:$falla<br>";
echo 'paso-PRODUCCION:['.$salida.'], RESTO:['.number_format($TotalG, 0, '', '.').']';
mysqli_close($gleads_db);
mysqli_close($gleads_rsp);
include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
?>
