<!DOCTYPE HTML PUBLIC
"-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html401/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Wine's Search Result</title>
</head>
<body>
<style>
body{text-align:center;}
table{
border-collapse:collapse;
margin-left:auto;
margin-right:auto;
width:70%;
}
th,td{
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
  function displayWinesList($connection, $query) {
    // Run the query on the server
    if (!($result = @ mysql_query ($query, $connection))) {
        showerror();
    }

    // Find out how many rows are available
    $rowsFound = @ mysql_num_rows($result);

    // If the query has results ...
    if ($rowsFound > 0) {
      //print out a header
      print "<h1>Search Result</h1><br>";

      //print number of records found
      print "{$rowsFound} records found <br><br>";

      // and start a <table>.
      print "\n<table><tr>" .
            "\n\t<th>Wine Name</th>" .
            "\n\t<th>Grape Variety</th>" .
            "\n\t<th>Year</th>" .
            "\n\t<th>Winery</th>" .
            "\n\t<th>Region</th>" .
            "\n\t<th>Cost</th>" .
            "\n\t<th>Stock in Hand</th>" .
            "\n\t<th>Stock Sold</th>" .
            "\n\t<th>Total Sales Revenue</th>\n</tr>";

      // Fetch each of the query rows
      while ($row = @ mysql_fetch_array($result)) {
        // Print one row of results
        print   "\n<tr>\n\t<td>{$row["wine_name"]}</td>" .
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
      print "\n</table>";
    } // end if $rowsFound body
    else{
  ?>
  <!--Pop up box if there is no records  -->
  <script type="text/javascript">   
    alert("There are no records found"); 
    history.back();
  </script>
  <?php
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

  // User validation Input
  if($yearFrom > $yearTo){
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please start with a lower bound for the year"); 
      history.back();
    </script>
  <?php
  } else if(!is_numeric($minStock) && !empty($minStock)){
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please enter a number for the stock"); 
      history.back();
    </script>
  <?php
  }else if(!is_numeric($minOrder) && !empty($minOrder)){
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please enter a number for the order"); 
      history.back();
    </script>
  <?php
  } else if(!is_numeric($costMin) && !empty($costMin)){
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please enter a number for the minimum cost"); 
      history.back();
    </script>
  <?php
  } else if(!is_numeric($costMax) && !empty($costMax)){
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please enter a number for the maximum cost"); 
      history.back();
    </script>
  <?php
  } else if ($costMin > $costMax) {
  ?>
    <!--Pop up box if the user input is wrong  -->
   <script type="text/javascript">   
      alert("Please start with a small number"); 
      history.back();
   </script>
  <?php
  }

  //if the validation success, do the query
  else {

  if (!mysql_select_db(DB_NAME, $connection)) {
    showerror();
  }

  // Start a query ...
  $query = "SELECT wine_name, variety, year, winery_name, region_name, cost, on_hand, SUM(items.qty) AS TotalStockSold, SUM(items.price) AS TotalRevenue 
FROM winery, region, wine, items, inventory, grape_variety, wine_variety
WHERE winery.region_id = region.region_id 
AND wine.winery_id = winery.winery_id 
AND wine_variety.wine_id = wine.wine_id 
AND wine_variety.variety_id = grape_variety.variety_id 
AND inventory.wine_id = wine.wine_id 
AND items.wine_id = wine.wine_id";

  // if statements to check whether the user entered something in the field
  // Add filter to the search if something is inputted on the form.

  //1.Search by wine name
  if (isset($wineName) && $wineName != "" ) {
    $query .= " AND wine_name LIKE '%{$wineName}%'";
  }

  //2. Search by winery name
  if (isset($wineryName) && $wineryName != "") {
    $query .= " AND winery_name LIKE '%{$wineryName}%'";
  }

  //3. Search by region
  if (isset($regionName) && $regionName != "") {
    $query .= " AND region_name = '{$regionName}'";
  }

  //4. Search by grape variety
  if (isset($grapeVariety) && $grapeVariety != "") {
    $query .= " AND variety = '{$grapeVariety}'";
  }

  //5. Search by year
  if (isset($yearFrom) && isset($yearTo) && $yearFrom != "" && $yearTo != ""){
    $query .= " AND year BETWEEN '{$yearFrom}' AND '{$yearTo}'";
  }

  //6. Search by number of stock
  if (isset($minStock) && $minStock != "") {
    $query .= " AND inventory.on_hand >= '{$minStock}'";
  }

  //7. Search by cost range
  if (isset($costMin) && isset($costMax) && $costMin != "" && $costMax != "") {
    $query .= " AND cost >= '{$costMin}' AND cost <= '{$costMax}'";
  }

  //Search are done by grouping the wine_id and grape variety
  $query .= " GROUP BY wine.wine_id, variety ";
  
  //8. Search by minimum order
  if (isset($minOrder) && $minOrder != "") {
    $query .= " HAVING TotalStockSold  >= '{$minOrder}'";
  }

  //Search list are arrange by wine name
  $query .= " ORDER BY wine_name;";

  //Display the search list
  displayWinesList($connection, $query);
}
?>
</body>
</html>



