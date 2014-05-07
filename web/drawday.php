<?php
/*
 Simple solarmax visualizer php program written by zagibu@gmx.ch in July 2010
This program was originally licensed under WTFPL 2 http://sam.zoy.org/wtfpl/
Improvements by Frank Lassowski flassowski@gmx.de in August 2010
This program is now licensed under GPLv2 or later http://www.gnu.org/licenses/gpl2.html
*/


function draw_day($start, $end, $pred_day, $image_name, $table, $fontfile, $show_text, $lang) {

	// include language file
	include 'lang.php';
	
	// Select data from given day
	// If you are experiencing problems with the value 'kdy' especially the days very first 'kdy' is equal to the last one of the previous day comment in the next line and comment out the following one. Be aware: This is !!!untested!!! Don't slap me if something's going wrong!
	$result1 = @mysql_query("SELECT HOUR(created) AS hour, MINUTE(created) AS minute, pac, kdy FROM $table WHERE created BETWEEN '$start' AND '$end' LIMIT 1, 999999999") or die(mysql_error());
	//$result1 = @mysql_query("SELECT HOUR(created) AS hour, MINUTE(created) AS minute, pac FROM $table WHERE created BETWEEN '$start' AND '$end'") or die(mysql_error());
	$result2 = @mysql_query("SELECT HOUR(created) AS hour, MINUTE(created) AS minute, kdy, udc1, udc2, udc3, idc1, idc2, idc3 FROM $table WHERE created BETWEEN '$start' AND '$end'") or die(mysql_error());

	if (mysql_num_rows($result1) == 0)
	{
		// No data...create dummy image
		$image = imagecreatetruecolor(10, 10);
		$white = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $white);
		imagepng($image, $image_name);
		imagedestroy($image);
		return $GLOBALS["error3".$GLOBALS['lang']] . '<br />';
	}

	// Initialize pac image
	// Sun rise and fall during longest day at your place, must be integer
	$rise = 5; $fall = 22;
	// Pixel per hour should be equal to log entries per hour (I log every minute)
	$px_per_hour = 39;
	// Width = hours * px per hour + gap
	$width = ($fall - $rise) * $px_per_hour +157;
	$maxpac = 13440;
	$maxkdy = 100;
	$maxidc = 10;
	$maxudc = 650;
	$minudc = 400;

	$lastidc1 = 0;
	$lastidc2 = 0;
	$lastidc3 = 0;

	$lastudc1 = 0;
	$lastudc2 = 0;
	$lastudc3 = 0;

	$lastpdc1 = 0;
	$lastpdc2 = 0;
	$lastpdc3 = 0;

	// How many W per diagram line
	$step_w = 1000;
	$step = floor ( $maxpac / $step_w);
	$step_kdy = $maxkdy / $step;
	$step_idc = $maxidc / $step;
	$step_udc = ($maxudc  - $minudc)/ $step;
	// How many px per diagram line (W/px = $step_w / $vert_px)
	$vert_px = 30;
	// Height = number of lines * px per line + px per line (for 0-line) + gap
	$gap = 50;
	$height = $maxpac / $step_w * $vert_px + $gap + 10;
	// Create image, prepare colors and set background to white
	$image = imagecreatetruecolor($width, $height);
	// get the colors
	include 'colors.php';
	imagefill($image, 0, 0, $white);

	if (preg_match('/gridday/', $show_text)) {
		// Draw horizontal lines with some space above and below
		for ($i = 0; $i <= $step; $i++) {
			// Create horizontal grid line
			$ypos = $height - $i * $vert_px - $gap;
			imageline($image, 12, $ypos, $width - 110, $ypos, $gray);
			// Draw the needed scales at the end of the horizontal line
			$pac = $i * $step_w;
			$kdy = $i * $step_kdy;
			$idc = $i * $step_idc;
			$udc = ($i * $step_udc) + $minudc;

			if (preg_match('/yield/', $show_text) | preg_match('/pdc/', $show_text))
				imagefttext($image, 7, 0, $width - 109, $ypos + 4, $black, $fontfile, (floor ($pac) / 1000.0));
			if (preg_match('/accu/', $show_text) | preg_match('/predday/', $show_text))
				imagefttext($image, 7, 0, $width - 77, $ypos + 4, $blue, $fontfile, floor ($kdy));
			if (preg_match('/current/', $show_text))
				imagefttext($image, 7, 0, $width - 52, $ypos + 4, $black, $fontfile, number_format ($idc, 1));
			if (preg_match('/voltage/', $show_text))
				imagefttext($image, 7, 0, $width - 25, $ypos + 4, $red, $fontfile, floor ($udc));
		}

		// Draw vertical lines with some space at the left and right
		for ($i = 0; $i <= $fall - $rise; $i++) {
			// Create vertical grid line
			$xpos = $i * $px_per_hour + 25;
			imageline($image, $xpos, 5, $xpos, $height - $gap + 6, $gray);
			// Draw the hour value at the end of the vertical line
			$hour = ($i + $rise) % 24 . ':00';
			imagefttext($image, 8, 0, $xpos - 10, $height - $gap + 18, $black, $fontfile, $hour);
		}
	}

	//explain colored lines
	if ((preg_match('/yield/', $show_text) | preg_match('/pdc/', $show_text)) & preg_match('/gridday/', $show_text)) {
		imagefttext($image, 7, 0, $width - 112, 10, $black, $fontfile, "(kW)");
	}

	$hp = 10;
	if (preg_match('/accu/', $show_text) | preg_match('/predday/', $show_text)) {
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $blue);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, ${'text17'.$lang});
		$hp--;
		if (preg_match('/gridday/', $show_text))
			imagefttext($image, 6, 0, $width - 81, 10, $blue, $fontfile, "(kWh)");
	}
	if (preg_match('/voltage/', $show_text)) {
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $red);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "UDC#1");
		$hp--;
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $green);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "UDC#2");
		$hp--;
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $blue);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "UDC#3");
		$hp--;
		if (preg_match('/gridday/', $show_text))
			imagefttext($image, 7, 0, $width - 25, 10, $red, $fontfile, "(V)");
	}
	if (preg_match('/current/', $show_text)) {
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $red);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "IDC#1");
		$hp--;
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $green);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "IDC#2");
		$hp--;
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $blue);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "IDC#3");
		$hp--;
		if (preg_match('/gridday/', $show_text))
			imagefttext($image, 6, 0, $width - 50, 10, $black, $fontfile, "(A)");
	}
	if (preg_match('/pdc/', $show_text)) {
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $red);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "PDC#1");
		$hp--;
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $green);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "PDC#2");
		$hp--;
		imageline($image, $width - ($width * $hp / 10) + 10, $height - 15, $width - ($width * $hp / 10) + 25, $height - 15, $blue);
		imagefttext($image, 7, 0, $width - ($width * $hp / 10) + 30, $height - 12, $black, $fontfile, "PDC#3");
	}

	// Draw pac values
	if (preg_match('/yield/', $show_text)) {
		$lastxpos = 0;
		while($row = mysql_fetch_assoc($result1)) {
			// Determine x position
			$xpos = floor (($row['hour'] - $rise) * $px_per_hour + $row['minute'] / 60 * $px_per_hour + 25);
			if ($xpos > $lastxpos) {
				// Calculate y position with logged pac
				$pac = $row['pac'] / $step_w * $vert_px;
				// Draw pac line
				imageline($image, $xpos, $height - $gap, $xpos, $height - $gap - $pac, $yellow2);
				if ($xpos > ($lastxpos + 1)) {
					imageline($image, $xpos-1, $height - $gap, $xpos-1, $height - $gap - $pac, $yellow2);
				}
			}
			$lastxpos = $xpos;
		}
	}

	// Draw prediction line
	if (preg_match('/predday/', $show_text)) {
		$pred = $pred_day / $step_kdy * $vert_px;
		imageline($image, 12, $height - $pred - $gap, $width - 122, $height - $pred - $gap, $blue);
	}

	// Draw other logged values: kdy, idc, udc
	$lastxpos = 0;
	$kdyLast = 0;
	while($row = mysql_fetch_assoc($result2)) {
		// Determine x position
		$xpos = ($row['hour'] - $rise) * $px_per_hour + $row['minute'] / 60 * $px_per_hour + 25;
			
		// skip over period with missing data
		if (($xpos - $lastxpos) > 4) {
			$lastidc1 = 0;
			$lastidc2 = 0;
			$lastidc3 = 0;
			$lastudc1 = 0;
			$lastudc2 = 0;
			$lastudc3 = 0;
		}

		// Logged kdy is ten times as high as effective
		$kdylast = $row['kdy'] / 10;
		if (preg_match('/accu/', $show_text)) {
			$kdy = $kdylast / $step_kdy * $vert_px;
			// Draw kdy dot
			imagesetpixel($image, $xpos, $height - $gap - $kdy, $blue);
			imagesetpixel($image, $xpos-1, $height - $gap - $kdy, $blue);
		}

		// draw idc1 ..  idc3
		if (preg_match('/current/', $show_text)) {

			$idc = $row['idc1'] / 100 / $step_idc * $vert_px;
			if ($lastidc1 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $idc, $red);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastidc1, $xpos, $height - $gap - $idc, $red);
			}
			$lastidc1 = $idc;

			$idc = $row['idc2'] / 100 / $step_idc * $vert_px;
			if ($lastidc2 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $idc, $green);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastidc2, $xpos, $height - $gap - $idc, $green);
			}
			$lastidc2 = $idc;

			$idc = $row['idc3'] / 100 / $step_idc * $vert_px;
			if ($lastidc3 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $idc, $blue);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastidc3, $xpos, $height - $gap - $idc, $blue);
			}
			$lastidc3 = $idc;
		}


		// Logged udc is ten times as high as effective
		if (preg_match('/voltage/', $show_text)) {

			$udc = (($row['udc1'] / 10) - $minudc) / $step_udc * $vert_px;
			if ($lastudc1 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $udc, $red);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastudc1, $xpos, $height - $gap - $udc, $red);
			}
			$lastudc1 = $udc;

			$udc = (($row['udc2'] / 10) - $minudc) / $step_udc * $vert_px;
			if ($lastudc2 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $udc, $green);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastudc2, $xpos, $height - $gap - $udc, $green);
			}
			$lastudc2 = $udc;

			$udc = (($row['udc3'] / 10) - $minudc) / $step_udc * $vert_px;
			if ($lastudc3 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $udc, $blue);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastudc3, $xpos, $height - $gap - $udc, $blue);
			}
			$lastudc3 = $udc;
		}

		// Logged pdc
		if (preg_match('/pdc/', $show_text)) {

			$pdc = ($row['udc1'] * $row['idc1'] / 1000) / $step_w * $vert_px; // kW
			if ($lastpdc1 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $pdc, $red);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastpdc1, $xpos, $height - $gap - $pdc, $red);
			}
			$lastpdc1 = $pdc;

			$pdc = ($row['udc2'] * $row['idc2'] / 1000) / $step_w * $vert_px; // kW
			if ($lastpdc2 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $pdc, $green);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastpdc2, $xpos, $height - $gap - $pdc, $green);
			}
			$lastpdc2 = $pdc;

			$pdc = ($row['udc3'] * $row['idc3'] / 1000) / $step_w * $vert_px; // kW
			if ($lastpdc3 == 0) {
				imagesetpixel($image, $xpos, $height - $gap - $pdc, $blue);
			} else {
				imageline($image, $lastxpos, $height - $gap - $lastpdc3, $xpos, $height - $gap - $pdc, $blue);
			}
			$lastpdc3 = $pdc;
		}

		$lastxpos = $xpos;
	}
	imagefilledrectangle($image, $width - 200, 0,  $width - 120, 20, $white);
	imagefttext($image, 10, 0, $width - 200, 10, $black, $fontfile, $kdylast . " kWh");

	imagepng($image, $image_name);
	imagedestroy($image);

	return '<p>' . $GLOBALS["graphday1".$GLOBALS['lang']] . '</p>';
}
?>
