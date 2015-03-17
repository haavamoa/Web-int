<?php
require_once("backend/pille_search.php");
$medical_drugs = getMedicalDrugsFromSearchBar();    

?>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="style/standard.css">
    </head>
    <body>
        <div class="jumbotron" id="header">
            <h1>HAFE</h1> 
            <p>Informasjon om legemidler!</p> 
        </div>
        <div class="container-fluid">
            <label>Søkefelt: </label> 
          <form name="medicaldrug_search" method="POST" action="">
    <input type="text" name="medical_drugs" placeholder="Leggemiddel" 
    value="<?php 
$string="";
foreach($medical_drugs as $medical_drug) {
if(!($medical_drugs[0] == $medical_drug)){
$string = $string.', '.$medical_drug;
}else{
$string = $medical_drug;
}
}

echo $string;

?>" 
size=50/>
    <input type="submit" value="Søk"  name="search_btn"/>     
    <font color="red" size="2px"> 
<?php
/** A initiation if drugs are available or not, this will only init if there is drugs in the search textbox and will show up as a red text over the tables of drugs **/
               $invalid_drugs = array();
//Check if the medics you are searching for are available
if(count($medical_drugs)>0){        
    $invalid_drugs_string ="";
    foreach($medical_drugs as $medical_drug){
        $active_ingredients = getActiveIngredients($medical_drug,new pille_query());
        if(count($active_ingredients[1])==0){ //Check if the medical drug exists
            array_push($invalid_drugs,$medical_drug);                
            $medical_drug_key = array_keys($medical_drugs,$medical_drug);
            //Remove key from medical drugs
            foreach($medical_drug_key as $key)
                unset($medical_drugs[$key]);
            unset($medical_drugs[$medical_drug_key]);
            if($medical_drug == $invalid_drugs[0]){
                $invalid_drugs_string = $medical_drug;
            }elseif(end($invalid_drugs)==$medical_drug){
                $invalid_drugs_string = $invalid_drugs_string." og ".$medical_drug;
            }else{
                $invalid_drugs_string = $invalid_drugs_string.", ".$medical_drug;
            }
            
        }
        
        
    }
    if(strlen($invalid_drugs_string)>0)
        echo $invalid_drugs_string." finnes ikke!";
}

?>
</font>
</br>
<table cellpadding="100" cellspacing="10">
<tr>
<td style="padding:10px 20px 10px 0px">    <input type="radio" name="view" value="full" <?php if($_POST["view"] == "full") echo 'checked = "checked"'; elseif($_POST["view"] == "") echo 'checked = "checked"';?>> Full informasjon</input></td>

<td><input type="radio" name="view" value="overlapp_bi" <?php if($_POST["view"] == "overlapp_bi") echo 'checked="checked"' ?> >Overlappende bivirkninger</input></td>
</tr>
<tr><td><input type="radio" name="view" value="overlapp_virk" <?php if($_POST["view"] == "overlapp_virk") echo 'checked="checked"' ?> >Overlappende virkestoff</input>
<td><input type="radio" name="view" value="counteracting_effects" <?php if($_POST["view"] == "counteracting_effects") echo 'checked="checked"' ?> >Motvirkende effekter</input>
</td>
</table></td>

</tr>
                                                                                   </form>

            <p></p>

<?php createMedicalViz($medical_drugs);?>

        </div>
    
    </body>
</html>

