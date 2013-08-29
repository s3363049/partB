<!DOCTYPE HTML PUBLIC
"-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html401/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Search Results</title>
</head>
<body>
<style type="text/css">
body {text-align:center; }
table {
border-collapse:collapse;
margin-left:auto;
margin-right:auto;
width:70%;
}
th,td {
text-align:center;
padding:5px;
vertical-align:middle;
border: 1px solid black;
}
</style>

<?php

  function showerror() {
     die("Error " . mysql_errno() . " : " . mysql_error());
  }

  require 'db.php';

  // Show all wines in a region in a <table>
  function displayWinesList($connection, $query){
  
  // Run the query on the server
    if (!($result = @ mysql_query ($query, $connection))) {
      showerror();
    }

    // Find out how many rows are available
    $rowsFound = @ mysql_num_rows($result);

    // If the query has results ...
    if ($rowsFound > 0) {
    
    // ... print out a header
    print "<h1>Search Results</h1><br>";

    // Report how many rows were found
    print "{$rowsFound} records found <br><br>";

    // and start a <table>.
    print 
      "\n<table><tr>" .
      "\n\t<th>Wine Name</th>" .
      "\n\t<th>Grape Variety</th>" .
      "\n\t<th>Year</th>" .
      "\n\t<th>Winery</th>" .
      "\n\t<th>Region</th>" .
      "\n\t<th>Cost in Inventory</th>" .
      "\n\t<th>Stock on Hand</th>" .
      "\n\t<th>Stock Sold</th>" .
      "\n\t<th>Total Sales Revenue</th>\n</tr>";

      // Fetch each of the query rows
      while ($row = @ mysql_fetch_array($result)) {
        // Print one row of results
        print 
          "\n<tr>\n\t<td>{$row["wine_name"]}</td>" .
          "\n\t<td>{$row["variety"]}</td>" .
          "\n\t<td>{$row["year"]}</td>" .
          "\n\t<td>{$row["winery_name"]}</td>" .
          "\n\t<td>{$row["region_name"]}</td>" .
          "\n\t<td>{$row["cost"]}</td>" .
          "\n\t<td>{$row["on_hand"]}</td>" .
          "\n\t<td>{$row["TotalStockSold"]}</td>" .
          "\n\t<td>{$row["TotalRevenue"]}</td>\n</tr>";
      } // end while loop body

      // Finish the <table>
      print "\n</table><br><br>";
    } // end if $rowsFound body
    else
      {
        print "<br>No records found";
      }

  } // end of function

  // Connect to the MySQL server
  if (!($connection = @ mysql_connect(DB_HOST, DB_USER, DB_PW))) {
    die("Could not connect");
  }

  // get the user data
  $wineName = $_GET['wineName'];
  $wineryName = $_GET['wineryName'];
  $regionName = $_GET['regionName'];
  $grapeVariety = $_GET['grapeVariety'];
  $yearFrom = $_GET['yearFrom'];
  $yearTo = $_GET['yearTo'];
  $minStock = $_GET['minStock'];
  $minOrder = $_GET['minOrder'];
  $costMin = $_GET['costMin'];
  $costMax = $_GET['costMax'];  

  if (!mysql_select_db(DB_NAME, $connection)) {
    showerror();
  }

  // Start a query ...
  $query = 
    "SELECT wine_name, variety, year, winery_name, region_name, cost, on_hand, SUM(items.qty) AS TotalStockSold, SUM(items.price) AS TotalRevenue 
    FROM winery, region, wine, items, inventory, grape_variety, wine_variety
    WHERE winery.region_id = region.region_id 
    AND wine.winery_id = winery.winery_id 
    AND wine_variety.wine_id = wine.wine_id 
    AND wine_variety.variety_id = grape_variety.variety_id 
    AND inventory.wine_id = wine.wine_id 
    AND items.wine_id = wine.wine_id";

  //1.Wine Name
  if (isset($wineName) && $wineName != "" ) {
    $query .= " AND wine_name LIKE '%{$wineName}%'";	
  }

  //2.Winery Name
  if (isset($wineryName) && $wineryName != "") {
    $query .= " AND winery_name LIKE '%{$wineryName}%'";
  }

  //3.Region Name
  if (isset($regionName) && $regionName!= "") {
    $query .= " AND region_name = '{$regionName}'";
  }

  //4.Grape Variety
  if (isset($grapeVariety) && $grapeVariety != "") {
    $query .= " AND grape_variety.variety = '{$grapeVariety}'";
  }

  //5.Range of years
  if (isset($yearFrom) && isset($yearTo) && $yearFrom != "" && $yearTo != ""){
    $query .= " AND year BETWEEN '{$yearFrom}' AND '{$yearTo}'";
  }

  //6.Minimum wines in stock
  if (isset($minStock) && $minStock != "") {
    $query .= " AND inventory.on_hand >= '{$minStock}'";
  }

  //7.Minimum number of wines in stock
  if (isset($minOrder) && $minOrder != "") {
    $query .= " HAVING TotalStockSold  >= '{$minOrder}'";
  }

  //8.Minimum number of wines order
  if (isset($costMin) && isset($costMax) && $minCost != "" && $costMax != "") {
    $query .= " AND cost BETWEEN '{$minCost}' AND '{$costMax}'";
  }

  $query .= " GROUP BY wine.wine_id, grape_variety.variety_id
            ORDER BY wine.wine_name;";


  // run the query and show the results
  displayWinesList($connection, $query);


?>
</body>
</html>
