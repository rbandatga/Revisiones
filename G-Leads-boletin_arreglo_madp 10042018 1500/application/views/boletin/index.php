<?php
if(base_url() == 'https://gleads.tgapps.net/'){
	$boletin_db  =  new mysqli('tga-reportes.cusc3ayieifn.us-west-1.rds.amazonaws.com','glead_view','=NFHB9hV','reportes');
	if (mysqli_connect_errno()) {echo 'fallo boletin';exit();}
}else{
	$boletin_db  =  new mysqli('tga-reportes.cusc3ayieifn.us-west-1.rds.amazonaws.com','glead_view_dev','uBx2v&Uw','reportes');
	if (mysqli_connect_errno()) {echo 'fallo boletin';exit();}
}

$arrFecha = array();

$arrFecha[1]  = "ENERO";
$arrFecha[2]  = "FEBRERO";
$arrFecha[3]  = "MARZO";
$arrFecha[4]  = "ABRIL";
$arrFecha[5]  = "MAYO";
$arrFecha[6]  = "JUNIO";
$arrFecha[7]  = "JULIO";
$arrFecha[8]  = "AGOSTO";
$arrFecha[9]  = "SEPTIEMBRE";
$arrFecha[10] = "OCTUBRE";
$arrFecha[11] = "NOVIEMBRE";
$arrFecha[12] = "DICIEMBRE";

$arrTraficoCotizacion = array();
$arrTraficoConsulta   = array();
$arrResumenGest		  = array();

$ID_INMOBILIARIA 		 = $this->valentinausuarios->empresaid($_SESSION['user']);
$nombreUsuario			 = $this->valentinausuarios->nombre($_SESSION['user']);
$nombreInmobiliaria      = $this->valentinausuarios->empresa($_SESSION['user']);

$idZona                  = ($this->input->get("zona")=="")?1:$this->input->get("zona");
$fechaMes 		 		 = ($this->input->get("mes")=="")?3:$this->input->get("mes");
$fechaAnio 		 		 = ($this->input->get("anio")=="")?2018:$this->input->get("anio");
if($ID_INMOBILIARIA==96 && $idZona==1){$idZona=2;}
$fechaCompleta 			 = $arrFecha[$fechaMes]." ".$fechaAnio;


$totalCotWeb 			 = 0;
$totalCotWebUnico 		 = 0;
$totalCotPresencial 	 = 0;
$totalCotPresencialUnico = 0;
$totalCon 				 = 0;
$totalConUnico 			 = 0;
$totalCat 				 = 0;
$totalCatUnico 			 = 0;
$catOrigenWeb 			 = 0;
$catOrigenEmailing 		 = 0;
$catOrigenEjecutivo 	 = 0;
$catOrigenVenta 		 = 0;

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
						   AND a.idZona = $idZona
						   AND a.fechaMes = $fechaMes
						   AND a.fechaAnio = $fechaAnio;
							");
if($res0->num_rows > 0){
	$row0 = mysqli_fetch_array($res0);
	$totalCotWeb 			 = $row0['totalCotWeb'];
	$totalCotWebUnico 		 = $row0['totalCotWebUnico'];
	$totalCotPresencial 	 = $row0['totalCotPresencial'];
	$totalCotPresencialUnico = $row0['totalCotPresencialUnico'];
	$totalCon 				 = $row0['totalCon'];
	$totalConUnico 			 = $row0['totalConUnico'];
	$totalCat 				 = $row0['totalCat'];
	$totalCatUnico 			 = $row0['totalCatUnico'];
	$catOrigenWeb 			 = $row0['catOrigenWeb'];
	$catOrigenEmailing 		 = $row0['catOrigenEmailing'];
	$catOrigenEjecutivo 	 = $row0['catOrigenEjecutivo'];
	$catOrigenVenta 		 = $row0['catOrigenVenta'];
}

$catOrigenWeb 		= 0;
$catOrigenEmailing 	= 0;
$catOrigenEjecutivo = 0;
$catOrigenVenta 	= 0;

