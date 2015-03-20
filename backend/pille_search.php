<?php
require("pille_query.php");
require("pille_buildtables.php");


/** A initiation if drugs are available or not, this will only init if there is drugs in the search textbox and will show up as a red text over the tables of drugs **/
$invalid_drugs = array();
if(isset($_POST["medical_drugs"])){
    //Check if the medics you are searching for are available
    //Remove duplicates
    $medical_drugs = getMedicalDrugsFromSearchBar();
    if(count($medical_drugs)>0){        
        foreach($medical_drugs as $medical_drug){
            $active_ingredients = getActiveIngredients($medical_drug,new pille_query());
            if(count($active_ingredients[1])==0){ //Check if the medical drug exists
                array_push($invalid_drugs,$medical_drug);
                
    }
        }
    }
}

function getMedicalDrugsFromSearchBar(){
    if(isset($_POST["medical_drugs"])){
        $medical_drugs_split = split(",",$_POST["medical_drugs"]);
        //Trim whitespace
        $result = array_map('trim',$medical_drugs_split);
        //Remove duplicates
        $result = array_unique($result);
        return $result;
    }
}

/*Check if you have pressed search and if the input is valid*/
function createMedicalViz($medical_drugs){
    /**
       :: Different views ::
       full = full information
       overlapp_bi = overlapping adverse effects
    */
    if($_POST["view"]){
    $view = $_POST["view"];
        if($view == "full"){
            createFullInformation($medical_drugs,new pille_query());
        }elseif($view == "overlapp_bi"){
            createOverLappingAdverseEffect($medical_drugs,new pille_query());
        }elseif($view == "overlapp_virk"){
            createOverLappingActiveIngredients($medical_drugs,new pille_query());
        }elseif($view == "counteracting_effects"){
            createCounteractingEffects($medical_drugs,new pille_query());
        }
    }
}
 
function createFullInformation($medical_drugs,$sparql){
    $medical_drugs_not_found = array();
    $medical_drugs_json=array();
    foreach($medical_drugs as $medical_drug){
        //Active Ingredients
        $active_ingredients = getActiveIngredients($medical_drug,$sparql);
        if(!count($active_ingredients[1])==0){ //Check if the medical drug exists 
        //Adverse Effects
        $adverse_effects = getAdverseEffects($medical_drug,$sparql);
        //Indications
        $indications = getIndications($medical_drug,$sparql);
        //Dosages
        $dosage = getDosages($medical_drug,$sparql);
        /* Create Export to JSON form */
        $queries = [$active_ingredients[0],$adverse_effects[0],$indications[0]];
        /* Update the json array*/
        array_push($medical_drugs_json,["legemiddel" =>$medical_drug,
                                        "doseringer" => $dosage[1]
                                        ,"virkemiddler" => $active_ingredients[1]
                                        ,"bivirkninger" => $adverse_effects[1]
                                        , "indikasjoner" => $indications[1]]);
        /* Create the table*/
        createMedicalDrugTableFullInfo($medical_drug,$dosage[1],$active_ingredients[1],$adverse_effects[1],$indications[1]);            
        }


    }
    createExportJSONButton($medical_drugs_json);
    
}

function createOverLappingAdverseEffect($medical_drugs,$sparql){
    /* Check overlap of every combination of the drugs*/
    if(count($medical_drugs)>1){
        echo '<table width="100%" id="container" class="table">' ;
        echo '<thead>';
        echo '<tr class="info">';
        echo '<th>Legemiddel</th>';
        echo '<th>Legemiddel</th>';
        echo '<th>Overlappende bivirkning</th>';
        echo '</tr>';
        echo '</thead>';
        $overlappings_json = array();
        for($i=0;$i<count($medical_drugs)-1;$i++){
            for($j=$i+1;$j<count($medical_drugs);$j++){
                $query = "SELECT * WHERE {                                              
   {select ?bi_label where{                                                         
   ?s rdfs:label ?med_label .                                                       
   filter (lcase(str(?med_label)) = lcase(str('$medical_drugs[$i]'))) .             
   ?s lmh:harVirkemiddel ?virkemiddel .                                             
   ?virkemiddel lmh:harBivirkning ?bi .                                             
         ?bi rdfs:label ?bi_label }}                                                
   .                                                                                
   {select ?bi_label where{                                                         
   ?s rdfs:label ?med_label .                    
filter (lcase(str(?med_label)) = lcase(str('$medical_drugs[$j]'))) .             
   ?s lmh:harVirkemiddel ?virkemiddel .                                             
   ?virkemiddel lmh:harBivirkning ?bi .                                             
         ?bi rdfs:label ?bi_label }}                                                
}";
                $result = $sparql->query($query);
                foreach($result as $row){
                    $overlapping_adverse_effect = $row->bi_label;
                    array_push($overlappings_json,["bivirkning" => $row->bi_label,
                                                   "legemiddler" => [$medical_drugs[$i],$medical_drugs[$j]]]);
                    createOverlappingAdversesTable($medical_drugs[$i],$medical_drugs[$j],$overlapping_adverse_effect);
                }
            }
        }
        createExportJSONButton($overlappings_json);
        echo "</table>";
        
    }else{
        echo '<font color="red">Det trengs minst to legemiddler for å avgjøre overlappende bivirkninger</font>';
        
    }
}

