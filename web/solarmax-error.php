<html>
 <head>
  <title>Solarmax Error</title>
  <link rel="shortcut icon" href="img/sun.ico" type="image/x-icon">
 </head>
 <body>
 <?php
  $fp = fopen("/var/log/solarmax-error.log","r");
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


