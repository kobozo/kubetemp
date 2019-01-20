<?php
  function csv_to_array($filename='', $delimiter=',')
  {
    if(!file_exists($filename) || !is_readable($filename))
      return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
      while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
      {
        $i=0;
        foreach($row as $column) {
          $row[$i] = trim($column);
          $i++;
        }
        if(!$header)
          $header = $row;
        else
          $data[] = array_combine($header, $row);
      }
      fclose($handle);
    }
    return $data;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The request is using the POST method
    if( isset($_FILES['file_upload']['tmp_name']) && $_FILES['file_upload']['tmp_name'] <> "" && isset($_POST['submit']) ){ 
      $usrform_file = file_get_contents($_FILES['file_upload']['tmp_name']);
    } else {
      $usrform_file = $_POST['file'];
    }
    $usrform_generated = $usrform_file;

    preg_match_all('/\$\((.*?)\\)/s', $usrform_file, $matches, PREG_PATTERN_ORDER);
   
    $matches_filtered = array_unique( $matches[1] );
    
    if( count($matches_filtered) > 0 ) {

      if(isset($_FILES['variable_csv']['tmp_name'])) {
        $variables_csv = csv_to_array($_FILES['variable_csv']['tmp_name']);
	foreach($variables_csv as $variable_csv) {
          $key = "id_".$variable_csv['key'];
          $$key = $variable_csv['value'];
        }
      }

      $csv_settings = "key,value\n";

      foreach( $matches_filtered as $variable ) {
        //SPLIT ON | - AFTER PIPE IS DEFAULT VALUE
        $variable_exploded = explode("|", $variable);
        $key = "id_".$variable_exploded[0];
        if( $$key == "" && !isset($_POST['submit']) ) {
          $$key = $_POST[$key];
        }

        if( ($$key == "" && isset($variable_exploded[1])) || isset($_POST['defaults']) ) {
          $$key = $variable_exploded[1];
        }
        
        if( isset($_POST['generate']) ) {
          $to_replace = "$(".$variable.")";

          $usrform_generated = str_replace($to_replace, $$key, $usrform_generated);
          $csv_settings = $csv_settings.$variable_exploded[0].','.$$key."\n";
        }
      }

    }
    $msg_post = "Post successful";
  } else {
    $msg_post = "";
    $matches_filtered = "";
    $usrform_file = "";
  }
?>
<html>
  <head>
    <link rel="icon" type="image/png" href="/icon.png" />
    <title>KubeTemp.io</title>
    <script language="javascript">
      function myCopy(field) {
        var copyText = document.getElementById(field);
        copyText.select();
        document.execCommand("copy");
        return false;
      }
    </script>
  </head>
  <body>
    <center><H1>Original file <a href="https://github.com/kobozo/kubetemp/blob/master/HOWTO.md" target="_blank">(Read Me)</a></H1></center>
    <form action="/" method="post" id="usrform" enctype="multipart/form-data">
      <textarea style="width: 100%" rows=12 name="file"><?php echo $usrform_file;?></textarea>
      <br/>
      <input type="file" name="file_upload">
      <input type="submit" value="submit" id="submit" name="submit">

    <?php
      if( count($matches_filtered) > 0 && $_POST ) {
    ?>
      <center><H1>Detected variables</H1></center>
      <H3>Upload a CSV with your values</H3>
      <label for="variable_csv">Upload CSV</label> <input type="file" name="variable_csv" id="variable_csv"><input type="submit" name="upload" value="upload">
      <H3>Edit manually</H3>
      <table border="0">
    <?php
        $check_keys = array("");
        foreach( $matches_filtered as $variable) {
          $variable_exploded = explode("|", $variable);
          $key = "id_".$variable_exploded[0];
          if( !in_array( $key, $check_keys ) ) {
            $check_keys[] = $key;
    ?>
    <tr>
      <td><label for="id_<?php echo $variable; ?>"><?php echo $variable_exploded[0]; ?></label></td>
      <td><input type="text" name="<?php echo $key; ?>" value="<?php echo $$key; ?>"></td>
    <?php
      if( isset($variable_exploded[2]) ) {
    ?>
      <td>*<?php echo $variable_exploded[2]; ?></td>
    <?php
      }
    ?>
    </tr>
    <?php
           } 
         }
    ?>
      </table>
      <p><input type="submit" name="generate" value="generate" id="generate"><input type="reset" name="reset" value="reset"><input type="submit" name="defaults" value="Load defaults"></p>
    <?php 
      }
    ?>
      </form>
      <form action="/download.php" method="post" target="_blank"> 
    <?php
      if( isset($_POST['generate']) ) {
      ?>
        <center><H1>Regenerated files</H1></center>
        <p> YAML: <button type="button" onclick="myCopy('usrform_gen')">Copy</button><input type="submit" value="Download" name="download_yaml"><br/><textarea style="width: 100%" rows=12 id="usrform_gen" name="usrform_gen"><?php echo $usrform_generated;?></textarea></p>
        <p> SHA1: <button type="button" onclick="myCopy('usrform_sha1')">Copy</button><br/> <input type="text" value="<?php echo sha1($usrform_generated); ?>" id="usrform_sha1"  style="width: 100%">
        <p> CSV: <button type="button" onclick="myCopy('csv_settings')">Copy</button><input type="submit" value="Download" name="download_csv"><br/><textarea style="width: 100%" rows=12 id="csv_settings" name="csv_settings"><?php echo $csv_settings;?></textarea></p>
      <?php
      }

    ?>
    </form>
  </body>
</html>