function createOverLappingActiveIngredients($medical_drugs,$sparql){
    if(count($medical_drugs)<=1){
            echo '<font color="red">Det trengs minst to legemiddler for å avgjøre overlappende virkemiddel</font>';

        }else{
    echo '<table width="100%" id="container" class="table">' ;
    echo '<thead>';
    echo '<tr>';
    echo '<th>Legemiddel</th>';
    echo '<th>Legemiddel</th>';
    echo '<th>Overlappende virkestoff</th>';
    echo '</tr>';
    echo '</thead>';
    $overlappings_json = array();
    for($i = 0;$i<count($medical_drugs)-1;$i++){
        for($j=$i+1;$j<count($medical_drugs);$j++){
            
            $query = "
select * where {
{select ?virkestoff_label where{
         ?s rdfs:label ?med_label .
         filter (lcase(str(?med_label)) = lcase(str('$medical_drugs[$i]'))) .
         ?s lmh:harVirkemiddel ?virkemiddel . 
         ?virkemiddel rdfs:label ?virkestoff_label }}
   . 
   {select ?virkestoff_label where{
         ?s rdfs:label ?med_label .
         filter (lcase(str(?med_label)) = lcase(str('$medical_drugs[$j]'))) .
         ?s lmh:harVirkemiddel ?virkemiddel . 
         ?virkemiddel rdfs:label ?virkestoff_label }}
}";
             $result = $sparql->query($query);
             foreach($result as $row){
                 array_push($overlappings_json,["overlappende virkestoff" => $row->virkestoff_label, "legemiddler" => [$medical_drugs[$i],$medical_drugs[$j]]]);
                 createOverlappingActiveIngredientsTable($medical_drugs[$i],$medical_drugs[$j],$row->virkestoff_label);
             }
        }
    }
    
    createExportJSONButton($overlappings_json);
    echo '</table>';
    }
}

function createCounteractingEffects($medical_drugs,$sparql){
    if(count($medical_drugs)<=1){
        echo '<font color="red">Det trengs minst to legemiddler for å avgjøre motvirkende effekter</font>';
        
    }else{
        echo '<table width="100%" id="container" class="table">' ;
        $counteracting_json = array();
        for($i = 0;$i<count($medical_drugs);$i++){
            
            for($j = 0;$j<count($medical_drugs);$j++){
                if($medical_drugs[$i] !== $medical_drugs[$j]){ //Not the same drug
                    $query = "
SELECT * WHERE {
   
   {select ?overlapping where{
         ?s rdfs:label ?med_label .
         filter (lcase(str(?med_label)) = lcase(str('$medical_drugs[$i]'))) .
         ?s lmh:harVirkemiddel ?virkemiddel . 
         ?virkemiddel lmh:harBivirkning ?bi .
         ?bi rdfs:label ?overlapping .
      }}
   .
   {select ?overlapping where{
         ?s rdfs:label ?med_label .
         filter (lcase(str(?med_label)) = lcase(str('$medical_drugs[$j]'))) .
         ?s lmh:harVirkemiddel ?virkemiddel . 
         ?virkemiddel lmh:harIndikasjon ?indi .
         ?indi rdfs:label ?overlapping.
      }}
}
                       ";
                    $result = $sparql->query($query);
                    foreach($result as $row){
                        array_push($counteracting_json,
                                   ["effekt" => $row->overlapping,
                                    "legemiddel" => [$medical_drugs[$i],$medical_drugs[$j]]]);
                        createCounteractingEffectsTable($medical_drugs[$i],$medical_drugs[$j],$row->overlapping);
                        
                    }
                }
            }
        }
        createExportJSONButton($counteracting_json);
        echo '</table>';
    }
}



function getActiveIngredients($medical_drug,$sparql){
        $active_ingredients = array();
        $query = "SELECT ?virkestoff_label WHERE {
   ?s rdfs:label ?med_label .
   filter (lcase(str(?med_label)) = lcase(str('$medical_drug'))) .
   ?s lmh:harVirkemiddel ?virkemiddel . 
   ?virkemiddel rdfs:label ?virkestoff_label
} ";
        $result = $sparql->query($query);
        foreach($result as $row){
            array_push($active_ingredients,$row->virkestoff_label);
        }
        
        return [$query,$active_ingredients];
}

function getAdverseEffects($medical_drug,$sparql){
    $adverse_effects = array();
    $query = "SELECT ?bi_label WHERE {
   ?s rdfs:label ?med_label .
   filter (lcase(str(?med_label)) = lcase(str('$medical_drug'))) .
   ?s lmh:harVirkemiddel ?virkemiddel . 
   ?virkemiddel lmh:harBivirkning ?bi .
   ?bi rdfs:label ?bi_label 
} ";
    $result = $sparql->query($query);
    foreach($result as $row){
        array_push($adverse_effects,$row->bi_label);          
    }
    
    return [$query,$adverse_effects];
}


function getIndications($medical_drug,$sparql){
    $indications = array();
    $query = "SELECT ?indikasjon_label WHERE {
   ?s rdfs:label ?med_label .
   filter (lcase(str(?med_label)) = lcase(str('$medical_drug'))) .
   ?s lmh:harVirkemiddel ?virkemiddel . 
   ?virkemiddel lmh:harIndikasjon ?indikasjon . 
   ?indikasjon rdfs:label ?indikasjon_label 
} ";
    $result = $sparql->query($query);
    foreach($result as $row){
        array_push($indications,$row->indikasjon_label);
    }
    
    return [$query,$indications];
}

function getDosages($medical_drug,$sparql){
    $dosage = array();
    $query = "
SELECT distinct ?d_label WHERE{
   ?s ?p ?o . 
   ?s rdfs:label ?sl .
   filter(lcase(str(?sl)) = lcase(str('$medical_drug'))) .
   ?s lmh:harDosering ?asd .
   ?asd rdfs:label ?d_label
            
}";
    $result = $sparql->query($query);
    foreach($result as $row){
        array_push($dosage,$row->d_label);
    }
    return [$query,$dosage];
}