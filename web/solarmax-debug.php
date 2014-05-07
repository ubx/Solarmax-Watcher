<html>
 <head>
  <title>Solarmax Debug</title>
  <link rel="shortcut icon" href="img/sun.ico" type="image/x-icon">
 </head>
 <body>
 <?php
  $fp = fopen("/var/log/solarmax-debug.log","r");
  if ($fp)
  {
   while(!feof($fp))
   {
    $text = fgets($fp);
    echo"$text"."<br>";
   }
   fclose($fp);
  }
 ?>
 </body>
</html>


