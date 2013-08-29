<!DOCTYPE HTML PUBLIC
"-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html401/loose.dtd">
<html>
<head>
</head>
<body>
<h1>Winestore Database</h1>

<form  action="wine_query.php" method = "GET">
<?php        
  require "db.php";

  //Show Error Function
  function showerror() {
    die("Error " . mysql_errno() . " : " . mysql_error());
  }

  // selectDistinct() function
  function selectList ($connection, $tableName, $attributeName, $listName)
  {
    // Query to find distinct values of $attributeName in $tableName
    $distinctQuery = "SELECT DISTINCT {$attributeName} FROM {$tableName}";

    // Run the distinctQuery on the databaseName
    if (!($resultId = @ mysql_query ($distinctQuery, $connection))) {
      showerror();
    }

    // Start the select widget
    print "\n<select name=\"{$listName}\">";

    // Retrieve each row from the query
    while ($row = @ mysql_fetch_array($resultId))
    {
      // Get the value for the attribute to be displayed
      $result = $row[$attributeName];
      print "\n\t<option value=\"{$result}\">{$result}</option>";
    } 
    
    print "\n</select>";
  } // end of function

  // Connect to the server
  if (!($connection = @ mysql_connect(DB_HOST, DB_USER, DB_PW))) {
    showerror();
  }

  if (!mysql_select_db(DB_NAME, $connection)) {
    showerror();
  }
?>

<label>Wine Name:</label> <input name="wineName" type="text">
<br><label>Winery Name:</label><input name="wineryName" type="text">
<br><label>Region:</label><?php selectList($connection, "region", "region_name", "regionName"); ?>
<br><label>Grape Variety:</label> <?php selectList($connection, "grape_variety", "variety", "grapeVariety"); ?>
<br><label>Year:</label><?php selectList($connection, "wine", "year", "yearFrom"); ?> &nbsp to &nbsp<?php selectList($connection, "wine", "year", "yearTo"); ?>
<br><label>Minimum number of wines in stock:</label><input name="minStock" type="text">
<br><label>Minimum number of wines ordered:</label><input name="minOrder" type="text">
<br><label>Cost Range:</label><input name="costMin" type="text">&nbsp to &nbsp<input name="costMax" type="text">
<br><input name="submitBtn" type = "submit" value="search">
</form>

</body>
</html>

