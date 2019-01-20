<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if( isset($_POST['download_yaml']) ) {
    $str = $_POST['usrform_gen'];
    $filename = "download.yaml";
  }

  if( isset($_POST['download_csv']) ) {
    $str = $_POST['csv_settings'];
    $filename = "settings.csv";
  }

  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Content-Type: text/plain'); # Don't use application/force-download - it's not a real MIME type, and the Content-Disposition header is sufficient
  header('Content-Length: ' . strlen($str));
  header('Connection: close');


echo $str;
}
?>