$res5 = $boletin_db->query("
						SELECT catOrigenWeb,catOrigenEmailing,catOrigenEjecutivo,catOrigenVenta,totalCatUnico AS totalCatOrigenUnico
						  FROM boletin_resumen_0 a
						 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
						   AND a.idZona 		= $idZona
						   AND a.fechaMes 		= $fechaMes
						   AND a.fechaAnio 		= $fechaAnio;
							");
	if($res5->num_rows > 0){
		while($row5 = $res5->fetch_assoc()) {
			$totalCatOrigenUnico= $row5['totalCatOrigenUnico'];
			$catOrigenWeb		= round((($row5['catOrigenWeb']*100)/$totalCatOrigenUnico),0);
			$catOrigenEmailing	= round((($row5['catOrigenEmailing']*100)/$totalCatOrigenUnico),0);
			$catOrigenEjecutivo	= round((($row5['catOrigenEjecutivo']*100)/$totalCatOrigenUnico),0);
			$catOrigenVenta		= round((($row5['catOrigenVenta']*100)/$totalCatOrigenUnico),0);
	    }
	}

$proyCateg		  = "";
$cotGestCateg	  = "";
$cotNoGestCateg	  = "";
$proyCateg		  = "";

$procentCotGest   = 0;
$procentCotNoGest = 0;

$res6 = $boletin_db->query("
						SELECT idProyecto,proyecto,cotUnicos,cotUnicosGest,cotUnicosNoGest
						  FROM boletin_resumen_5 a
						 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
						   AND a.idZona 		= $idZona
						   AND a.fechaMes 		= $fechaMes
						   AND a.fechaAnio 		= $fechaAnio;
							");
	if($res6->num_rows > 0){
		$comma = "";
		$porU   = 0;
		$porUG  = 0;
		$porUNG = 0;
		while($row6 = $res6->fetch_assoc()) {

			$idProyecto       = $row6['idProyecto'];
			$proyecto         = $row6['proyecto'];


			$cotUnicos        = ($row6['cotUnicos']=="")?0:$row6['cotUnicos'];
			$cotUnicosGest    = ($row6['cotUnicosGest']=="")?0:$row6['cotUnicosGest'];
			$cotUnicosNoGest  = ($row6['cotUnicosNoGest']=="")?0:$row6['cotUnicosNoGest'];

			$proyCateg 		  = $proyCateg.$comma."'$proyecto'";
			$cotGestCateg     = $cotGestCateg.$comma."$cotUnicosGest";
			$cotNoGestCateg   = $cotNoGestCateg.$comma."$cotUnicosNoGest";
			$comma 			  = ",";

			$porU   = $porU   + $cotUnicos;
			$porUG  = $porUG  + $cotUnicosGest;
			$porUNG = $porUNG + $cotUnicosNoGest;

	    }
	    $procentCotGest   = round(($porUG/$porU)*100);
	    $procentCotNoGest = round(($porUNG/$porU)*100);
	}

?>
<!--HEADER-->
<header>
	<div class="contenedor">
		<img class="logotipo_gleads" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/logotipo_gleads.png">
		<div class="panel_reporte">
			<div class="btn_reporte" onclick="volver();">
				<img class="icono_volver" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/ico_back.svg">
				<p class="panel_boletin">Panel de reporte</p>
			</div>
			<img class="barra" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/barra.png">
			<img class="icono_off" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/ico_off.svg" onclick="salir();">
		</div>
	</div>
</header>
<!--FIN HEADER-->

<!--ENCABEZADO-->
<section class="encabezado">
	<div class="contenedor">
		<div class="contenedor_titulo">
			<p class="titulo_boletin">Boletín mensual G- Leads</p>
		</div>
		<div class="contenedor_cliente">
			<p class="cliente">Cliente: <b><?php echo $nombreInmobiliaria; ?></b>
			<img class="icono_cliente" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/ico_inmob.svg"></p>
			<p class="cliente">Usuario: <b><?php echo $nombreUsuario; ?></b>
			<img class="icono_usuario" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/ico_user.svg"></p>
		</div>
	</div>
</section>
<!--FIN ENCABEZADO-->

<!--FILTRAR POR-->
<section class="select_filtros">
	<div class="contenedor">
	   <div class="contenedor_filtrar">
		    <div class="texto_filtrar">FILTRAR POR</div>
            <div class="selectClass">
                <select id="filtro_anio" data-width="100%">
                    <option value="2018">2018</option>
                </select>
            </div>
			<div class="selectClass">
                <select id="filtro_mes" data-width="100%">
                    <option value="3" selected >Marzo</option>
                </select>
            </div>
            <?php if($ID_INMOBILIARIA == 96){?>
        	<div class="selectClass selectClass3">
				<select id="filtro_zona" data-width="100%">
					<option value="2">Zona Norte</option>
					<option value="3">Zona Centro Norte</option>
					<option value="4">Zona Centro</option>
					<option value="5">Zona Sur</option>
					<option value="6">Zona Sur Austral</option>
				</select>
            </div>
            <?php } ?>
			<div class="btn_filtrar" onclick="filtrar();">
			   	<p class="descargar">FILTRAR</p>
			</div>
			<div>
				<form id="frm_download_pdf" action="<?= base_url();?>boletin_mes/boletin_pdf" method="post" accept-charset="utf-8" target="_blank">
					<input type="hidden" name="idZona" 		  value="<?= $idZona; ?>">
					<input type="hidden" name="fechaMes" 	  value="<?= $fechaMes; ?>">
					<input type="hidden" name="fechaAnio" 	  value="<?= $fechaAnio; ?>">
					<input type="hidden" name="fechaCompleta" value="<?= $fechaCompleta; ?>">
					<input type="hidden" name="svg_origen" 	  value="" id="svg_origen">
					<input type="hidden" name="svg_volumen"   value="" id="svg_volumen">
				</form>
			</div>
		    <div class="btn_descargar" onclick="generaPDF();">
		        <img class="icono_descargar" src="<?= base_url(); ?>/images/sitio/reporte_pro/boletin/icono_descargar.png">
		        <p class="descargar">DESCARGAR</p>
		    </div>
		 </div>
	 </div>
</section>
<!--FIN FILTRAR POR-->

<!--RESUMEN MENSUAL-->
<section class="resumen_mensual">
	<div class="contenedor">
	    <!--COTIZACIONES WEB-->
		<div class="resumen">
			<p class="nombre_resumen">Cotizaciones web</p>
			<div class="caja_totales">
				<p class="nombre_totales">Totales</p>
				<p class="totales"><?php echo $totalCotWeb; ?></p>
			</div>
			<div class="caja_unicos">
				<p class="nombre_unicos">Únicos</p>
				<p class="unicos"><?php echo $totalCotWebUnico; ?></p>
			</div>
		</div>
		<!--FIN COTIZACIONES WEB-->

		<!--COTIZACIONES PRESENCIALES-->
		<div class="resumen">
			<p class="nombre_resumen">Cotizaciones presenciales</p>
			<div class="caja_totales">
				<p class="nombre_totales">Totales</p>
				<p class="totales"><?php echo $totalCotPresencial; ?></p>
			</div>
			<div class="caja_unicos">
				<p class="nombre_unicos">Únicos</p>
				<p class="unicos"><?php echo $totalCotPresencialUnico; ?></p>
			</div>
		</div>
		<!--FIN COTIZACIONES PRESENCIALES-->

		<!--CONSULTAS-->
		<div class="resumen">
			<p class="nombre_resumen">Consultas</p>
			<div class="caja_totales">
				<p class="nombre_totales">Totales</p>
				<p class="totales"><?php echo $totalCon; ?></p>
			</div>
			<div class="caja_unicos">
				<p class="nombre_unicos">Únicos</p>
				<p class="unicos"><?php echo $totalConUnico; ?></p>
			</div>
		</div>
		<!--FIN CONSULTAS-->

		<!--PERSONAS CATEGORIZADAS-->
		<div class="resumen">
			<p class="nombre_resumen">Personas categorizadas</p>
			<div class="caja_totales">
				<p class="nombre_totales">Totales</p>
				<p class="totales"><?php echo $totalCatUnico; ?></p>
			</div>
			<div class="caja_unicos">
				<p class="nombre_unicos">Únicos</p>
				<p class="unicos"><?php echo $totalCatUnico; ?></p>
			</div>
		</div>
		<!--FIN PERSONAS CATEGORIZADAS-->
	</div>
</section>
<!--FIN RESUMEN MENSUAL-->

<!--CONTENIDO CENTRAL-->
<section class="contenido_central">
	<!--FUENTE DE TRAFICO-->
	<section class="fuente_trafico">
		<div class="subtitulo">
		    <img class="icono_fuente" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/icono_fuente.png">
			<p class="nombre_section">Fuente de tráfico</p>
		</div>
		<!--POR PORTAL-->
		<div class="caja_1">
			<div class="caja_1A">
				<div class="table_responsive_1AB">
					<table class="table_1A">
						<thead>
							<tr>
								<td class="fondo_titulo_lista"><p class="titulo_lista">Cotizaciones por portal</p></td>
								<th class="fondo_titulo_lista">Totales</th>
								<th class="fondo_titulo_lista">Únicos</th>
							</tr>
						</thead>
							<?php
							$res1 = $boletin_db->query("
													SELECT a.portal
														 , a.cotWebPortal
														 , a.cotWebPortalUnico
													  FROM boletin_resumen_1 a
													 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
													   AND a.idZona 		= $idZona
													   AND a.fechaMes 		= $fechaMes
													   AND a.fechaAnio 		= $fechaAnio
												  ORDER BY a.cotWebPortal DESC,a.cotWebPortalUnico DESC;
														");
								if($res1->num_rows > 0){
									$tcot = 0;
									while($row1 = $res1->fetch_assoc()) {
										$portal		  	   = $row1['portal'];
										$cotWebPortal	   = $row1['cotWebPortal'];
										$cotWebPortalUnico = $row1['cotWebPortalUnico'];
										$arrTraficoCotizacion[$portal] = array($cotWebPortal,$cotWebPortalUnico);
										?>
										<tr>
											<td class="fondo_portal"><p class="nombre_lista"><?php echo $portal; ?></p></td>
											<td class="fondo_portal"><p class="resul_totales"><?php echo $cotWebPortal; ?></p></td>
											<td class="fondo_portal"><p class="resul_unicos"><?php echo $cotWebPortalUnico; ?></p></td>
										</tr>
										<?php
										$tcot++;
								    }
								}
							?>
					</table>
				</div>
			</div> <!-- END caja_1A-->
			<div class="caja_1B">
				<div class="table_responsive_1AB">
					<table class="table_1B">
						<thead>
							<tr>
								<td class="fondo_titulo_lista"><p class="titulo_lista">Consultas por portal</p></td>
								<th class="fondo_titulo_lista">Totales</th>
								<th class="fondo_titulo_lista">Únicos</th>
							</tr>
						</thead>
						<?php
						$res1 = $boletin_db->query("
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
							if($res1->num_rows > 0){
								while($row1 = $res1->fetch_assoc()) {
									$portal		  	= $row1['portal'];
									$conPortal	    = $row1['conPortal'];
									$conPortalUnico = $row1['conPortalUnico'];
									$arrTraficoConsulta[$portal] = array($conPortal,$conPortalUnico);
									?>
									<tr>
										<td class="fondo_consulta"><p class="nombre_lista"><?php echo $portal; ?></p></td>
										<td class="fondo_consulta"><p class="resul_totales"><?php echo $conPortal; ?></p></td>
										<td class="fondo_consulta"><p class="resul_unicos"><?php echo $conPortalUnico; ?></p></td>
									</tr>
									<?php
							    }
							}
						?>
					</table>
				</div>
			</div> <!-- END table_1B-->
		</div><!--FIN POR CONSULTA-->
	</section>
	<!--FUENTE DE TRAFICO-->

	<!--RESUMEN DE COTIZACIONES-->
	<section class="cotizaciones">
		<div class="subtitulo">
		    <img class="icono_cotizaciones" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/icono_cotizaciones.png">
			<p class="nombre_section">Resumen de cotizaciones </p>
		</div>
		<div class="caja_2">
			<div class="caja_cotizacion">
				<div class="table_responsive">
					<table class="tabla_cotizacion ">
						<?php

							$arrIdProyectos     = array();
							$arrNomProyectos 	= array();
							$arrIdPortales 		= array();
							$arrNomPortales 	= array();

							$arrCotResumen 		= array();
							$arrCotResumenUnico = array();

							$res2 = $boletin_db->query("
													SELECT a.idProyecto, a.proyecto, a.idPortal, a.portal, a.cotResumen, a.cotResumenUnico
													  FROM boletin_resumen_3 a
													 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
													   AND a.idZona 		= $idZona
													   AND a.fechaMes 		= $fechaMes
													   AND a.fechaAnio 		= $fechaAnio
												  ORDER BY a.proyecto ASC,a.portal ASC;
														");
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
						?>
						<tr>
							<td class="items" colspan="1"></td>
							<?php
								for ($i=0; $i < count($arrIdPortales); $i++) {
									?>
										<td class="nombre_items" colspan="2"><?php echo $arrNomPortales[$i]; ?></td>
									<?php
								}
							?>
						</tr>
						<tr>
							<td class="categoria" colspan="1"></td>
							<?php
								for ($i=0; $i < count($arrIdPortales); $i++) {
									?>
										<td class="categoria_t" colspan="1">Total</td>
										<td class="categoria_u" colspan="1">Únicos</td>
									<?php
								}
							?>
						</tr>
						<tr class="fila_gris">
							<?php
								$c0 = '0';
								for ($i=0; $i < count($arrIdProyectos); $i++) {
									$idProyectoFind = $arrIdProyectos[$i];
									?>
									<tr class="<?php echo ($c0%2==0)?'fila_gris':''; ?>">
										<td class="proyecto" colspan="1"><p class="left"><?php echo $arrNomProyectos[$i];?></p></td>
									<?php
										for ($u=0; $u < count($arrIdPortales); $u++) {
											$idPortalFind = $arrIdPortales[$u];
											?>
												<td class="resultado_t" colspan="1"><?php echo ( empty($arrCotResumen[$idProyectoFind][$idPortalFind]) )?0:$arrCotResumen[$idProyectoFind][$idPortalFind]; ?></td>
												<td class="resultado_u" colspan="1"><?php echo ( empty($arrCotResumenUnico[$idProyectoFind][$idPortalFind]) )?0:$arrCotResumenUnico[$idProyectoFind][$idPortalFind]; ?></td>
											<?php
										}
									?>
									</tr>
									<?php
									$c0++;
								}
							?>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</section>
	<!--FIN RESUMEN DE COTIZACIONES-->

	<!--CATEGORIZACION-->
	<section class="categorizacion">
		<div class="caja_3">
			<div class="caja_3_A">
				<div class="subtitulo_resumen_3A">
					<img class="icono_origen" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/icono_origen_categorizacion.png">
					<p class="nombre_section">Origen de la categorización</p>
				</div>
				<div class="caja_origen" id="caja_origen">
				</div>
			</div>
			<div class="caja_3_B">
				<div class="subtitulo_resumen_3B">
		    		<img class="icono_resumen" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/icono_resumen_categorizacion.png">
					<p class="nombre_section">Resumen de categorizados por asesor</p>
		 		</div>
				<div class="caja_resumen">
					<div class="categorizados_ejecutivoscxz">
						<div class="table_responsive_3B">
							<table class="table_3B">
								<thead>
									<tr>
										<td class="fondo_titulo_lista">
											<p class="titulo_lista">Asesor comercial</p>
										</td>
										<th class="fondo_titulo_lista">Cantidad cat. ventas</th>
										<th class="fondo_titulo_lista">Cantidad cat. ejecutivos</th>
									</tr>
								</thead>
								<?php
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
									if($res3->num_rows > 0){
										while($row3 = $res3->fetch_assoc()) {
											$ejecutivo		   = $row3['ejecutivo'];
											$catEjecutivoPanel = $row3['catEjecutivoPanel'];
											$catEjecutivoFicha = $row3['catEjecutivoFicha'];
											?>
												<tr>
													<td class="fondo_ejecutivo"><p class="nombre_lista"><?php echo $ejecutivo; ?></p></td>
													<td class="fondo_lista"><p class="resul_eject"><?php echo $catEjecutivoPanel; ?></p></td>
													<td class="fondo_lista"><p class="resul_ventas"><?php echo $catEjecutivoFicha; ?></p></td>
												</tr>
											<?php
									    }
									}
								?>
							</table>
						</div>
				    </div>
			    </div>
			</div>
		</div>
		<div class="texto_info">
			<p class="texto"><b>Categorización web:</b> Respuestas obtenidas de banner situado en la web de la inmobiliaria.</p>
	        <p class="texto"><b>Categorización E-mailing:</b> Respuestas obtenidas a través de la campaña E-mailing post cotización o consulta.</p>
	        <p class="texto"><b>Categorización Ejecutivo:</b> Cuestionario realizado por el ejecutivo a través de la ficha cotizante. (Vía Telefónica)</p>
	        <p class="texto"><b>Categorización Ventas:</b> Cuestionario realizado por el ejecutivo a través del botón cuestionario del panel de G-Leads.</p>
	        <p class="texto">(Envío por email o ingreso de cliente)</p>
		</div>
	</section>
	<!--FIN CATEGORIZACION-->

	<!--VOLUMEN DE PERSONAS-->
	<section class="volumen_personas">
			<div class="subtitulo">
			    <img class="icono_volumen" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/icono_volumen.png">
				<p class="nombre_section">Volumen de personas obtenidas al mes y que no han sido gestionadas por proyecto mensual</p>
			</div>
			<div class="datosgrafico_1">
				<div class="dato">
					<div style="float: left;">
						<b class="porcentaje_1"><?php echo $procentCotNoGest;?></b>
					</div>
					<div class="cotizantes_unicos">Cotizantes únicos<br>
					    <b class="no_gestionadas">NO gestionados</b>
					</div>
				</div>
				<div class="dato">
					<div style="float: left;">
						<b class="porcentaje_2"><?php echo $procentCotGest;?></b>
					</div>
					<div class="cotizantes_unicos">Cotizantes únicos<br>
						<b class="si_gestionados">gestionados</b>
					</div>
			    </div>
			</div>
			<div class="caja_4" id="caja_4">
			</div>

	</section>
	<!--FIN VOLUMEN DE PERSONAS-->

	<!--RESUMEN DE GESTIONES-->
	<section class="gestiones">
			<div class="subtitulo">
			    <img class="icono_gestion" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/icono_gestiones.png">
				<p class="nombre_section">Resumen de gestiones</p>
			</div>
			<div class="caja_5">
				<div class="caja_gestiones">
					<div class="table_responsive">
						<table class="">
							<tr>
								<td class="itemss" colspan="1"></td>
								<td class="nombre_items" colspan="4">Cantidad de gestiones</td>
								<td class="nombre_items" colspan="4">Cantidad de personas</td>
								<td class="nombre_items" colspan="4">Ratio seguimiento</td>
								<td class="nombre_items" colspan="4">Días de primer contacto</td>
							</tr>
							<tr>
								<td class="categoria_ejecutvio" colspan="1"></td>
								<td class="cat_cotizantes" colspan="1">Cotizantes</td>
								<td class="cat_categorizados" colspan="3">Categorizados</td>
								<td class="cat_cotizantes" colspan="1">Cotizantes</td>
								<td class="cat_categorizados" colspan="3">Categorizados</td>
								<td class="cat_cotizantes" colspan="1">Cotizantes</td>
								<td class="cat_categorizados" colspan="3">Categorizados</td>
								<td class="cat_cotizantes" colspan="1">Cotizantes</td>
								<td class="cat_categorizados" colspan="3">Categorizados</td>
							</tr>
							<tr>
								<td class="colores" colspan="1"></td>
								<td class="gris" colspan="1"></td>
								<td class="naranjo" colspan="1"></td>
								<td class="rojo" colspan="1"></td>
								<td class="amarillo" colspan="1"></td>
								<td class="gris" colspan="1"></td>
								<td class="naranjo" colspan="1"></td>
								<td class="rojo" colspan="1"></td>
								<td class="amarillo" colspan="1"></td>
								<td class="gris" colspan="1"></td>
								<td class="naranjo" colspan="1"></td>
								<td class="rojo" colspan="1"></td>
								<td class="amarillo" colspan="1"></td>
								<td class="gris" colspan="1"></td>
								<td class="naranjo" colspan="1"></td>
								<td class="rojo" colspan="1"></td>
								<td class="amarillo" colspan="1"></td>
							</tr>
							<?php
								$cotGesUnicoTotal = 0;
								$cotGesTotal 	  = 0;
								$cotRatioTotal 	  = 0;
								$cotDiasTotal 	  = 0;
								$catGesUnicoTotal = 0;
								$catGesTotal 	  = 0;
								$catRatioTotal 	  = 0;
								$catDiasTotal 	  = 0;
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
								if($res4->num_rows > 0){
									$c = '0';
									$f = $res4->num_rows-1;
									$cantCatDias = 0;
									$cantCotDias = 0;
									while($row4 = $res4->fetch_assoc()) {
										$ejecutivo 	 = $row4['ejecutivo'];
										$cotGesUnico = $row4['cotGesUnico'];
										$cotGes 	 = $row4['cotGes'];
										$cotRatio 	 = $row4['cotRatio'];
										$cotDias 	 = $row4['cotDias'];
										$catGesUnico = $row4['catGesUnico'];
										$catGes 	 = $row4['catGes'];
										$catRatio 	 = $row4['catRatio'];
										$catDias 	 = $row4['catDias'];
										if($catDias!=0){$cantCatDias++;}
										if($cotDias!=0){$cantCotDias++;}
										$cotGesUnicoTotal = $cotGesUnicoTotal+$cotGesUnico;
										$cotGesTotal 	  = $cotGesTotal+$cotGes;
										$cotDiasTotal 	  = $cotDiasTotal+$cotDias;
										$catGesUnicoTotal = $catGesUnicoTotal+$catGesUnico;
										$catGesTotal 	  = $catGesTotal+$catGes;
										$catDiasTotal 	  = $catDiasTotal+$catDias;
										?>
										    <tr class="<?php echo ($c%2==0)?'fila_gris':''; ?>">
											    <td class="ejecutivos" colspan="1"><p class="left"><?php echo $ejecutivo; ?></p></td>
												<td class="resultado_cot" colspan="1"><?php echo $cotGes; ?></td>
												<td class="resultado_cat" colspan="3"><?php echo $catGes; ?></td>
												<td class="resultado_cot" colspan="1"><?php echo $cotGesUnico; ?></td>
											    <td class="resultado_cat" colspan="3"><?php echo $catGesUnico; ?></td>
												<td class="resultado_cot" colspan="1"><?php echo round($cotRatio,1); ?></td>
												<td class="resultado_cat" colspan="3"><?php echo round($catRatio,1); ?></td>
												<td class="resultado_cot" colspan="1"><?php echo round($cotDias,1); ?></td>
												<td class="resultado_cat" colspan="3"><?php echo round($catDias,1); ?></td>
											</tr>
										<?php
										$c++;
										$arrResumenGest[$ejecutivo] = array($cotGes,$catGes,$cotGesUnico,$catGesUnico,$cotRatio,$catRatio,$cotDias,$catDias);

								    }
									$cotRatioTotal = ($cotGesUnicoTotal>0)?round(($cotGesTotal/$cotGesUnicoTotal),1):0;
									$catRatioTotal = ($catGesUnicoTotal>0)?round(($catGesTotal/$catGesUnicoTotal),1):0;
									$cotDiasTotal  = ($cantCotDias>0)?round(($cotDiasTotal/$cantCotDias),1):0;
									$catDiasTotal  = ($cantCatDias>0)?round(($catDiasTotal/$cantCatDias),1):0;
								    $arrResumenGest["Total"] = array($cotGesTotal,$catGesTotal,$cotGesUnicoTotal,$catGesUnicoTotal,$cotRatioTotal,$catRatioTotal,$cotDiasTotal,$catDiasTotal);

								}
							?>
							<tr class="fila_total">
							    <td class="ejecutivos" colspan="1"><p class="left">Total</p></td>
								<td class="resultado_cot" colspan="1"><?php echo $cotGesTotal; ?></td>
								<td class="resultado_cat" colspan="3"><?php echo $catGesTotal; ?></td>
								<td class="resultado_cot" colspan="1"><?php echo $cotGesUnicoTotal; ?></td>
							    <td class="resultado_cat" colspan="3"><?php echo $catGesUnicoTotal; ?></td>
								<td class="resultado_cot" colspan="1"><?php echo $cotRatioTotal; ?></td>
								<td class="resultado_cat" colspan="3"><?php echo $catRatioTotal; ?></td>
								<td class="resultado_cot" colspan="1"><?php echo $cotDiasTotal; ?></td>
								<td class="resultado_cat" colspan="3"><?php echo $catDiasTotal; ?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="texto_info">
				<p class="texto"><b>Cantidad de gestiones:</b> Es el número total de gestiones realizadas por los ejecutivos a sus cotizantes web durante el mes del informe.</p>
				<p class="texto"><b>Cantidad personas:</b> Es el total de personas únicas web gestionadas por el ejecutivo.</p>
				<p class="texto"><b>Ratio de seguimiento:</b> Representa a la cantidad promedio de gestiones/seguimiento que un ejecutivo hizo a un cotizante que haya realizado una cotización durante el mes del informe.</p>
				<p class="texto"><b>Días de primer contacto:</b> Es cantidad promedio de días que se tardó un ejecutivo en darle un primer contacto a un cotizante que cotizó durante el mes del informe.</p>
			</div>
	</section>
	<!--FIN RESUMEN DE GESTIONES-->

	<!--FOOTER-->
	<footer>
	<div class="contenedor">
		<img class="logotipo_tga" src="<?php echo base_url(); ?>images/sitio/reporte_pro/boletin/logotipo_tga.png" />
		<p class="texto_footer"></p>
	</div>
	</footer>
	<!--FIN FOOTER-->
</section>
<div id="cargaR"></div>
<!--FIN CONTENIDO CENTRAL-->
<script src="https://code.highcharts.com/modules/series-label.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<script src="http://code.highcharts.com/modules/offline-exporting.js"></script>

<script type="text/javascript">
	$('#filtro_anio').selectpicker();
	$('#filtro_mes').selectpicker();
	<?php if($ID_INMOBILIARIA == 96){ ?>
		$('#filtro_zona').selectpicker();		
	<?php } ?>
	var grafico_origen = new Highcharts.chart('caja_origen',{
    chart: {
        type: 'bar'
    },
	title: {
        text: ''
    },
    subtitle: {
        text: 'Canal de origen de las categorizaciones llegadas durante el mes'
    },
    exporting: {
    	enabled: false
    },
    credits: {
      enabled: false
  	},
    lang: {
        noData: "NO HAY DATOS"
    },
    xAxis: {
        categories: ['% Cat web', '% Cat emailing', '% Cat ejecutivo', '% Cat ventas']
    },
    yAxis: {
        min: 0,
        max: 100,
        labels: {
        	formatter: function() {
           return this.value+"%";
        	}
      	},
        title: {
            text: 'Porcentaje'
        }
    },
    legend: {
    		enabled: false
    },
    plotOptions: {
        series: {
            stacking: '',
            dataLabels: {
            	format: '{y} %'
            }
        },
        bar: {
            dataLabels: {
                enabled: true
            }
        },
    },
    series: [{
        name: '',
        data: [<?php echo $catOrigenWeb; ?>,<?php echo $catOrigenEmailing; ?>,<?php echo $catOrigenEjecutivo; ?>,<?php echo $catOrigenVenta; ?>],

    }],
});

function cargado(){}

var grafico_volumen = new Highcharts.chart('caja_4',{
	credits:{
		enabled: false
    },
    lang: {
        noData: "NO HAY DATOS"
    },
    chart: {
        type: 'area'
    },
    title: {
    	useHTML: true,
    	align: 'left',
        text: '<p></p>'
    },
    xAxis: {
    	title: {
      		text: 'Cotizantes únicos'
      	}
  	},
    yAxis: {
        title: {
            text: 'Cantidad de personas'
        }
    },
    tooltip: {
        pointFormat: 'Cantidad de {series.name} : <b>{point.y:,.0f}</b>'
    },
    series: [{
        name: 'Cotizaciones únicos no gestionados',
        data: [<?php echo $cotNoGestCateg;?>],
        color: '#73879C'
    }, {
        name: 'Cotizantes únicos gestionados',
        data: [<?php echo $cotGestCateg;?>],
        color: "#64C79F"
    }]
});
function salir(){
	window.location = '<?= base_url(); ?>valentina/entrada/close_user';
}
function volver(){
	window.location = '<?= base_url(); ?>reportes';
}
function filtrar(){
	var filtro_anio = $('#filtro_anio').val();
	var filtro_mes = $('#filtro_mes').val();
	var filtro_zona = $('#filtro_zona').val();
	if(typeof filtro_zona == 'undefined'){
		filtro_zona = 1;
	}
	window.location = "<?= base_url(); ?>boletin?anio="+filtro_anio+"&mes="+filtro_mes+"&zona="+filtro_zona;
}
function generaPDF(){
    $('#frm_download_pdf').submit();
}
var grafico_volumenR = grafico_volumen.getSVG({
									chart: {
										width: 1100,
										height: 400
									}
								});
var grafico_origenR = grafico_origen.getSVG({
									chart: {
										width: 450,
										height: 300
									}
								});
$("#svg_volumen").val(grafico_volumenR);
$("#svg_origen").val(grafico_origenR);

<?php if($ID_INMOBILIARIA == 96){ ?>
	$("#filtro_zona").val("<?= $idZona; ?>").selectpicker('refresh');		
<?php } ?>
$("#filtro_anio").val("<?= $fechaAnio; ?>").selectpicker('refresh');	
$("#filtro_mes").val("<?= $fechaMes; ?>").selectpicker('refresh');	
</script>
<?php
mysqli_close($boletin_db);
?>
