<?php
class Boletin_pdf extends CI_Controller {
    public function __construct(){

        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('html');
        $this->load->library('user_agent');
        $this->load->database();
        $this->load->helper('cookie');
        session_start();
        $this->load->library('Gleads');
        $this->load->library('Gleadsventas');
        $this->load->library('Valentina');
        $this->load->library('Valentinalista');
        $this->load->library('Valentinausuarios');
        $this->load->library('pagination');
        $this->load->helper(array('form'));
        $this->load->library('form_validation');
        #############################
		####Includes del proyecto####
		#############################
        $this->load->library('tcpdf/Mitcpdf');
        if(isset($_SESSION['user'])){$session=$_SESSION['user'];}else{$session=null;}
        $this->valentinausuarios->user($session);
    }
    public function index(){
		$secret_key = '6LfNplIUAAAAAOjKYJzFevvwRS8q4sqEWBBHnG1F';
        $captcha    = $this->input->post('g-recaptcha-response');
        $response   = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
        $obj = json_decode($response);
        if($obj->success == false){
            exit;
        }
    	if(base_url() == 'https://gleads.tgapps.net/'){
			$boletin_db = new mysqli('tga-reportes.cusc3ayieifn.us-west-1.rds.amazonaws.com','glead_view','=NFHB9hV','reportes');
			if (mysqli_connect_errno()) {$this->doError(1313);exit();}
		}else{
			$boletin_db = new mysqli('tga-reportes.cusc3ayieifn.us-west-1.rds.amazonaws.com','glead_view_dev','uBx2v&Uw','reportes');
			if (mysqli_connect_errno()) {$this->doError(1313);exit();}
		}
		mysqli_set_charset($boletin_db, 'utf8');

		#############################
		####Variable de librerías####
		#############################
		$ID_INMOBILIARIA 		 = $this->valentinausuarios->empresaid($_SESSION['user']);
		$nombreInmobiliaria      = $this->valentinausuarios->empresa($_SESSION['user']);
		$nombreUsuario			 = $this->valentinausuarios->nombre($_SESSION['user']);

		##############################
		####Obtención de variables####
		##############################
		$idZona                 = $this->input->post("idZona");
		$fechaMes 		 		= $this->input->post("fechaMes");
		$fechaAnio 		 		= $this->input->post("fechaAnio");
		$fecha 					= $this->input->post("fechaCompleta");
		$svg_origen				= $this->input->post("svg_origen");
		$svg_volumen			= $this->input->post("svg_volumen");

		
		$nombreInmobiliaria		= $nombreInmobiliaria;
		$nombreUsuario			= $nombreUsuario;


		############################ 
		##########Imágenes##########
		############################
		$img_logon			= site_url() . "images/sitio/reporte_pro/boletin/pdf/logon_trans.png";
		$img_bullet_1		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (1).png";
		$img_bullet_2		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (2).png";
		$img_bullet_3		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (3).png";
		$img_bullet_4		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (4).png";
		$img_bullet_5		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (5).png";
		$img_bullet_6		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (6).png";
		$img_bullet_7		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (7).png";
		$img_cot_barra		= site_url() . "images/sitio/reporte_pro/boletin/pdf/cot_barra.gif";
		$img_cat_barra		= site_url() . "images/sitio/reporte_pro/boletin/pdf/cat_barra.gif";
		$img_fecha_anio		= site_url() . "images/sitio/reporte_pro/boletin/pdf/a";
		$img_fecha_mes		= site_url() . "images/sitio/reporte_pro/boletin/pdf/m";

		###############
		####queries####
		###############
		//PAG 1   : Resumen Mensual
		$res0 = $boletin_db->query("
									SELECT a.totalCotWeb
										 , a.totalCotWebUnico
										 , a.totalCotPresencial
										 , a.totalCotPresencialUnico
										 , a.totalCon
										 , a.totalConUnico
										 , a.totalCat
										 , a.totalCatUnico
										 , a.catOrigenWeb
										 , a.catOrigenEmailing
										 , a.catOrigenEjecutivo
										 , a.catOrigenVenta
									  FROM boletin_resumen_0 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio;
										");
		//PAG 2
		//Fuente de tráfico por PORTAL
		$res1 = $boletin_db->query("
									SELECT a.portal
										 , a.cotWebPortal
										 , a.cotWebPortalUnico
									  FROM boletin_resumen_1 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio
								  ORDER BY a.cotWebPortal DESC, a.cotWebPortalUnico DESC;
										");
		//Fuente de tráfico por CONSULTA
		$res1_con = $boletin_db->query("
										SELECT a.portal
											 , a.conPortal
											 , a.conPortalUnico
										  FROM boletin_resumen_2 a
										 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
										   AND a.idZona 		= $idZona
										   AND a.fechaMes 		= $fechaMes
										   AND a.fechaAnio 		= $fechaAnio
									  ORDER BY a.conPortal DESC, a.conPortalUnico DESC;
											");
		//PAG 3
		//Resumen de cotizaciones
		$res2 = $boletin_db->query("
									SELECT a.idProyecto, a.proyecto, a.idPortal, a.portal, a.cotResumen, a.cotResumenUnico
									  FROM boletin_resumen_3 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio
								  ORDER BY a.proyecto ASC, a.portal ASC;
										");

		//PAG 4
		//Resumen cantidades Categorizados Ejecutivos
		$res3 = $boletin_db->query("
									SELECT a.ejecutivo
										 , a.catEjecutivoPanel
										 , a.catEjecutivoFicha
									  FROM boletin_resumen_4 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio;
										");
		//PAG 5
		//Resumen gestiones
		$res4 = $boletin_db->query("
									SELECT ejecutivo
										 , cotGesUnico
										 , cotGes
										 , cotRatio
										 , cotDias
										 , catGesUnico
										 , catGes
										 , catRatio
										 , catDias
									  FROM boletin_resumen_6 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio
								  ORDER BY a.ejecutivo;
									");

		if($res0->num_rows > 0){
			$row0 = mysqli_fetch_array($res0);
			$totalCotWeb 			 = ($row0['totalCotWeb']=="")?0:$row0['totalCotWeb'];
			$totalCotWebUnico 		 = ($row0['totalCotWebUnico']=="")?0:$row0['totalCotWebUnico'];
			$totalCotPresencial 	 = ($row0['totalCotPresencial']=="")?0:$row0['totalCotPresencial'];
			$totalCotPresencialUnico = ($row0['totalCotPresencialUnico']=="")?0:$row0['totalCotPresencialUnico'];
			$totalCon 				 = ($row0['totalCon']=="")?0:$row0['totalCon'];
			$totalConUnico 			 = ($row0['totalConUnico']=="")?0:$row0['totalConUnico'];
			$totalCat 				 = ($row0['totalCat']=="")?0:$row0['totalCat'];
			$totalCatUnico 			 = ($row0['totalCatUnico']=="")?0:$row0['totalCatUnico'];
			//$catOrigenWeb 			 = ($row0['catOrigenWeb']=="")?0:$row0['catOrigenWeb'];
			//$catOrigenEmailing 		 = ($row0['catOrigenEmailing']=="")?0:$row0['catOrigenEmailing'];
			//$catOrigenEjecutivo 	 = ($row0['catOrigenEjecutivo']=="")?0:$row0['catOrigenEjecutivo'];
			//$catOrigenVenta 		 = ($row0['catOrigenVenta']=="")?0:$row0['catOrigenVenta'];
		}



		##############################
		####Generación del gráfico####
		##############################
		$Grandomico 		= rand(100000,999999999);
		$Grandomicob		= rand(1000,9999);
		$Gname				= "g_" . $Grandomico . "_" . $Grandomicob .".svg";
		$Gname2				= "g_" . $Grandomico . "_" . $Grandomicob ."_2.svg";
	
		
		if(base_url() == "https://gleads.tgapps.net/"){
			$GubicacionLocal 	   = "/var/www/vhosts/TGApps.net/gleads.tgapps.net/images/deposito/";
		}else if(base_url() == "http://gleads.desarrollo.cool/"){
			$GubicacionLocal 	   = "/usr/share/nginx/html/gleads.desarrollo.cool/web/images/deposito/";
		}else{
			$GubicacionLocal = "insertar ubicación de prueba";
		}
		
		$GubicacionFinalLocal  = $GubicacionLocal . $Gname;
		$GubicacionFinal2Local = $GubicacionLocal . $Gname2;

		$Gubicacion      	= base_url().'images/deposito/';
		$GubicacionFinal	= $Gubicacion . $Gname;
		$GubicacionFinal2	= $Gubicacion . $Gname2;


		file_put_contents($GubicacionFinalLocal, $svg_volumen);
		file_put_contents($GubicacionFinal2Local, $svg_origen);
		$e = chmod($GubicacionFinalLocal , 0775);
		$i = chmod($GubicacionFinal2Local, 0775);

		if (!$e || !$i) {
			$this->doError(3747);
			exit();
		}

		###########################
		####Creación del objeto####
		###########################
		$pageLayout = array(216, 279); //Tamaño carta
		$pdf = new Mitcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
		#############
		####Metas####
		#############
		$pdf->SetCreator("Trend Group America");
		$pdf->SetAuthor("Trend Group America");
		$pdf->SetTitle('Boletín_mensual_' . $fechaMes . ' ' .$fechaAnio);
		$pdf->SetSubject('Trend Group America');
		$pdf->SetKeywords('TGA, Gleads, boletin, pdf');




		######################################################################################################
		#################################### Cabecera y Footer ###############################################
		######################################################################################################
		$htmlHeader= '
		<table width="100%" style="border-bottom: 1px solid #ededed; color: #636262;">
			<tr style="vertical-align: middle;">
				<td width="80%" style="background-color: #c1212b;  vertical-align: middle;"><img width="117px" src="' . $img_logon. '" /></td>
				<td width="20%" style="background-color: #c1212b;"><table><tr><td><img width="60px" src="' . $img_fecha_mes . $fechaMes . '.png" /></td><td><img width="60px" src="' . $img_fecha_anio . $fechaAnio . '.png" /></td></tr></table></td>
			</tr>
			<tr>
				<td width="60%">
					&nbsp;
					<br />
					<span style="font-size: 14pt; font-weight: bold;">Boletín mensual G-Leads</span><br />
					<span>Cliente: <b>' . $nombreInmobiliaria  .'</b></span>
				</td>
				<td width="40%" style="text-align: right; vertical-align: middle;" valign="middle">
					&nbsp;
					<br />
					<span style="text-align: right;">Págs: ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages() .'</span><br/><span>Usuario: <b>' . $nombreUsuario . '</b></span>
					<br />
					&nbsp;
				</td>
			</tr>
		</table>'
		;
		$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs=$htmlHeader, $tc=array(0,0,0), $lc=array(0,0,0));
		$pdf->SetHeaderMargin(5);			//Margen del header
		$pdf->SetMargins(5, 35, 5, true);	//Margen del cuerpo IZQ ARRIBA DER
		$pdf->setFooterMargin(35);
		$pdf->SetAutoPageBreak(TRUE, 35); 









		######################################################################################################
		#################################### Página 1/5 ######################################################
		######################################################################################################
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";
		//< info pagina
		
		//Estilos en general
		$cssPdf = '
		<style>
		.subTitulo{
			font-size: 11pt;
			border-bottom: 1.1px solid #8c8c8c;
		}
		.grayTable{
			border: 1px solid #cecece;
		}
		.grayTable>tr{
			border: 1px solid #cecece;
		}
		.grayTable>tr>td{
			border: 1px solid #cecece;
		}
		.st1{ text-align: right; font-size: 10pt;}
		.st2{ border-bottom: 1px solid #ededed;}
		.st3{ color: #767a7b;}
		.st4{ border-bottom: 2px solid #7a7a7a; margin: 0; padding: 0;}
		.st5{ color: #5787b3; background-color: #e6f2fa;}
		.st6{ color: #5787b3; font-weight: bold;}
		.st7{ color: #353535;}
		.bulletes{
			width: 8px;
			height: 8px;
			margin-right: 8px;
		}
		.tab_2 td{
			border-bottom: 1px solid #d2d4d3;
		}
		.tab_4{
			font-size: 11pt;
		}
		.tab_4head{
			background-color: #edeeed;
			border-top: 1px solid #dedfde;
		}
		.tab_4 td{
			border-bottom: 1px solid #dedfde;
		}
		</style>
		';
		//$pdf->writeHTML($cssPdf, false, false, true, false, '');

		$htmlpdf .= '
		<table width="100%">
			<tr>
				<td width="45%"><div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_7 . '"> Resumen mensual</div></td>
				<td width="10%">&nbsp;</td>
				<td width="45%"><div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_4 . '"> Origen de la categorización</div></td>
			</tr>
			<tr>
				<td width="45%">
					<table width="100%" cellpadding="3"> 
						<thead>
							<tr>
								<td width="50%" >&nbsp;</td>
								<td width="25%" class=" st5" style=" font-size: 9pt; color: black;" align="center">Totales</td>
								<td width="25%" style="font-size: 9pt;" align="center">Únicos</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="st2" align="right" style="font-size: 12pt;"><br />Cotizaciones web<br /></td>
								<td class="st2  st5" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCotWeb .'</td>
								<td class="st2  st3" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCotWebUnico .'</td>
							</tr>
							<tr>
								<td class="st2" align="right" style="font-size: 12pt;"><br />Cotizaciones presenciales<br /></td>
								<td class="st2  st5" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCotPresencial .'</td>
								<td class="st2  st3" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCotPresencialUnico .'</td>
							</tr>
							<tr>
								<td class="st2" align="right" style="font-size: 12pt;"><br />Consultas<br /></td>
								<td class="st2  st5" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCon .'</td>
								<td class="st2  st3" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalConUnico .'</td>
							</tr>
							<tr>
								<td class="st2" align="right" style="font-size: 12pt;"><br />Personas categorizadas<br /></td>
								<td class="st2  st5" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCatUnico .'</td>
								<td class="st2  st3" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCatUnico .'</td>
							</tr>
						</tbody>
						
					</table>
				</td>
				<td width="10%">
					&nbsp;
				</td>
				<td width="45%">
					<img src="'  .  $GubicacionFinal2  .'" width="300px"/>
				</td>
			</tr>
		</table>
		<br /> <br />
		<table>
			<tr>
				<td style="font-size: 7pt;">
					<b>Categorización web:</b> Respuestas obtenidas de banner situado en la web de la inmobiliaria.<br />
					<b>Categorización E-mailing:</b> Respuestas obtenidas a través de la campaña E-mailing post cotización o consulta.<br />
					<b>Categorización Ejecutivo:</b> Cuestionario realizado por el ejecutivo a través de la ficha cotizante (Vía Telefónica).<br />
					<b>Categorización Ventas:</b> Cuestionario realizado por el ejecutivo a través del botón cuestionario del panel de G-Leads (Envío por email o ingreso de cliente).
				</td>
			</tr>
		</table>
		<br /> <br />
		<table width="100%">
			<tr>
				<td><div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_6 . '"> Volumen de personas obtenidas al mes y que no han sido gestionadas por proyecto mensual</div></td>
			</tr>
			<tr>
				<td>
					<img src="'  .  $GubicacionFinal  .'" width="550px"/>
				</td>
			</tr>
		</table>
		';

		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');


		#####################################################################################################
		#################################### Página 2/5######################################################
		#####################################################################################################
		if($res1->num_rows > 0){
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";
		//< info pagina

		$htmlpdf .= "
		<style>
			.titleTable{
				background-color: #f2f2f2;
				color: #666666;
				font-size: 8pt;
				text-align: center;
			}
		</style>
		";

		$htmlpdf .= '<div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_2 . '"> Fuente de tráfico</div><br />';





		$htmlpdf.= '<table width="100%" align="center" vertical-align="center">';
		$htmlpdf.= "<tr>";
		$htmlpdf.= '<td width="47%">';
		#######Portales#######
		$htmlpdf .= '<table width="100%" class="tab_2" cellpadding="5">';
		$htmlpdf .= '<thead>';
		$htmlpdf .= '<tr class="titleTable">';
		$htmlpdf .= '<td width="50%" style="border-top: 1px solid #d2d4d3;"><b>Por portal</b></td>';
		$htmlpdf .= '<td width="25%" style="border-top: 1px solid #d2d4d3;"><b>Totales</b></td>';
		$htmlpdf .= '<td width="25%" style="border-top: 1px solid #d2d4d3;"><b>Únicos</b></td>';
		$htmlpdf .= '</tr>';
		$htmlpdf .= '</thead><tbody>';

		$porTotUn = array();
		if($res1->num_rows > 0){
			while($row1 = $res1->fetch_assoc()) {
				$portal		  	   = ($row1['portal']=="")?"Sin portal":$row1['portal'];
				$cotWebPortal	   = ($row1['cotWebPortal']=="")?0:$row1['cotWebPortal'];
				$cotWebPortalUnico = ($row1['cotWebPortalUnico']=="")?0:$row1['cotWebPortalUnico'];
				array_push($porTotUn, array($portal,$cotWebPortal,$cotWebPortalUnico));
		    }
		}
		$cantidadPortales 	= count($porTotUn);

		for ($i=0; $i < $cantidadPortales; $i++) {
			$portal		  	   = ($row1['portal']=="")?"Sin portal":$row1['portal'];
			$cotWebPortal	   = ($row1['cotWebPortal']=="")?0:$row1['cotWebPortal'];
			$cotWebPortalUnico = ($row1['cotWebPortalUnico']=="")?0:$row1['cotWebPortalUnico'];

			$htmlpdf .= '<tr><td style="font-size: 10pt;">' . $porTotUn[$i][0] . '</td><td class="st6">' . $porTotUn[$i][1] . '</td><td class="st7">' . $porTotUn[$i][2] . '</td></tr>';
		}
		$htmlpdf .= '</tbody></table>';


		$htmlpdf.= "</td>";
		$htmlpdf.= '<td width="6%">';
			$htmlpdf.= "&nbsp;";
		$htmlpdf.= "</td>";
		$htmlpdf.= '<td width="47%">';


		#######Consultas#######
		$htmlpdf .= '<table width="100%" class="tab_2" align="center" cellpadding="5">';
		$htmlpdf .= '<thead>';
		$htmlpdf .= '<tr class="titleTable">';
		$htmlpdf .= '<td width="50%"><b>Por consulta</b></td>';
		$htmlpdf .= '<td width="25%"><b>Totales</b></td>';
		$htmlpdf .= '<td width="25%"><b>Únicos</b></td>';
		$htmlpdf .= '</tr>';
		$htmlpdf .= '</thead><tbody>';

		$PorTotCon = array();
		while($row1 = $res1_con->fetch_assoc()) {
			$portal		  	= ($row1['portal']=="")?"Sin portal":$row1['portal'];
			$conPortal	    = ($row1['conPortal']=="")?0:$row1['conPortal'];
			$conPortalUnico = ($row1['conPortalUnico']=="")?0:$row1['conPortalUnico'];
			array_push($PorTotCon, array($portal,$conPortal,$conPortalUnico));
		}

		$cantidadConsultas	= count($PorTotCon);

		for ($i=0; $i < $cantidadConsultas; $i++) { 
			$htmlpdf .= '<tr><td style="font-size: 10pt;">' . $PorTotCon[$i][0] . '</td><td class="st6">' . $PorTotCon[$i][1] . '</td><td class="st7">' . $PorTotCon[$i][2] . '</td></tr>';
		}
		$htmlpdf .= '</tbody></table>';



		$htmlpdf.= "</td>";
		$htmlpdf.= "</tr>";
		$htmlpdf.= "</table>";


		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');


		}
		######################################################################################################
		#################################### Página 3/5 ######################################################
		######################################################################################################
		if($res2->num_rows > 0){


		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";
		$cantidadPortales = 0;
		//< info pagina

		$htmlpdf .= '<div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_1 . '"> Resumen de cotizaciones</div>';




		$arrIdProyectos     = array();
		$arrNomProyectos 	= array();
		$arrIdPortales 		= array();
		$arrNomPortales 	= array();

		$arrCotResumen 		= array();
		$arrCotResumenUnico = array();


		if($res2->num_rows > 0){
			$proy = 0;
			$port = 0;
			while($row2 = $res2->fetch_assoc()) {
				$idProyecto		 = $row2['idProyecto'];
				$proyecto	     = $row2['proyecto'];
				$idPortal 		 = $row2['idPortal'];
				$portal		  	 = $row2['portal'];
				$cotResumen	     = $row2['cotResumen'];
				$cotResumenUnico = $row2['cotResumenUnico'];
				if (!in_array("$idProyecto", $arrIdProyectos)) {
				    $arrIdProyectos[$proy]  = "$idProyecto";
				    $arrNomProyectos[$proy] = "$proyecto";
				    $proy++;
				}
				if (!in_array("$idPortal", $arrIdPortales)) {
				    $arrIdPortales[$port]  = "$idPortal";
				    $arrNomPortales[$port] = "$portal";
				    $port++;
				}
				$arrCotResumen[$idProyecto][$idPortal] 		= "$cotResumen";
				$arrCotResumenUnico[$idProyecto][$idPortal] = "$cotResumenUnico";
		    }
		}

		$anchMax 			= 9; //Cantidad de portales por tabla a mostrar
		$vMax 				= 18;
		$paginacion			= count($arrIdPortales) / $anchMax; //Cantidad de tablas que van a haber en total
		$pagactual			= 1; //No mover. No puede quedarse en 0
		$vActual			= 1;
		$p 					= 0;
		$soloDos			= 0;

		for ($a=$paginacion; $a > 0; $a--) {
			$soloDos = 0;
			//Imprime   Tabla completa
			$htmlpdf .= '&nbsp;<br/><table style="font-size: 8pt;" cellpadding="1" width="900px" align="center">';
			$htmlpdf .= '<tr>';
			$htmlpdf .= '<td width="120px" ></td>';
			for ($i=0; $i < count($arrIdPortales); $i++) {
				//Imprime     portal_1 | portal_2 | portal_3 | portal_4
				if ($i < $anchMax && isset($arrNomPortales[$i + $p])) { //Para que no se pase más allá del ancho máximo
					$htmlpdf .= '<td colspan="2" width="50px" style="border-bottom:0.5px solid #d2d4d3; font-size: 7pt;">' . $arrNomPortales[$i + $p] . '</td>';
				}
				
			}
			$htmlpdf .= '</tr>';
			$htmlpdf .= '<tr>';
			$htmlpdf .= '<td width="120px" ></td>';
			for ($i=0; $i < count($arrIdPortales); $i++) {
				//Imprimir    color || únicos
				if ($i < $anchMax && isset($arrNomPortales[$i + $p])) { //Para que no se pase más allá del ancho máximo
					$htmlpdf .= '<td width="25px" height="12px" style="line-height:12px; border-left:1.7px solid #d2d4d3; border-bottom:1px solid #d2d4d3; font-size:7pt;">Total</td>';
					$htmlpdf .= '<td width="25px" height="12px" style="line-height:12px; border-left:0.5px solid #d2d4d3; border-bottom:1px solid #d2d4d3; font-size:7pt; color:#636363;">Únicos</td>';
				}
				
			}
			$htmlpdf .= '</tr>';

			
			for ($v=0; $v < count($arrIdProyectos); $v++) {
				//Imprime      <tr> ----> (Nombre proyecto 99 88 99 88 99 88 99 88 99 88 99)
				if ($vActual > $vMax) {
					//REPITE LA GENERACIÓN DE LA TABLA PADRE CON SUS HEADERS -----------------------------------
					$htmlpdf .= "</table>";
						//Aquí valida si es la segunda tabla que pasa. Esto provoca un salto de línea.
						$soloDos = 0;
						$htmlpdfFinal = $cssPdf . $htmlpdf;
						$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');
						$pdf->AddPage();
						$htmlpdf = "";
						$soloDos = 1;
					//Imprime   Tabla completa
					$htmlpdf .= '<table style="font-size: 8pt;" cellpadding="1" width="900px" align="center">';
					$htmlpdf .= '<tr>';
					$htmlpdf .= '<td width="120px" ></td>';
					for ($i=0; $i < count($arrIdPortales); $i++) {
						//Imprime     portal_1 | portal_2 | portal_3 | portal_4
						if ($i < $anchMax && isset($arrNomPortales[$i + $p])) { //Para que no se pase más allá del ancho máximo
							$htmlpdf .= '<td colspan="2" width="50px" style="border-bottom:0.5px solid #d2d4d3; font-size: 7pt;">' . $arrNomPortales[$i + $p] . '</td>';
						}
						
					}
					$htmlpdf .= '</tr>';
					$htmlpdf .= '<tr>';
					$htmlpdf .= '<td width="120px" ></td>';
					for ($i=0; $i < count($arrIdPortales); $i++) {
						//Imprimir    color || únicos
						if ($i < $anchMax && isset($arrNomPortales[$i + $p])) { //Para que no se pase más allá del ancho máximo
							$htmlpdf .= '<td width="25px" height="12px" style="line-height:12px; border-left:1.7px solid #d2d4d3; border-bottom:1px solid #d2d4d3; font-size:7pt;">Total</td>';
							$htmlpdf .= '<td width="25px" height="12px" style="line-height:12px; border-left:0.5px solid #d2d4d3; border-bottom:1px solid #d2d4d3; font-size:7pt; color:#636363;">Únicos</td>';
						}
						
					}

					//FIN REPITE -------------------------------------------------------------------------------
					$htmlpdf .= '</tr>';

					$vActual = 1;
				}
				$idProyectoFind = $arrIdProyectos[$v];
				if(!($v % 2)){$colorR ='#edeeed;';}else{$colorR = 'white;';}
				$htmlpdf .= '<tr style="background-color:'.$colorR.'">';
				$htmlpdf .= '<td width="120px" style="font-size:6pt;">' . $arrNomProyectos[$v] . '</td>'; //Nombreproyecto
				for ($u=0; $u < count($arrIdPortales); $u++) {
					//Imprime      tot99 || un88
					if ($u < $anchMax && isset($arrIdPortales[$u + $p])) { //Para que no se pase más allá del ancho máximo
						$idPortalFind = $arrIdPortales[$u + $p];
						$valorCotR  = ( empty($arrCotResumen[$idProyectoFind][$idPortalFind]) )?0:$arrCotResumen[$idProyectoFind][$idPortalFind];
						$valorCotRU = ( empty($arrCotResumenUnico[$idProyectoFind][$idPortalFind]) )?0:$arrCotResumenUnico[$idProyectoFind][$idPortalFind];
						$htmlpdf .= '<td width="25px" height="25px" style="border-left:1.5px solid #d2d4d3; color: black;">&nbsp;<br />' . $valorCotR . '</td>';
						$htmlpdf .= '<td width="25px" height="25px" style="border-left:0.5px solid #d2d4d3; color: black;">&nbsp;<br />' . $valorCotRU . '</td>';
					}
				}
				$vActual++;
				$htmlpdf .= '</tr>';
			}
			
			$htmlpdf .= '</table>';
			$p = ($pagactual * $anchMax); //Este será nuestro número que va a ir sumando luego de las tablas anteriores
			$pagactual++;
		}


		############## #INI R
/*		$arrPortales	 = array();
		$arrProyTotUn	 = array();



		if($res2->num_rows > 0){
			while($row2 = $res2->fetch_assoc()) {
				$idProyecto		 = $row2['idProyecto'];
				$proyecto	     = $row2['proyecto'];
				$idPortal 		 = $row2['idPortal'];
				$portal		  	 = $row2['portal'];
				$cotResumen	     = ($row2['cotResumen']=="")?0:$row2['cotResumen'];
				$cotResumenUnico = ($row2['cotResumenUnico']=="")?0:$row2['cotResumenUnico'];

				array_push($arrPortales, $portal); // array("portal_1","portal_2","portal_3",...);
				$totrUn = array();
				$totlUn = array();
				while ($row22 = $res2->fetch_assoc()) {
					if ($row22["proyecto"] == $row2["proyecto"]) {
						array_push($totrUn, $row22["cotResumen"]);
						array_push($totlUn, $row22["cotResumenUnico"]);
					}
				}
				array_push($arrProyTotUn, array($proyecto,$totrUn,$totlUn));
		    }
		}

		$cantidadProyectos	= count($totrUn);
		$cantidadPortales 	= count($arrPortales); //Cantidad
		$anchMax 			= 10; //Cantidad de portales por tabla a mostrar
		$paginacion			= $cantidadPortales / $anchMax;
		$pagactual			= 1; //No mover. No puede quedarse en 0


		for ($a=$paginacion; $a > 0; $a--) {
			$htmlpdf .= '&nbsp;<br/><table style="font-size: 8pt;" cellpadding="1" width="900px" align="center">';
			for ($i=-1; $i < $cantidadProyectos; $i++) {
					//Color gris por cada TR
					if($i > 0){if($i % 2){$htmlpdf .= '<tr style="background-color: #edeeed;">';}else{$htmlpdf .= '<tr style="background-color: white;">';}
					}else{$htmlpdf .= '<tr>';}

				if ($i == -1) {
					$htmlpdf .= '<td width="70px" ></td>';
					for ($e=0; $e < $anchMax; $e++) { 
						$htmlpdf .= '<td colspan="2" width="48px" style="border-bottom:0.5px solid #d2d4d3;">' . $arrPortales[$e] . '</td>';
					}
				} elseif ($i == 0) {
					$htmlpdf .= '<td width="70px" ></td>';
					for ($e=0; $e < $anchMax; $e++) { 
						$htmlpdf .= '<td width="24px" height="12px" style="line-height:12px; border-left:1.7px solid #d2d4d3; border-bottom:1.7px solid #d2d4d3; font-size:7pt;">Total</td>';
						$htmlpdf .= '<td width="24px" height="12px" style="line-height:12px; border-left:0.5px solid #d2d4d3; border-bottom:1.7px solid #d2d4d3; font-size:7pt; color:#636363;">Únicos</td>';
					}
				}else{
					$htmlpdf .= '<td width="70px" >' . $arrProyTotUn[$i] . '</td>';
					for ($e=0; $e < $anchMax; $e++) {
						$htmlpdf .= '<td width="24px" height="24px" style="border-left:1.5px solid #d2d4d3; color: black;">' . $arrProyTotUn[$i - 1][0][$e] . '</td>';
						$htmlpdf .= '<td width="24px" height="24px" style="border-left:0.5px solid #d2d4d3; color: black;">' . $arrProyTotUn[$i - 1][1][$e]. '</td>';
					}
				}
				$htmlpdf .= '</tr>';
			}
			$htmlpdf .= '</table>';
			$pagactual++;
		}*/
		#################### FIN R

		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');

		}
		######################################################################################################
		#################################### Página 4/5 ######################################################
		######################################################################################################
		if($res3->num_rows > 0){ //Imprime la página siempre y cuando hayan datos
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";
		//< info pagina

		$htmlpdf .= '<div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_5 . '"> Resumen cantidades Categorizados Ejecutivos</div><br/>';

		##############################
		###Declaración de variables###
		##############################
		$tagTableBig 				= '<table width="100%"><tr><td width="47%">';
		$tagTablaSub				= '<table class="tab_4"align="center" cellpadding="5"><thead><tr><td class="tab_4head" style="font-size: 8pt;">Nombre ejecutivo</td><td class="tab_4head" style="font-size: 8pt;">Cant. Cat. Ejec</td><td class="tab_4head" style="font-size: 8pt;">Cant. Cat. Ventas</td></tr></thead>';
		$vueltasMaximas 		= 20;			//Cantidad de vueltas por subTabla


		
		$htmlpdf .= $tagTableBig . $tagTablaSub; // Tabla grande + sub_table 1
		$vueltasTab1 = $vueltasMaximas;			// |  datos  |  |         |
		$vueltasTab2 = $vueltasMaximas * 2;		// |         |  |  datos  |
		$vuelta 	 = $vueltasTab2;

		
			while($row3 = $res3->fetch_assoc()) {
				if ($vuelta == $vueltasTab1) {
					//Cierro tab_1, agrego espacio en blanco bigTable y abro tab_2
							//Cierre_tab1|  tabmargin         | tab2 de 45%
					$htmlpdf .= '</table cellpadding="5"></td><td width="6%"></td><td width="47%" style="font-size: 10pt;">' . $tagTablaSub;
				}else if($vuelta == 0){
					//Cierro tab_2, bigTable y genero otro bigTable
					$htmlpdf .= '</table></table>';
					$htmlpdf .= $tagTableBig . $tagTablaSub; //Genero una nueva bigTable

					$vuelta = $vueltasTab2;
				}
				$ejecutivo		   = trim(($row3['ejecutivo']=="")?"<i>Sin ejecutivo</i>":$row3['ejecutivo']);
				$catEjecutivoPanel = trim(($row3['catEjecutivoPanel']=="")?"0":$row3['catEjecutivoPanel']);
				$catEjecutivoFicha = trim(($row3['catEjecutivoFicha']=="")?"0":$row3['catEjecutivoFicha']); 
				$htmlpdf.='<tr>';
				$htmlpdf.='<td style="font-size: 10pt;">'.$ejecutivo.'</td><td style="font-size: 16pt; color: #767a7b;">'.$catEjecutivoPanel.'</td><td style="font-size: 16pt; color: #767a7b;">'.$catEjecutivoFicha.'</td>';
				$htmlpdf.='</tr>';

				$vuelta --;
		    }
		

		$htmlpdf .= "</table></td></tr></table>";
		
		
		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		//echo $htmlpdfFinal; exit(); #BORRAR
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');
		
		}
		######################################################################################################
		#################################### Página 5/5 ######################################################
		######################################################################################################
		if($res4->num_rows > 0){
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";




		$htmlpdf .= '<div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_3 . '" /> Resumen de gestiones</div><br />';

		$htmlpdf .= '<table width="100%" align="center"  style="font-size: 10pt;" cellpadding="3">';

		$htmlpdf .= '<thead><tr>';
		$htmlpdf .= '<td width="28%"             style=" border-bottom: 0.8px solid #d2d4d3;">&nbsp;</td>';
		$htmlpdf .= '<td width="18%" colspan="2" style="font-size:7pt; border-bottom: 0.8px solid #d2d4d3;">Cantidad de gestiones</td>';
		$htmlpdf .= '<td width="18%" colspan="2" style="font-size:7pt; border-bottom: 0.8px solid #d2d4d3;">Cantidad de personas</td>';
		$htmlpdf .= '<td width="18%" colspan="2" style="font-size:7pt; border-bottom: 0.8px solid #d2d4d3;">Ratio seguimiento</td>';
		$htmlpdf .= '<td width="18%" colspan="2" style="font-size:7pt; border-bottom: 0.8px solid #d2d4d3;">Días de primer contacto</td>';
		$htmlpdf .= '</tr></thead>';

		$htmlpdf .= '<tbody>';
		$htmlpdf .= '<tr>';
		$htmlpdf .= '<td width="28%" style="font-size: 7pt;">Nombre ejecutivo</td>';
		$htmlpdf .= '<td width="9%" style="font-size:6pt;">Cotizantes<br/>   <img src="' . $img_cot_barra . '" width="52px" height="3px" /></td>';
		$htmlpdf .= '<td width="9%" style="font-size:6pt;">Categorizados<br/><img src="' . $img_cat_barra . '" width="52px" height="3px" /></td>';
		$htmlpdf .= '<td width="9%" style="font-size:6pt;">Cotizantes<br/>   <img src="' . $img_cot_barra . '" width="52px" height="3px" /></td>';
		$htmlpdf .= '<td width="9%" style="font-size:6pt;">Categorizados<br/><img src="' . $img_cat_barra . '" width="52px" height="3px" /></td>';
		$htmlpdf .= '<td width="9%" style="font-size:6pt;">Cotizantes<br/>   <img src="' . $img_cot_barra . '" width="52px" height="3px" /></td>';
		$htmlpdf .= '<td width="9%" style="font-size:6pt;">Categorizados<br/><img src="' . $img_cat_barra . '" width="52px" height="3px" /></td>';
		$htmlpdf .= '<td width="9%" style="font-size:6pt;">Cotizantes<br/>   <img src="' . $img_cot_barra . '" width="52px" height="3px" /></td>';
		$htmlpdf .= '<td width="9%" style="font-size:6pt;">Categorizados<br/><img src="' . $img_cat_barra . '" width="52px" height="3px" /></td>';
		$htmlpdf .= "</tr>";

		//< info pagina
		$cotGesUnicoTotal = 0;
		$cotGesTotal 	  = 0;
		$cotRatioTotal 	  = 0;
		$cotDiasTotal 	  = 0;
		$catGesUnicoTotal = 0;
		$catGesTotal 	  = 0;
		$catRatioTotal 	  = 0;
		$catDiasTotal 	  = 0;
		$cantCatDias 	  = 0;
		$cantCotDias 	  = 0;
		$colorPar = 1;
		while($row4 = $res4->fetch_assoc()) {
			$ejecutivo 	 = ($row4['ejecutivo']=="")?"<i>No encontrado</i>":$row4['ejecutivo'];
			$cotGesUnico = ($row4['cotGesUnico']=="")?0:$row4['cotGesUnico'];
			$cotGes 	 = ($row4['cotGes']=="")?0:$row4['cotGes'];
			$cotRatio 	 = ($row4['cotRatio']=="")?0:$row4['cotRatio'];
			$cotDias 	 = ($row4['cotDias']=="")?0:$row4['cotDias'];
			$catGesUnico = ($row4['catGesUnico']=="")?0:$row4['catGesUnico'];
			$catGes 	 = ($row4['catGes']=="")?0:$row4['catGes'];
			$catRatio 	 = ($row4['catRatio']=="")?0:$row4['catRatio'];
			$catDias 	 = ($row4['catDias']=="")?0:$row4['catDias'];

			if($catDias!=0){$cantCatDias++;}
			if($cotDias!=0){$cantCotDias++;}

			$cotGesUnicoTotal += $cotGesUnico;
			$cotGesTotal 	  += $cotGes;
			$cotRatioTotal 	  += $cotRatio;
			$cotDiasTotal 	  += $cotDias;
			$catGesUnicoTotal += $catGesUnico;
			$catGesTotal 	  += $catGes;
			$catRatioTotal 	  += $catRatio;
			$catDiasTotal 	  += $catDias;


			if($colorPar % 2){	$clasex = '#edeeed'; }else{	$clasex = 'white';	}
			$htmlpdf .= '<tr style="background-color: '. $clasex .';">';
			$htmlpdf .= '<td width="28%" height="13px" style="font-size: 8pt;">'. $ejecutivo .'</td>';
			$htmlpdf .= '<td width="9%" height="13px" style="border-left:1.8px solid #d2d4d3; font-size: 7pt;">' . $cotGes . '</td>'; # Se cambio de posición estaba mal
			$htmlpdf .= '<td width="9%" height="13px" style="border-left:1px   solid #d2d4d3; font-size: 7pt;">' . $catGes . '</td>'; # Se cambio de posición estaba mal
			$htmlpdf .= '<td width="9%" height="13px" style="border-left:1.8px solid #d2d4d3; font-size: 7pt;">' . $cotGesUnico . '</td>'; # Se cambio de posición estaba mal
			$htmlpdf .= '<td width="9%" height="13px" style="border-left:1px   solid #d2d4d3; font-size: 7pt;">' . $catGesUnico . '</td>'; # Se cambio de posición estaba mal
			$htmlpdf .= '<td width="9%" height="13px" style="border-left:1.8px solid #d2d4d3; font-size: 7pt;">' . $cotRatio . '</td>'; # Se cambio de posición estaba mal
			$htmlpdf .= '<td width="9%" height="13px" style="border-left:1px   solid #d2d4d3; font-size: 7pt;">' . $catRatio . '</td>'; # Se cambio de posición estaba mal
			$htmlpdf .= '<td width="9%" height="13px" style="border-left:1.8px solid #d2d4d3; font-size: 7pt;">' . $cotDias . '</td>'; # Se cambio de posición estaba mal
			$htmlpdf .= '<td width="9%" height="13px" style="border-left:1px   solid #d2d4d3; font-size: 7pt;">' . $catDias . '</td>'; # Se cambio de posición estaba mal
			$htmlpdf .= "</tr>";
			$colorPar++;
		}

		$cotRatioTotal = ($cotGesUnicoTotal>0)?round(($cotGesTotal/$cotGesUnicoTotal),1):0;
		$catRatioTotal = ($catGesUnicoTotal>0)?round(($catGesTotal/$catGesUnicoTotal),1):0;
		$cotDiasTotal  = ($cantCotDias>0)?round(($cotDiasTotal/$cantCotDias),1):0;
		$catDiasTotal  = ($cantCatDias>0)?round(($catDiasTotal/$cantCatDias),1):0;

		$htmlpdf .= '<tr style="background-color: #d2eae1; color: #444444;" cellpadding="4">';
		$htmlpdf .= '<td>Total</td>';
		$htmlpdf .= '<td>' . $cotGesTotal . '</td>'; # Se cambio de posición estaba mal
		$htmlpdf .= '<td>' . $catGesTotal . '</td>'; # Se cambio de posición estaba mal
		$htmlpdf .= '<td>' . $cotGesUnicoTotal . '</td>'; # Se cambio de posición estaba mal
		$htmlpdf .= '<td>' . $catGesUnicoTotal . '</td>'; # Se cambio de posición estaba mal
		$htmlpdf .= '<td>' . $cotRatioTotal . '</td>'; # Se cambio de posición estaba mal
		$htmlpdf .= '<td>' . $catRatioTotal . '</td>'; # Se cambio de posición estaba mal
		$htmlpdf .= '<td>' . $cotDiasTotal . '</td>'; # Se cambio de posición estaba mal
		$htmlpdf .= '<td>' . $catDiasTotal . '</td>'; # Se cambio de posición estaba mal
		$htmlpdf .= '</tr>';

		$htmlpdf .= '</tbody></table>';

		/*
		$pdf->SetY(-40);
		$pdf->setFooterMargin(40);
		$pdf->SetAutoPageBreak(TRUE, 40); 
		*/

		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');
		}
		################################################################################
		#################################### CIERRE ####################################
		################################################################################
		#mysqli_close($boletin_db);

		$guardarComo		= "Boletin_mensual_" . $fechaAnio . "-" . $fechaMes . ".pdf";
		$pdf->Output($guardarComo, 'I');


		unlink($GubicacionFinal2Local);
		unlink($GubicacionFinalLocal);

		mysqli_close($boletin_db);
		
    }

    public function doError($nro){
    	/*$nro = "";*/ $head = ""; $body = ""; $foot = "";
    	switch ($nro) {
    		case 3747:
    			$head = "Se produjo un error."; $body = "Lo sentimos, pero se produjo un error al intentar <b>exportar lo gráficos.</b>"; $foot = "";
    			break;
    		case 1313:
    			$head = "Error de conexión."; $body = "Lo sentimos, pero parece que hubo un error de conexión. <br /> Asegúrese de: <ul><li>Estar conectado a internet</li><li>Tener una conexión estable.</li><li>No haber cerrado sesión en otra pestaña o haber permanecido en 'stand by' por un tiempo prolongado.</li></ul>"; $foot = "<b>Sugerencia: </b>Cierre y vuelva a iniciar sesión de nuevo.";
    			break;
    		default:
    			$head = "Se produjo un error en la generación del documento."; $body = "Lo sentimos, pero se produjo un error desconocido."; $foot = print_r(error_get_last()); $nro= "0";
    			break;
    	}
    	echo "<div style='background-color: #c42727; color: white; padding: 15px; border: 1px solid black; font-family: roboto, arial;'><h3>Error#" .  $nro . " - " . $head . "</h3>" . "<div>" . $body . "<br /></div>" . "<div style='background-color: white !IMPORTANT; border: 1px solid gray; color: black; padding: 8px;;'>"  . $foot . "</div></div>";
    }

}
?>
