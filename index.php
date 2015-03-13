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
            <label>Search: </label> 
          <form name="medicaldrug_search" method="POST" action="">
    <input type="text" name="medical_drugs" placeholder="Leggemiddel" 
    value="<?php echo $_POST['medical_drugs']; ?>" size=50/>
          <input type="submit" value="Søk"  name="search_btn"/>
          </form>

            <p></p>
<?php createMedicalViz();?>
        </div>
    
    </body>
</html>

