<?php
	/*
		PHP data feeder for SolarAnalyzer (http://sunics.de/solaranalyzer_beschreibung.htm
		written by Stephan Collet stephan@sunics.de)
		written by Frank Lassowski flassowski@gmx.de in August 2010
		licensed under GPLv2 or later http://www.gnu.org/licenses/gpl2.html

		Put this file in your web directory, for example /var/www/
		It will be automagically called by SolarAnalyzer like this:
		http://yourwebadress/analyzer.php?q=day&d=3&m=9&y=2010   data of 2010-09-03 from all logged inverters
		or
		http://yourwebadress/analyzer.php?q=allDayData           data of all days from all logged inverters

		You shouldn't change the file name otherwise SolarAnalyzer can't figure out
		where to find it.
	*/

	// How many inverters do we have?
	$wrnum=1;
	$table="log";

	// Check GET vars
	$q = $_GET['q'];

	$wr=1;
	
	// Daten aller Tage
	if ($q == "allDayData") {
		// Connect to mysql database
		@mysql_connect('localhost', 'solaruser', 'solaruser') or die(mysql_error());
		@mysql_select_db('solarmax') or die(mysql_error());

		while($wr < $wrnum+1) {
			${result.$wr} = @mysql_query("SELECT created, DAY(created) AS day, max(kdy) AS kdy FROM $table$wr GROUP BY DATE(created)") or die(mysql_error());
			echo "created;kdy_".$wr."\n";
			if (mysql_num_rows(${result.$wr}) == 0) {
				// No data...create dummy line
				echo "no data\n";
			}
			else {
				while($row = mysql_fetch_assoc(${result.$wr})) {
					echo substr ($row['created'], 0, 10).";".$row['kdy']*100 . "\n";
				}
			}
			$wr++;
			echo "\n";
		}
		echo "\n";
	}

	// Tagesdaten
	elseif ($q == "day") {
		$sday = $_GET['d'];
		if (empty($sday))
			$sday = date('j');
		$smonth = $_GET['m'];
		if (empty($smonth))
			$smonth = date('n');
		$syear = $_GET['y'];
		if (empty($syear))
			$syear = date('Y');

		$start['day'] = $sday;
		$start['month'] = $smonth;
		$start['year'] = $syear;
		$end['day'] = $sday;
		$end['month'] = $smonth;
		$end['year'] = $syear;

		// Make sure we define a valid end date
		while (!checkdate($end['month'], $end['day'], $end['year']))
			$end['day']--;

		// Include time in start and end delimiters
		$start = date('Y-m-d H:i:s', mktime(0, 0, 0, $start['month'], $start['day'], $start['year']));
		$end = date('Y-m-d H:i:s', mktime(23, 59, 59, $end['month'], $end['day'], $end['year']));

		// Connect to mysql database
		@mysql_connect('localhost', 'solaruser', 'solaruser') or die(mysql_error());
		@mysql_select_db('solarmax') or die(mysql_error());

		while($wr < $wrnum+1) {
			// Select data from given day
			${result.$wr} = @mysql_query("SELECT * FROM $table$wr WHERE created BETWEEN '$start' AND '$end'") or die(mysql_error());
			echo "created;kdy_".$wr.";kmt_".$wr.";kyr_".$wr.";kt0_".$wr.";tnf_".$wr.";tkk_".$wr.";pac_".$wr.";prl_".$wr.";il1_".$wr.";idc_".$wr.";ul1_".$wr.";udc_".$wr.";sys_".$wr."\n";
			// No data...create dummy line      
			if (mysql_num_rows(${result.$wr}) == 0) {
				echo "no data\n";
			}
			else {
				while($row = mysql_fetch_assoc(${result.$wr})) {
					echo $row['created'].";".$row['kdy']*100 . ";".$row['kmt'].";".$row['kyr'].";".$row['kt0'].";".$row['tnf']/100 . ";".$row['tkk'].";".$row['pac'].";".$row['prl'].";".$row['il1']/100 . ";".$row['idc']/100 . ";".$row['ul1']/10 . ";".$row['udc']/10 . ";".$row['sys']."\n";
				}
			}
			$wr++;
			echo "\n";
		}
	}
?>