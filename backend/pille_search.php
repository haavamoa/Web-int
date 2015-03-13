<?php
require("pille_query.php");

/** A initiation if drugs are available or not, this will only init if there is drugs in the search textbox and will show up as a red text over the tables of drugs **/
$invalid_drugs = array();
if(isset($_POST["medical_drugs"])){
    //Check if the medics you are searching for are available
    $medical_drugs_post = $_POST["medical_drugs"];
    if(strlen($medical_drugs_post)>0){
        $medical_drugs = split(",",$medical_drugs_post);
        foreach($medical_drugs as $medical_drug){
            
            $active_ingredients = getActiveIngredients($medical_drug,new pille_query());
            if(count($active_ingredients)==0){ //Check if the medical drug exists
                array_push($invalid_drugs,$medical_drug);
                
    }
        }
    }
}

/*Check if you have pressed search and if the input is valid*/
function createMedicalViz(){
    /**
       :: Different views ::
       full = full information
       overlapp_bi = overlapping adverse effects
    */
    $view = $_POST["view"];
    if(isset($_POST["medical_drugs"])){
        $medical_drugs = split(",",$_POST["medical_drugs"]);
        if($view == "full"){
            createFullInformation($medical_drugs,new pille_query());
        }elseif($view == "overlapp_bi"){
            createOverLappingAdverseEffect($medical_drugs,new pille_query());
        }
        
    }
}
 
function createFullInformation($medical_drugs,$sparql){
    $medical_drugs_not_found = array();
    foreach($medical_drugs as $medical_drug){
        //Active Ingredients
        $active_ingredients = getActiveIngredients($medical_drug,new pille_query());
        if(!count($active_ingredients)==0){ //Check if the medical drug exists 
        //Adverse Effects
        $adverse_effects = getAdverseEffects($medical_drug,$sparql);
        //Indications
        $indications = getIndications($medical_drug,$sparql);
        /* Create the table*/
        createMedicalDrugTableFullInfo($medical_drug,$active_ingredients,$adverse_effects,$indications);            
        }

    }
       
}
function createMedicalDrugTableFullInfo($medical_drug,$active_ingredients,$adverse_effects,$indications){
 echo '<table width="100%" CELLPADDING="4" CELLSPACING="3" id="container" class="table table-hover table-bordered">' ;
 echo '<thead>';
 echo '<tr>';
 echo '<th style="font-size:25px">'.$medical_drug.'</th>';
 echo '</tr>';
 echo '</thead>';
 echo '<tbody>';
 
 echo '<td>';
 echo '<table width="100%" CELLPADDING="4" CELLSPACING="3" id="container" class="table table-hover table-bordered">';
 echo '<thead>';
 echo '<tr><th>Virkemiddel</th></tr>';
 foreach($active_ingredients as $active_ingredient)
     echo '<tr><td>'.$active_ingredient.'</td></tr>';
 echo '</thead>';
 echo '</table>';
 echo '</td>';

 echo '<td>';
 echo '<table width="100%" CELLPADDING="4" CELLSPACING="3" id="container" class="table table-hover table-bordered">';
 echo '<thead>';
 echo '<tr><th>Bivirkninger</th></tr>';
 echo '</thead>';
 echo '<tbody>';
 foreach($adverse_effects as $adverse_effect)
     echo '<tr><td>'.$adverse_effect.'</td></tr>';
 echo '</tbody>';
 echo '</table>';
 echo '</td>';

 echo '<td>';
 echo '<table width="100%" CELLPADDING="4" CELLSPACING="3" id="container" class="table table-hover table-bordered">';
 echo '<thead>';
 echo '<tr><th>Indikasjoner</th></tr>';
 foreach($indications as $indication)
     echo '<tr><td>'.$indication.'</td></tr>';
 echo '</thead>';
 echo '</table>';
 echo '</td>';
 echo '</tbody>';
 echo '</table>';
}

function createOverLappingAdverseEffect($medical_drugs,$sparql){
    /* Check overlap of every combination of the drugs*/
    echo '<table width="100%" CELLPADDING="4" CELLSPACING="3" id="container" class="table table-hover table-bordered">' ;
    echo '<thead>';
    echo '<tr>';
    echo '<th>Legemiddel</th>';
    echo '<th>Legemiddel</th>';
    echo '<th>Overlapp</th>';
    echo '</tr>';
    echo '</thead>';

    for($i=0;$i<count($medical_drugs)-1;$i++){
        for($j=$i+1;$j<count($medical_drugs);$j++){
            $query = 'SELECT * WHERE {
   {select ?bi_label where{
   ?s rdfs:label ?med_label .
   filter (lcase(str(?med_label)) = lcase(str("'.$medical_drugs[$i].'"))) .
   ?s lmh:harVirkemiddel ?virkemiddel . 
   ?virkemiddel lmh:harBivirkning ?bi .
         ?bi rdfs:label ?bi_label }}
   . 
   {select ?bi_label where{
   ?s rdfs:label ?med_label .
   filter (lcase(str(?med_label)) = lcase(str("'.$medical_drugs[$j].'"))) .
   ?s lmh:harVirkemiddel ?virkemiddel . 
   ?virkemiddel lmh:harBivirkning ?bi .
         ?bi rdfs:label ?bi_label }}
}';
            $result = $sparql->query($query);
            foreach($result as $row){
                $overlapping_adverse_effect = $row->bi_label;
                createOverlappingAdversesTable($medical_drugs[$i],$medical_drugs[$j],$overlapping_adverse_effect);
            }
        }
    }
    echo "</table>";
}

function createOverlappingAdversesTable($first_drug,$second_drug,$overlap){
 echo '<tbody>';
 echo '<tr><td>'.$first_drug.'</td><td>'.$second_drug.'</td><td>'.$overlap.'</td></tr>';
 echo '</tbody>';
}

function getActiveIngredients($medical_drug,$sparql){
                $active_ingredients = array();
                $query = 'SELECT ?virkestoff_label WHERE {
   ?s rdfs:label ?med_label .
   filter (lcase(str(?med_label)) = lcase(str("'.$medical_drug.'"))) .
   ?s lmh:harVirkemiddel ?virkemiddel . 
   ?virkemiddel rdfs:label ?virkestoff_label
} ';
                $result = $sparql->query($query);
                foreach($result as $row){
                    array_push($active_ingredients,$row->virkestoff_label);
                }
                
                return $active_ingredients;
}

function getAdverseEffects($medical_drug,$sparql){
    $adverse_effects = array();
    $query = 'SELECT ?bi_label WHERE {
   ?s rdfs:label ?med_label .
   filter (lcase(str(?med_label)) = lcase(str("'.$medical_drug.'"))) .
   ?s lmh:harVirkemiddel ?virkemiddel . 
   ?virkemiddel lmh:harBivirkning ?bi .
   ?bi rdfs:label ?bi_label 
} ';
    $result = $sparql->query($query);
    foreach($result as $row){
        array_push($adverse_effects,$row->bi_label);          
    }
    
    return $adverse_effects;
}


function getIndications($medical_drug,$sparql){
    $indications = array();
    $query = 'SELECT ?indikasjon_label WHERE {
   ?s rdfs:label ?med_label .
   filter (lcase(str(?med_label)) = lcase(str("'.$medical_drug.'"))) .
   ?s lmh:harVirkemiddel ?virkemiddel . 
   ?virkemiddel lmh:harIndikasjon ?indikasjon . 
   ?indikasjon rdfs:label ?indikasjon_label 
} ';
    $result = $sparql->query($query);
    foreach($result as $row){
        array_push($indications,$row->indikasjon_label);
    }
    
    return $indications;
}