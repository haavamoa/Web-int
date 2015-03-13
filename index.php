<?php
require_once("backend/pille_search.php");
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
    <button type="button" class="btn btn-success" id="button-style">Export to JSON</button>
        <div class="container-fluid">
            <label>Søkefelt: </label> 
          <form name="medicaldrug_search" method="POST" action="">
    <input type="text" name="medical_drugs" placeholder="Leggemiddel" 
    value="<?php echo $_POST['medical_drugs']; ?>" size=50/>
    <input type="submit" value="Søk"  name="search_btn"/>     
    <font color="red"> <?php 



$invalid_drug ="";
foreach($invalid_drugs as $drug){

if($drug == $invalid_drugs[0]){
$invalid_drug = $drug;
}elseif(end($invalid_drugs)==$drug){
            $invalid_drug = $invalid_drug." og ".$drug;
        }else{
            $invalid_drug = $invalid_drug.", ".$drug;
}
}
echo $invalid_drug." finnes ikke!";


?>
</font>
</br>
    <input type="radio" name="view" value="full" <?php if($_POST["view"] == "full") echo 'checked = "checked"'; elseif($_POST["view"] == "") echo 'checked = "checked"';?>> Full informasjon</input>
</br>
<input type="radio" name="view" value="overlapp_bi" <?php if($_POST["view"] == "overlapp_bi") echo 'checked="checked"' ?> >Overlappende bivirkninger</input>

          </form>

            <p></p>
<?php createMedicalViz();?>
        </div>
    
    </body>
</html>

