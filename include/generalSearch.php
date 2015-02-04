<?php
/**
 * Created by PhpStorm.
 * User: valentinvegaa
 * Date: 12-11-2014
 * Time: 0:21
 */

$additionalParameters = array(
    'fq' => '',
    'fl' => '',
    'facet' => 'true',
    'facet.field' => 'dwc.institutionCode_s'
);
try {
    $results = $solr->search('*:*', 0, 0, $additionalParameters);
}catch (Exception $e)
{
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
}
?>
<div id="start-point"></div>
<div id="final-point"></div>
<form class="form-wrapper" accept-charset="utf-8" method="post">
    <input type="text" id="qw" name="qw" placeholder="Busque aquí" required>
    <input type="submit" value="Search" id="submit">
</form>
<script>
    (function ($) {
        Drupal.behaviors.yourThemeTabs = {
            attach: function (context, settings) {
                $("#tabs").tabs();
            }
        };
    })(jQuery);
</script>
<div>
    <?php
    function countReunaOccurences($name, $taxa, $solr){
        $additionalParameters = array(
            'fq' => $taxa.':"'.$name.'"',
            'fl' => '',
            'facet' => 'true',
            'facet.field' => 'dwc.institutionCode_s'
        );
        try {
            $results = $solr->search('*:*', 0, 0, $additionalParameters);
        }catch (Exception $e)
        {
            die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
        }
        return $results->response->numFound;
    }
    function getVernaculars($key){
        $vernaculars = json_decode(file_get_contents('http://api.gbif.org/v1/species/'.$key.'/vernacularNames'), true);
        $return='';
        foreach($vernaculars['results'] as $i){
            //$return[$i['sourceTaxonKey']]=$i['vernacularName'];
            $return.=$i['vernacularName'].', ';
        }
        $return=substr($return,0,sizeof($return)-3);
        return $return;
    }
    function makeTaxaHierarchy($i){
        $phylum=isset($i['phylum'])?$i['phylum']:'';
        $order=isset($i['order'])?$i['order']:'';
        $family=isset($i['family'])?$i['family']:'';
        $genus=isset($i['genus'])?$i['genus']:'';
        $result='';
        if($phylum!=''){
            $result.=''.$phylum.'';
        }
        if($order!=''){
            $result.=' > <a href="http://www.ecoinformatica.cl/site/analizador/order/'.$order.'">'.$order.'</a>';
        }
        if($family!=''){
            $result.=' > <a href="http://www.ecoinformatica.cl/site/analizador/family/'.$family.'">'.$family.'</a>';
        }
        if($genus!=''){
            $result.=' > <a href="http://www.ecoinformatica.cl/site/analizador/genus/"'.$genus.'>'.$genus.'</a>';
        }
        return $result;
    };
    function traducir($word){
        $palabra='';
        switch($word){
            case 'SPECIES':
                $palabra='Especie';
                break;
            case 'GENUS':
                $palabra='Genero';
                break;
            case 'FAMILY':
                $palabra='Familia';
                break;
            case 'ORDER':
                $palabra='Orden';
                break;
            case 'PHYLUM':
                $palabra='Filo';
                break;
            case 'VARIETY':
                $palabra='Variedad';
                break;
            case 'SUBSPECIES':
                $palabra='Subespecie';
                break;
        }
        return $palabra;
    }
    function suma($i,$j){return $i+$j;}
    $queryFilterWord = str_replace(' ', '+', $queryFilterWord);
    $offset=isset($_REQUEST['offset']) ? $_REQUEST['offset'] : false;
    if($offset){
        $search = json_decode(file_get_contents('http://api.gbif.org/v1/species/search?q=' . $queryFilterWord . '&dataset_key=d7dddbf4-2cf0-4f39-9b2a-bb099caae36c&nameType=WELLFORMED&nameType=SCINAME&offset='.$offset), true);
    }
    else{
        //$search = json_decode(file_get_contents('http://api.gbif.org/v1/species/search?q=' . $queryFilterWord . '&dataset_key=d7dddbf4-2cf0-4f39-9b2a-bb099caae36c&nameType=WELLFORMED&nameType=SCINAME&offset=0'), true);
        $search = json_decode(file_get_contents('http://api.gbif.org/v1/species/search?q=' . $queryFilterWord . '&dataset_key=d7dddbf4-2cf0-4f39-9b2a-bb099caae36c&nameType=WELLFORMED&nameType=SCINAME'), true);
    }
    //fab88965-e69d-4491-a04d-e3198b626e52 NCI Taxonomy
    //d7dddbf4-2cf0-4f39-9b2a-bb099caae36c GBIF backbone Taxonomy
    //$searchGenus = json_decode(file_get_contents('http://api.gbif.org/v1/species/search?q=' . $queryFilterWord . '&dataset_key=fab88965-e69d-4491-a04d-e3198b626e52&rank=GENUS'), true);
    //$searchFamily = json_decode(file_get_contents('http://api.gbif.org/v1/species/search?q=' . $queryFilterWord . '&dataset_key=fab88965-e69d-4491-a04d-e3198b626e52&rank=FAMILY'), true);
    //$searchOrder = json_decode(file_get_contents('http://api.gbif.org/v1/species/search?q=' . $queryFilterWord . '&dataset_key=fab88965-e69d-4491-a04d-e3198b626e52&rank=ORDER'), true);
    //$searchInstitutions = json_decode(file_get_contents('http://api.gbif.org/v1/species/search?q=' . $queryFilterWord . '&dataset_key=fab88965-e69d-4491-a04d-e3198b626e52&rank=FAMILY&limit=300'), true);
    print '<div id="tabs"><ul>';
    print ' <li><a href="#tabs-1"><div class="title">Resultados</div></a></li>';
    print ' <li><a href="#tabs-2"><div class="title">Instituciones</div></a></li>';
    print '</ul>';
    print '<div id="tabs-1">';
    count($search['results'])>0?print '<div class="subtitle">Aquí puede encontrar una lista de los resultados asociadas a su busqueda ('.$queryFilterWord.')</div>':print '<div class="no-results">No se han encontrado resultados</div>';
    $j=1;
    print '<div class="result-count"><span>Se han encontrado '.$search['count'].' resultados</span></div>';
    foreach ($search['results'] as $i) {
        if(strcmp($i['parent'],'Unclassified')!=0){
            print '<div class="result">';
            print '<div class="scientificName">';
            print '<span>'.suma($offset,$j).') </span>';
            switch($i['rank']){
                case 'SPECIES':
                    $reunaCount=countReunaOccurences($i['canonicalName'],'dwc.scientificName_mt',$solr);
                    $url='http://api.gbif.org/v1/occurrence/count?taxonKey='.$i['key'].'&isGeoreferenced=true&country=CL';
                    $count=json_decode(file_get_contents($url),true);
                    if($count>0 && $reunaCount>0){
                        print '<a href="http://www.ecoinformatica.cl/site/analizador/species/' .$i['canonicalName']. '">' . $i['canonicalName'] . '</a> <div class="resultCount">Reuna: '.$reunaCount.' Gbif: '.$count.'</div></div>';
                    }
                    else{
                        if($count==0&&$reunaCount>0)print '<a href="http://www.ecoinformatica.cl/site/analizador/species/' .$i['canonicalName']. '">' . $i['canonicalName'] . '</a><div class="resultCount"> No hay registros en Chile en Gbif si en Reuna</div></div>';
                        else if($count>0&&$reunaCount==0)print '<a href="http://www.gbif.org/species/'.$i['key'].'">' . $i['canonicalName'] . '</a><div class="resultCount">No hay registros en Chile en Reuna si en Gbif</div></div>';
                        else if($count==0&&$reunaCount==0)print '<a href="http://www.gbif.org/species/'.$i['key'].'">' . $i['canonicalName'] . '</a><div class="resultCount">No hay registros en Chile en Reuna ni registros en Chile en Gbif</div></div>';
                    }
                    break;
                case 'GENUS':
                    $reunaCount=countReunaOccurences($i['canonicalName'],'dwc.genus_mt',$solr);
                    $url='http://api.gbif.org/v1/occurrence/count?taxonKey='.$i['key'].'&isGeoreferenced=true&country=CL';
                    $count=json_decode(file_get_contents($url),true);
                    if($count>0 && $reunaCount>0){
                        print '<a href="http://www.ecoinformatica.cl/site/analizador/genus/' .$i['canonicalName']. '">' . $i['canonicalName'] . '</a> <div class="resultCount">Reuna: '.$reunaCount.' Gbif: '.$count.'</div></div>';
                    }
                    else{
                        if($count==0&&$reunaCount>0)print '<a href="http://www.gbif.org/species/'.$i['key'].'">' . $i['canonicalName'] . '</a><div class="resultCount"> No hay registros en Chile en Gbif si en Reuna</div></div>';
                        else if($count>0&&$reunaCount==0)print '<a href="http://www.gbif.org/species/'.$i['key'].'">' . $i['canonicalName'] . '</a><div class="resultCount">No hay registros en Chile en Reuna si en Gbif</div></div>';
                        else if($count==0&&$reunaCount==0)print '<a href="http://www.gbif.org/species/'.$i['key'].'">' . $i['canonicalName'] . '</a><div class="resultCount">No hay registros en Chile en Reuna ni registros en Chile en Gbif</div></div>';
                    }
                    break;
                case 'FAMILY':
                    $reunaCount=countReunaOccurences($i['canonicalName'],'dwc.family_mt',$solr);
                    $url='http://api.gbif.org/v1/occurrence/count?taxonKey='.$i['key'].'&isGeoreferenced=true&country=CL';
                    $count=json_decode(file_get_contents($url),true);
                    if($count>0 && $reunaCount>0){
                        print '<a href="http://www.ecoinformatica.cl/site/analizador/family/' .$i['canonicalName']. '">' . $i['canonicalName'] . '</a> <div class="resultCount">Reuna: '.$reunaCount.' Gbif: '.$count.'</div></div>';
                    }
                    else{
                        if($count==0&&$reunaCount>0)print '<a href="http://www.gbif.org/species/'.$i['key'].'">' . $i['canonicalName'] . '</a><div class="resultCount"> No hay registros en Chile en Gbif si en Reuna</div></div>';
                        else if($count>0&&$reunaCount==0)print '<a href="http://www.gbif.org/species/'.$i['key'].'">' . $i['canonicalName'] . '</a><div class="resultCount">No hay registros en Chile en Reuna si en Gbif</div></div>';
                        else if($count==0&&$reunaCount==0)print '<a href="http://www.gbif.org/species/'.$i['key'].'">' . $i['canonicalName'] . '</a><div class="resultCount">No hay registros en Chile en Reuna ni registros en Chile en Gbif</div></div>';
                    }
                    break;
                case 'ORDER':
                    print '<a href="http://www.ecoinformatica.cl/site/analizador/order/' .$i['canonicalName']. '">' . $i['canonicalName'] . '</a></div>';
                    break;
                case 'PHYLUM':
                    print '<a href="http://www.ecoinformatica.cl/site/analizador/phylum/' .$i['canonicalName']. '">' . $i['canonicalName'] . '</a></div>';
                    break;
                case 'VARIETY':
                    print '<a href="http://www.ecoinformatica.cl/site/analizador/species/' .$i['canonicalName']. '">' . $i['species'] . '</a></div>';
                    break;
                case 'SUBSPECIES':
                    print '<a href="http://www.ecoinformatica.cl/site/analizador/species/' .$i['canonicalName']. '">' . $i['species'] . '</a></div>';
                    break;
            }
            print '<div class="moreInfo">
                    <div class="rank">Tipo: '.traducir($i['rank']).'</div>
                    <div class="autor">Autor: '.$i['authorship'].'</div>
                    <div class="taxa-hierarchy">Jerarquia taxonómica: '.makeTaxaHierarchy($i).'</div>
                    <div class="vernacular">Nombres comunes: '.getVernaculars($i['key']).'</div>
            </div>';
            print '</div>';
            //$i['phylum'].'>'.$i['order'].'>'.$i['family'].'>'.$i['genus']
        }
        $j++;
        //dataset_key=fab88965-e69d-4491-a04d-e3198b626e52&highertaxon_key=106605002&rank=GENUS
        //&dataset_key=fab88965-e69d-4491-a04d-e3198b626e52&rank=SPECIES&highertaxon_key=106605002
    }
    print '<form method="post" action=""><input type="hidden" name="qw" value="'.$queryFilterWord.'"><span>Pagina: </span>';
    for($i=0;$i<$search['count'];$i+=20){
        $pagina=$i/20;
        print '<button type="submit" value="'.$i.'" name="offset">'.($pagina==0?'Primera':($pagina*20+20>=$search['count']?'Ultima':$pagina+1)).'</button>';
    }
    print '</form>';
    print '</div>';
    print '<div id="tabs-2">';
    if ($results) {
        $totalInstitutions = $results->response->numFound;
        $j=1;
        foreach ($results->facet_counts->facet_fields as $doc) {
            foreach ($doc as $field => $value) {
                print '<div class="result">';
                print '<div class="scientificName">';
                print '<span>'.$j.') </span>';
                print '<a href="http://www.ecoinformatica.cl/site/analizador/institutions/' .$field. '">' . $field . '</a>';
                print '</div></div>';
                $j++;
            }
        }
    }
    print '</div>';
    ?>
</div>