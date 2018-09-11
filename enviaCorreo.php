<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
#########################Conexión a gleads.#####################################
include('/var/www/php/crones/conecta/web/conexion_gleads.php');
#########################Conexión a respaldo.###################################
//
$dominio    =   'https://gleads.desarrollo.cool/';
$ambiente   =   $dominio.'gleads/Landing/cambioPass/';
//$ambiente = 'https://crm.g-leads.com/entrada';
require_once 'src/Mandrill.php';
if (mysqli_connect_errno()) {echo 'fallo gleads_db';exit();}
################################################################################
$apiClave = 'st1Hj4DLJK9LlvAOKL6YZw';
// CONSULTA PARA SACAR LAS ZONAS HORARIAS
$query = "SELECT zonaHoraria
					FROM app_user_password
			WHERE estadoEnvio = 0
				AND estado = 0
				GROUP BY zonaHoraria;";
$result = $gleads_db->query($query);
$salida = 0;
if ($result->num_rows > 0) {
    while($row = $result->fetch_array()) {
    	// PROCESO PARA SACAR EL DÍA DE HOY POR ZONA HORARIA
	    $zonaHoraria 		= $row["zonaHoraria"];
	    date_default_timezone_set($zonaHoraria);	
	    $hoy                = date("Y-m-d");
	    //CONSULTA PARA SACAR EL MAIL Y EL MD5
    	$query2 = " SELECT a.mailSolicita,a.idUserPass,a.envios,
    						b.Nombre
                    FROM app_user_password a 
						  LEFT JOIN app_user b
						   ON a.mailSolicita = b.email
                    WHERE  a.estado = 0
                    	   AND DATE_FORMAT(a.fecha,'%Y-%m-%d') = '$hoy'
                    	   AND a.estadoEnvio = 0
                    LIMIT 20";
				$result2 = $gleads_db->query($query2);
                $salida = $result2->num_rows;
    			while($row2 = $result2->fetch_array()) {
				    $mailSolicita 		= $row2["mailSolicita"];
				    $idUserPass 		= $row2["idUserPass"];
				    $nombre 			= $row2["Nombre"];
				    $envios 			= $row2["envios"] + 1;
				    $link               = $ambiente.''.md5(sha1($idUserPass.'godofwar')).'/'.$idUserPass;
                    $fuente             = "'Open Sans', sans-serif;";
				    try {
					    $mandrill = new Mandrill($apiClave);
                       $htmlFinal = '<!DOCTYPE html>
                                                    <html>
                                                    <head>
                                                        <meta charset="utf-8">
                                                        <style type="text/css" media="screen">
                                                            @import url("https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700");
                                                            <html, body{
                                                                margin: 0;
                                                            }
                                                            *{
                                                                border-spacing: 0;
                                                            }
                                                            b {font-weight: 500;
                                                            }
                                                            li {list-style:none;
                                                            }
                                                            ul {padding: 0;
                                                            }
                                                        </style>
                                                    </head>
                                                    <body>
                                                    <table align="center">
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <table width="600" height="40" border="0" cellspacing="0" cellpadding="0" style="background:#be191e;padding: 10px 0; font-family:$fuente">
                                                                        <tr> 
                                                                            <td width="50">&nbsp;</td>
                                                                            <td width="200" align="justify">
                                                                                <img src="https://s3-us-west-1.amazonaws.com/tgaspa/G-Leads/Correo/logotipo_gleads.png" width="130" alt="">
                                                                            </td>
                                                                            <td width="300" align="right"></td>
                                                                            <td width="50"></td>
                                                                        </tr>                 
                                                                    </table>
                                                                    <table width="600" border="0" cellspacing="0" cellpadding="0" >
                                                                        <tr>
                                                                            <td>
                                                                                &nbsp;
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="50">&nbsp;</td>
                                                                            <td width="500" align="center">
                                                                                <img src="https://s3-us-west-1.amazonaws.com/tgaspa/G-Leads/Correo/luke.png" width="150" alt="">
                                                                            </td>
                                                                            <td width="50">&nbsp;</td>
                                                                        </tr>
                                                                    </table>
                                                                        <table style="">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td style="padding: 0;">
                                                                                        <table width="600" border="0" cellspacing="0" cellpadding="0">
                                                                                            <br>
                                                                                            <td width="50">&nbsp;</td>
                                                                                            <td width="500" align="left" style="font-size:13px;line-height:18px;font-weight:300;color:black;font-family:helvetica neue,helvetica,arial,sans-serif;">
                                                                                                <p>Hola '.$nombre.',</p>
                                                                                                <p>Acabamos de recibir una solicitud para restablecer tu contrase&ntilde;a.</p>

                                                                                                <p>Para realizarla, haz clic  <a href="'.$link.'"  style="color:#1b5aa1; text-decoration: none;">aqu&iacute;</a> o copia y pega el siguiente enlace en la  barra de tu navegador: '.$link.'</p>

                                                                                                <p>En caso de que no hayas sido t&uacute;, quien solicit&oacute; este cambio, no tomes en cuenta este correo. Si vuelve a ocurrir, notif&iacute;canos por favor a trav&eacute;s del chat de soporte.</p>
                                                                                            </td>
                                                                                            <td width="50">&nbsp;</td>
                                                                                            <tr>
                                                                                                <td>
                                                                                                    &nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>
                                                                                                    &nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                        <table width="600" border="0" cellspacing="0" cellpadding="0">
                                                                                            <tr>
                                                                                                <td width="50">&nbsp;</td>
                                                                                                <td width="500" align="left" style="font-family:helvetica neue,helvetica,arial,sans-serif;font-size:13px;font-weight:300;">
                                                                                                    <p style="margin: 0 auto; color:#000000">Saludos cordiales,</p>

                                                                                                    <p style="font-weight: 500; color: #be191e; margin: 0 auto;">Equipo G-Leads</p>
                                                                                                </td>
                                                                                                <td width="50">&nbsp;</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>
                                                                                                    &nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>
                                                                                                    &nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>
                                                                                                    &nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                        <table width="600" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 10px;">
                                                                                            <td width="50">&nbsp;</td>
                                                                                            <td width="500" align="center" style="font-size:11px;line-height:15px;font-weight:300;color:#999999;font-family:helvetica neue,helvetica,arial,sans-serif;">
                                                                                                <p style="margin: 0 auto;">Este correo es s&oacute;lo informativo, favor no responder a esta cuenta ya que no se encuentra activa para recibir mensajes. El link es temporal y estar&aacute; vigente s&oacute;lo hasta hoy a las 23:59 horas.</p>
                                                                                            <br>
                                                                                            </td>
                                                                                            <td width="50">&nbsp;</td>
                                                                                        </table>
                                                                                        <table width="600" height="43" border="0" cellspacing="0" cellpadding="0" style="background:#666666;padding:10px 0; media:screen;">
                                                                                            <tr> 
                                                                                                <td width="50">&nbsp;</td>
                                                                                                <td width="160" align="justify">
                                                                                                    <img src="https://s3-us-west-1.amazonaws.com/tgaspa/Logos/i_1/logo_trend_blanco.png" width="170" alt="">
                                                                                                </td>
                                                                                                <td width="340" align="right">
                                                                                                    <span style="font-family:helvetica neue,helvetica,arial,sans-serif;color:#ffffff;font-size:10px;float:right;font-weight:500;">&iexcl;Especialistas en conocimiento del consumidor inmobiliario!</span>
                                                                                                </td>
                                                                                                <td width="50"></td>
                                                                                            </tr>
                                                                                        </table>  
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </td>
                                                                <tr>
                                                                    <td>
                                                                        &nbsp;
                                                                    </td>
                                                                </tr>
                                                            </tr>    
                                                        </tbody>
                                                    </table>
                                                    </body>
                                                    </html>';
                        $message = array(
                            'html' => mb_convert_encoding($htmlFinal, 'UTF-8', 'ASCII'),
                            //contenido
                            'subject' => 'Creación o recuperación de contraseña',
                            'from_email' => 'noreply@g-leads.com',
                            'from_name' => 'G-Leads',
                            'to' => array(array('email'=> strtolower(ltrim($mailSolicita)))),
                            'important' => false,
                            'track_opens' => null,
                            'track_clicks' => null,
                            'url_strip_qs' => null,
                            'preserve_recipients' => null,
                            'view_content_link' => null,
                            //'bcc_address' => 'message.bcc_address@example.com',
                            'tracking_domain' => null,
                            'signing_domain' => null,
                            'return_path_domain' => null,
                            'tags' => array('envio_pass'),
                            'merge' => true,
                            'merge_language' => 'mailchimp'
                        );
                        $async      	= false;
                        $ip_pool    	= 'Main Pool';
                        $resultMand     = $mandrill->messages->send($message, $async, $ip_pool, $hoy);
					    if ($resultMand[0]['status'] == 'sent') {
					    	$gleads_db->query("UPDATE app_user_password 
                                                    SET estadoEnvio = 1,
                                                        envios      = $envios
                                                    WHERE 
                                                        mailSolicita    = '".$mailSolicita."'
                                                        AND DATE_FORMAT(fecha,'%Y-%m-%d') = '$hoy'
                                                        AND estadoEnvio = 0
                                                        LIMIT 1;");
					    }
					} catch(Mandrill_Error $e) {
					    // Mandrill errors are thrown as exceptions
					    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
					    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
					    throw $e;
					}

    			}

	}
}
echo "[insert:$salida]<br>";
$totalGeneral = $salida;
// ------------------------------------------------------
//  monitor
// ------------------------------------------------------

// monitoreo crones
$idCron = 218;
$total = $totalGeneral;
mysqli_close($gleads_db);
include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
 ?>