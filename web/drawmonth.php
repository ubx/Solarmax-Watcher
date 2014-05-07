<?php
	/*
		Simple solarmax visualizer php program written by zagibu@gmx.ch in July 2010
		This program was originally licensed under WTFPL 2 http://sam.zoy.org/wtfpl/
		Improvements by Frank Lassowski flassowski@gmx.de in August 2010
		This program is now licensed under GPLv2 or later http://www.gnu.org/licenses/gpl2.html
	*/

	function draw_month($start, $end, $pred_day, $image_name, $table, $fontfile, $show_text) {

		// Create a date array
		$date = getdate(strtotime($end));

		// Select daily energy for each day of month
		// If you are experiencing problems with the value 'kdy' especially the days very first 'kdy' is equal to the last one of the previous day comment in the next line and comment out the following one. Be aware: This is !!!untested!!! Don't slap me if something's going wrong!
		// $result = @mysql_query("SELECT DAY($table.created) AS day, kdy FROM $table, (SELECT MAX(created) AS created FROM $table WHERE MONTH(created)=$date[mon] AND YEAR(created)=$date[year] GROUP BY DAYOFYEAR(created)) AS sublog WHERE $table.created=sublog.created AND MONTH($table.created)=$date[mon] AND YEAR($table.created)=$date[year]") or die(mysql_error());
		$result = @mysql_query("SELECT DAY(created) AS day, MAX(kdy) AS kdy FROM $table WHERE MONTH(created)=$date[mon] AND YEAR(created)=$date[year] GROUP BY DAYOFYEAR(created)") or die(mysql_error());

		if (mysql_num_rows($result) == 0) {
			// No data...create dummy image
			$image = imagecreatetruecolor(10, 10);
			$white = imagecolorallocate($image, 255, 255, 255);
			imagefill($image, 0, 0, $white);
			imagepng($image, $image_name);
			imagedestroy($image);
			return $GLOBALS["error3".$GLOBALS['lang']] . '<br />';
		}

		// Initialize kdy image
		$sum=0;
		// Pixel per day determines bar width
		$px_per_day = 25;
		// Width = days * px per day + gap
		$width = $date['mday'] * $px_per_day + 45;
		$maxkwh = 100;
		// How many kWh per diagram line
		$step_w = 10;
		// How many px per diagram line (W/px = $step_w / $vert_px)
		$vert_px = 30;
		// Height = number of lines * px per line + px per line (for 0-line) + gap
		$gap = 72;
		$height = $maxkwh / $step_w * $vert_px + $gap;
		// Create image, prepare colors and set background to white
		$image = imagecreatetruecolor($width, $height);
		// get the colors
		include 'colors.php';
		imagefill($image, 0, 0, $white);

		if (preg_match('/gridmonth/', $show_text)) {
			// Draw horizontal lines with some space above and below
			for ($i = 0; $i <= $maxkwh / $step_w; $i++) {
				// Create horizontal grid line
				$ypos = $height - $i * $vert_px - $gap + 22;
				imageline($image, 12, $ypos, $width - 35, $ypos, $gray);
				// Draw the kWh value at the end of the horizontal line
				imagefttext($image, 8, 0, $width - 30, $ypos + 4, $black, $fontfile, $i * $step_w);
			}
			// Draw vertical lines with some space at the left and right
			for ($i = 1; $i <= $date['mday']; $i++) {
				// Create vertical grid line
				$xpos = $i * $px_per_day;
				imageline($image, $xpos, 5, $xpos, $height - $gap + 27, $gray);
				// Draw the hour value at the end of the vertical line
				imagefttext($image, 8, 0, $xpos - 4, $height - $gap + 40, $black, $fontfile, $i);
			}
		}

		// Draw kdy values
		$days = 0;
		$totalkwh = 0;
		$lasttotalkwh = 0;
		while($row = mysql_fetch_assoc($result)) {
			// Determine x position
			$xpos = $row['day'] * $px_per_day - $px_per_day / 2;
			$lastxpos = $xpos;
			// Transform kWh to pixel height
			$kwh = $row['kdy'] / 10 / $step_w * $vert_px;
			$days++;
			$sum = $sum + $row['kdy'] / 10;

			// Draw acumulated yield
			$totalkwh = $totalkwh + $kwh*0.1;
			if ($lasttotalkwh == 0) {
				imagesetpixel($image, $xpos, $height - $gap + 22 - $totalkwh, $black);
			} else {
				imageline($image, $lastxpos - $px_per_day/2, ($height - $gap + 22 - $lasttotalkwh), $xpos + $px_per_day/2, ($height - $gap + 22 - $totalkwh), $black);
			}
			$lasttotalkwh = $totalkwh;

			// Draw kWh bar
			imagefilledrectangle($image, $xpos + 2, $height - $gap + 22, $xpos + $px_per_day - 2, $height - $gap - $kwh + 22, $green);
			if (preg_match('/numbersmonth/', $show_text)) {
				imagefttext($image, 12, 90, $xpos + 19, $height - $gap - $kwh + 17, $black, $fontfile, $row['kdy']/10);
			}
		}

		// Draw prediction line
		if (preg_match('/predmonth/', $show_text)) {
			$pred = $pred_day / $step_w * $vert_px;
			imageline($image, 12, $height - $pred - $gap + 22, $width - 35, $height - $pred - $gap + 22, $blue);
			imageline($image, $width - ($width * 3 / 4) -10, $height - 15, $width - ($width * 3 / 4) + 5, $height - 15, $blue);
			imagefttext($image, 7, 0, $width - ($width * 3 / 4) + 20, $height - 12, $black, $fontfile, $GLOBALS["graphmonth2".$GLOBALS['lang']]);
		}

		// Draw average line
		if (preg_match('/avg/', $show_text)) {
			$avg =$sum / $days / $step_w * $vert_px;
			imageline($image, 12, $height - $avg - $gap + 22, $width - 35, $height - $avg - $gap + 22, $yellow);
			imageline($image, $width - ($width * 2 / 4) - 5, $height - 15, $width - ($width * 2 / 4) +10, $height - 15, $yellow);
			imagefttext($image, 7, 0, $width - ($width * 2 / 4) +25, $height - 12, $black, $fontfile, $GLOBALS["graphmonth3".$GLOBALS['lang']]);
		}

		imagepng($image, $image_name);
		imagedestroy($image);
		return '<p>' . $GLOBALS["graphmonth1".$GLOBALS['lang']] . '</p>';
	}
?>
