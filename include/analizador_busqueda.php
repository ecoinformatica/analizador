<?php
/**
 * Created by PhpStorm.
 * User: valentinvegaa
 * Date: 25-09-2014
 * Time: 23:38
 */
include($path . '/include/resumenGeneral.php');
$resumen=obtenerResumen($solr);
?>
<link href="<?php echo $path; ?>/include/busqueda.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $path; ?>/include/busqueda_especies.css" rel="stylesheet" type="text/css"/>
<div id="start-point"></div>
<div id="final-point"></div>
<form class="form-wrapper" accept-charset="utf-8" method="post">
    <input type="text" id="qw" name="qw" placeholder="Busque aquí" required>
    <input type="submit" value="Search" id="submit">
</form>
<?print $resumen;?>