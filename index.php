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
<title>HAFE - Informasjon om legemidler</title> 
</head>
    <body>
        <div class="jumbotron" id="header">
            <img src="resources/img/HAFE_3_T_02.png" width="22%" height="10%"> 
            <p>Informasjon om legemidler!</p>
</div>
        <div class="container-fluid">
          <form name="medicaldrug_search" method="POST" action="" role="form">
           <div class="form-group ">
               <div class="col-sm-3">
    <input type="text" name="medical_drugs" placeholder="Leggemiddel" class="form-control"
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
</div>
    <button type="submit"  name="search_btn" class="btn btn-default btn-primary" aria-label="Left Align">
              <span class="glyphicon glyphicon-search" aria-hidden="true"></span> Søk</button>
    <font>
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
            
            if($medical_drug == $invalid_drugs[0]){
                $invalid_drugs_string = $medical_drug;
            }elseif(end($medical_drugs)==$medical_drug){
                $invalid_drugs_string = $invalid_drugs_string." og ".$medical_drug;
            }else{
                $invalid_drugs_string = $invalid_drugs_string.", ".$medical_drug;
            }
            $medical_drug_key = array_keys($medical_drugs,$medical_drug);
            //Remove key from medical drugs
            unset($medical_drugs[$medical_drug_key]);

        }
        
        
    }
    if(strlen($invalid_drugs_string)>0)
        echo "<p><div class=\"alert alert-danger\">Legemiddlet $invalid_drugs_string finnes ikke!</div></p>";
}

?>
</font>
</br>
</br>
<table class="table" style="width:40%; margin-left:10px">
<tr><td style="width:35%; border:none"><label class="radio-inline">
<input type="radio" name="view" value="full" <?php if($_POST["view"] == "full") echo 'checked = "checked"'; elseif($_POST["view"] == "") echo 'checked = "checked"';?>/>
Full informasjon</label>
</td><td style="border:none"><label class="radio-inline">
<input type="radio" name="view" value="overlapp_bi" <?php if($_POST["view"] == "overlapp_bi") echo 'checked="checked"' ?> />Overlappende bivirkninger
</label>
</td></tr>

<tr><td style="border:none"><label class="radio-inline">
<input type="radio" name="view" value="overlapp_virk" <?php if($_POST["view"] == "overlapp_virk") echo 'checked="checked"' ?> />Overlappende virkestoff</label>
</td>
<td style="border:none"><label class="radio-inline">
<input type="radio" name="view" value="counteracting_effects" <?php if($_POST["view"] == "counteracting_effects") echo 'checked="checked"' ?> />
Motvirkende effekter</label>
</td></tr>
</table>

</tr>                                                                                   </form>

            <p></p>

<?php createMedicalViz($medical_drugs);?>

        </div>
    
    </body>
</html>

