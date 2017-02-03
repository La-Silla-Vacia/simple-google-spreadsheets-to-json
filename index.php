<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function updateFeed()
{
  $row = 1;
  $result = array();

  if (($handle = fopen($_POST['path'] . "&single=true&output=csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 4000, ",")) !== FALSE) {

      $num = count($data);
      $obj = array();
      $count = 'a';
      $row++;
      if ($row > 2) {
        for ($c = 0; $c < $num; $c++) {
          $obj[$count] = $data[$c];
          $count++;
        }
        array_push($result, $obj);
      }
    }
    fclose($handle);

    return json_encode($result);
  }
}

$cache_file = "./cache/". filter_var($_POST['path'], FILTER_SANITIZE_NUMBER_INT) .".json";

if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60 * 5 ))) {
  // Cache file is less than five minutes old.
  // Don't bother refreshing, just use the file as-is.
  $file = file_get_contents($cache_file);
  print_r($file);
} else {
  // Our cache is out-of-date, so load the data from our remote server,
  // and also save it over our cache for next time.
  $file = updateFeed();
  file_put_contents($cache_file, $file, LOCK_EX);
  print_r($file);
}