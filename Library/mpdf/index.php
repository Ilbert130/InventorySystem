<?php 

	include "../../conexion.php";

	if(empty($_REQUEST['cl']) || empty($_REQUEST['f']))
	{
		echo "No es posible generar la factura.";
	}else{
		
		$codCliente = $_REQUEST['cl'];
		$noFactura = $_REQUEST['f'];
		$anulada = '';

            $subtotal 	= 0;
            $iva 	 	= 18;
            $impuesto 	= 0;
            $tl_sniva   = 0;
            $total 		= 0; 

		$query = mysqli_query($connection,"SELECT f.nofactura, DATE_FORMAT(f.fecha, '%d/%m/%Y') as fecha, DATE_FORMAT(f.fecha,'%H:%i:%s') as  hora, f.codcliente, f.estatus,
												 v.nombre as vendedor,
												 cl.nit, cl.nombre, cl.telefono,cl.direccion
											FROM factura f
											INNER JOIN usuario v
											ON f.usuario = v.idusuario
											INNER JOIN cliente cl
											ON f.codcliente = cl.idcliente
											WHERE f.nofactura = $noFactura AND f.codcliente = $codCliente  AND f.estatus != 10 ");

		$result = mysqli_num_rows($query);
		if($result > 0){

			$factura = mysqli_fetch_assoc($query);
			$no_factura = $factura['nofactura'];

            $select = "SELECT p.codproducto,p.descripcion,dt.cantidad,dt.precio_venta,(dt.cantidad * dt.precio_venta) as precio_total
            FROM factura f
            INNER JOIN detallefactura dt
            ON f.nofactura = dt.nofactura
            INNER JOIN producto p
            ON dt.codproducto = p.codproducto
            WHERE f.nofactura = $no_factura ";


$result = $connection->query($select);

if ($result > 0) {
$data = array();
while($row = $result->fetch_object()){
$data .= '<tr>'
    .'<td class="no">'.$row->codproducto.'</td>' 
    .'<td class="desc">'.$row->descripcion.'</td>'
    .'<td class="unit">'.$row->cantidad.'</td>'
    .'<td class="qty">$'.$row->precio_venta.'</td>'
    .'<td class="total">$'.$row->precio_total.'</td>
</tr>';

$precio_total = $row->precio_total;
$subtotal = round($subtotal + $precio_total, 2);

}

}

$impuesto = round($subtotal * ($iva / 100), 2);
$tl_sniva = round($subtotal, 2);
$total = round($subtotal + $impuesto, 2);


    // importando el archivo principal de las librerias de composer
    require_once __DIR__ . '/vendor/autoload.php';


    $css = file_get_contents ('need/stylo.css');

    // Crear instancia de la clase mpdf
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'Letter'
    ]);

    $mpdf = new \Mpdf\Mpdf([
      'pagenumPrefix' => 'Numero de Pagina ',
      'pagenumSuffix' => ' - ',
      'nbpgPrefix' => ' de ',
      'nbpgSuffix' => ' Pagina'
  ]);
    

   if($factura['estatus'] == 2){

    $mpdf->SetWatermarkText('Anulado');
    $mpdf->showWatermarkText = true;
    $mpdf->watermarkTextAlpha = 0.1;

}


    $mpdf->showImageErrors = true;
    $mpdf->curlAllowUnsafeSslRequests = true;

    $mpdf->WriteHTML("$css", \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML('

   <body>
    '.$anulada.'
    <!--<img src="var:myvariable" style="width:10%"/>-->
    <table id="factura_head">
        <tr>
            <td>
                <div class="logo_factura">
                    <img src="need/img/log.png" style="width:20%">
                </div>
            </td>
            <td class="info_empresa">

            <div>
                <span class="h2">SISTEMA FACTURACION</span>
                <br>
                <br>
                
                <p><strong>Direccion:</strong> Gualey #34 c/16 </p>
                <p><strong>Teléfono:</strong> 8295567878 </p>
                <p><strong>Email:</strong> Amauris Ortiz </p>
            </div>

            </td>
            <td class="info_factura">
                <div>
                    <span class="h3">Factura</span>
                    <br>
                    <br>
                    <p><strong>Fecha:</strong> '.$factura['fecha'].'</p>
                    <p><strong>Hora:</strong> '.$factura['hora'].'</p>
                </div>
            </td>
        </tr>
    </table>
    <br>
    <table id="factura">
        <tr>
            <td colspan="4" id="details" class="clearfix">
            <td id="client">
                <div class="to"><strong>Cliente:</strong> '.$factura['nombre'].'</p></div>
                <div class="address"><strong>Direccion:</strong> '.$factura['direccion'].'</div>
                <div class="tel"><strong>Tel.:</strong> '.$factura['telefono'].'</div>
            </td>
            <td style="padding-right:80px; padding-left:80px;">
                <div>
                    <h3>Vendedor</h3>
                    <p class="lado" style="font-size:15px;">'. $factura['vendedor'].'</p>
                </div>
            </td>
            <td colspan="4">
                <div id="invoice">
                    <div>
                        <h1>No. Factura:'.$factura['nofactura'].'</h1>
                    </div>
                </div>
            </td>
            </td>
        </tr>
    </table>

    <br>
    <br>
    
    <table id="factura_detalle" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th class="no">#</th>
                <th class="desc">DESCRIPCION</th>
                <th class="unit">PRECIO UNIT.</th>
                <th class="qty">CANTIDAD</th>
                <th class="total">TOTAL</th>
            </tr>
        </thead>
         <tbody id="detalle_productos">
         '.$data.'
         </tbody>
        <tfoot>
            <tr>
                <td colspan="2"></td>
                <td colspan="2">SUBTOTAL</td>
                <td>$'.$subtotal.'</td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="2">IVA (18)%</td>
                <td>$'.$impuesto.'</td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="2">GRAND TOTAL</td>
                <td>$'.$total.'</td>
            </tr>
        </tfoot>
    </table>
</body>

'
, \Mpdf\HTMLParserMode::HTML_BODY);

        $mpdf->SetFooter('{PAGENO}{nbpg}');

        $mpdf->Output("reporte.pdf", "I");
    }
}


?>