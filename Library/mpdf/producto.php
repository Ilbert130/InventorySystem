<?php

echo'Jeison Bandido';

//$conection = require_once "../../conexion.php";

//include "../../conexion.php";

// importando el archivo principal de las librerias de composer
require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('America/Santo_Domingo'); 

$fecha = date('j/n/Y');
$hora = date('H:i:s');

$host = 'singleph-8922.banahosting.com';
$user = 'wbhfkmnj_Oneidyaa';
$password = '0c;jT}{E&=DD';
$db = 'wbhfkmnj_Clinica';

/*$host = 'localhost';
$user = 'roost';
$password = '';
$db = 'pos';*/

$conection = @mysqli_connect($host,$user,$password,$db);

if(!$conection){
    echo "Error de conexion";
}else{
    echo "Exito";
}

$query = "SELECT * FROM consultas";

$result = mysqli_num_rows($query);

if ($result > 0) {
    $data = array();
    while ($row = mysqli_fetch_array($query)){

    $data .= '<tr>'
        .'<td class="no">'.$row['estado'].'</td>' 
        /*.'<td class="desc">'.$row['descripcion'].'</td>'
        .'<td class="cat">'.$row['stock'].'</td>'
        .'<td class="price">'.$row['precio_compra'].'</td>'
        .'<td class="unit">'.$row['fechas'].'</td>*/
         .'</tr>';
    
    }
}

$css = file_get_contents ('need/stylo.css');

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
$mpdf->WriteHTML('
<body>
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
        <p><strong>Email:</strong> Ilbert Castillo </p>
    </div>

        </td>
        <td class="info_factura">
            <div>
                <span class="h3">Registro</span>
                <br>
                <br>              
                <p>Fecha:'.$fecha.'</p>
                <p>Hora:'.$hora.'</p>
            </div>
        </td>
    </tr>
</table>
<br>

<h1 class="titulo">Lista de Usuarios</h1>

<br>
<br>

<table id="factura_detalle" border="0" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th class="no">#</th>
            <th class="desc">NOMBRE</th>
            <th class="qty">CORREO</th>
            <th class="unit">USUARIO</th>
            <th class="qty">ROL</th>
        </tr>
    </thead>
     <tbody id="detalle_productos">
     '.$data.'
     </tbody>
</table>
</body>'

, \Mpdf\HTMLParserMode::HTML_BODY);

$mpdf->SetFooter('{PAGENO}{nbpg}');

$mpdf->Output("reporte.pdf", "I");


?>