<?php

include "../../conexion.php";

$busqueda = '';
$fecha_de = '';
$fecha_a = '';
$iva = 18;

date_default_timezone_set('America/Santo_Domingo'); 

$fecha = date('j/n/Y');
$hora = date('H:i:s');

    $select = "SELECT f.nofactura, f.fecha, f.codcliente, cl.nombre as cliente, f.totalfactura, f.estatus, v.nombre as vendedor, cl.nombre as cliente
            FROM factura f
            INNER JOIN usuario v
            ON f.usuario = v.idusuario
            INNER JOIN cliente cl
            ON f.codcliente = cl.idcliente
            WHERE f.estatus != 1 
            ORDER BY f.fecha";


    $result = $connection->query($select);

    if ($result > 0) {
        $data = array();
        while ($row = $result->fetch_object()) {
            $data .= '<tr>'
                . '<td class="no">' . $row->nofactura . '</td>'
                . '<td class="desc">' . $row->fecha . '</td>'
                . '<td class="unit">' . $row->cliente . '</td>'
                . '<td class="qty">' . $row->vendedor . '</td>'
                . '<td class="total">' . $row->totalfactura . '</td>
            </tr>';

            $precio_total = $row->totalfactura;
            $precio_total = $row->totalfactura;
            $subtotal = round($subtotal + $precio_total, 2);
        }
    }

    $impuesto = round($subtotal * ($iva / 100), 2);
    $tl_sniva = round($subtotal - $impuesto, 2);
    $total = round($subtotal + $impuesto, 2);

    // importando el archivo principal de las librerias de composer
    require_once __DIR__ . '/vendor/autoload.php';


    $css = file_get_contents('need/stylo.css');

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


    $mpdf->showImageErrors = true;
    $mpdf->curlAllowUnsafeSslRequests = true;

    $mpdf->WriteHTML("$css", \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML(
        '

   <body>
    ' . $anulada . '
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
                    <p>Fecha:'.$fecha.'</p>
                    <p>Hora:'.$hora.'</p>
                </div>
            </td>
        </tr>
    </table>
    <br>
    <table id="factura">
        <tr>
            <td colspan="4" id="details" class="clearfix">

            <td style="padding-right:80px; padding-left:80px;">
              
            </td>
            <td colspan="4">
                <div id="invoice">
                    <div>
                        <h1>Lista de Ventas Devueltas</h1>
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
                <th class="no">NO.FACTURA</th>
                <th class="desc">FECHA</th>
                <th class="unit">CLIENTE</th>
                <th class="qty">VENDEDOR</th>
                <th class="total">TOTAL</th>
            </tr>
        </thead>
         <tbody id="detalle_productos">
         ' . $data . '
         </tbody>
         <tfoot>
         <tr>
             <td colspan="2"></td>
             <td colspan="2">SUBTOTAL</td>
             <td>$' . $subtotal . '</td>
         </tr>
         <tr>
             <td colspan="2"></td>
             <td colspan="2">IVA (18)%</td>
             <td>$' . $impuesto . '</td>
         </tr>
         <tr>
             <td colspan="2"></td>
             <td colspan="2">GRAND TOTAL</td>
             <td>$' . $total . '</td>
         </tr>
     </tfoot>
    </table>
</body>

',
        \Mpdf\HTMLParserMode::HTML_BODY
    );

    $mpdf->SetFooter('{PAGENO}{nbpg}');

    $mpdf->Output("reporte.pdf", "I");

