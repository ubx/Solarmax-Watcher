<?php
    /*
       Simple solarmax visualizer php program written by zagibu@gmx.ch in July 2010
       This program was originally licensed under WTFPL 2 http://sam.zoy.org/wtfpl/
       Improvements by Frank Lassowski flassowski@gmx.de in August 2010
       This program is now licensed under GPLv2 or later http://www.gnu.org/licenses/gpl2.html
    */
   $title="Solar Info Eggenstrasse 3";
   $slogan1="Photovoltaik-Anlage: 13.44 kWp (84 x 3S MegaSlate 2) / SolarMax 13MT3";
   $link0="http://luethi.dyndns.org/solarmax/solarertrag.php";
   $link1="solarertrag.php?wr=1";

   echo "<div id=\"header\">\n";
   echo "<h1><a href=\"" . $link0 . "\">" . $title . "</a></h1>\n";
   echo "<h5> ";
   echo "<a href=\"" . $link1 . "\">" . $slogan1 . "</a> ";
   echo "</h5>\n";
   echo "</div>\n";
?>
