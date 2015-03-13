<?php
require("pille_query.php");
/*Check if you have pressed search and if the input is valid*/
function createMedicalViz(){
    if(isset($_POST["medical_drugs"])){
        $medical_drugs = $_POST["medical_drugs"];
        if(strlen($medical_drugs)>0){
            $splitted_medical_drugs = split(",",$medical_drugs);
            $n_medical_drugs = count($splitted_medical_drugs);
            /*Retrieve medical drug information from pille */
            $sparql = new pille_query();
            
            foreach($splitted_medical_drugs as $medical_drug){


            //Active_ingredients
            $active_ingredients = getActiveIngredients($sparql,$medical_drug);
            if(count($active_ingredients)==0){
                echo '<font size=3" color="red">Legemiddel finnes ikke!</font>';
            }else{
                //Adverse Effects
                $adverse_effects = getAdverseEffects($sparql,$medical_drug);
                //Indications
                $indications = getIndications($sparql,$medical_drug);
                /* Create the table*/
                createMedicalDrugTable($medical_drug,$active_ingredients,$adverse_effects,$indications,1);            
            }
            }
        }       
    }
}

function createMedicalDrugTable($medical_drug,$active_ingredients,$adverse_effects,$indications){
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

function getActiveIngredients($sparql,$medical_drug){
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

function getAdverseEffects($sparql,$medical_drug){
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


function getIndications($sparql,$medical_drug){
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