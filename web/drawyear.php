<?php
	/*
		Simple solarmax visualizer php program written by zagibu@gmx.ch in July 2010
		This program was originally licensed under WTFPL 2 http://sam.zoy.org/wtfpl/
		Improvements by Frank Lassowski flassowski@gmx.de in August 2010
		This program is now licensed under GPLv2 or later http://www.gnu.org/licenses/gpl2.html
	*/

	function draw_year($start, $end, $image_name, $table, $fontfile, $show_text) {

		// Create a date array
		$date = getdate(strtotime($end));

		// Select daily energy for each day of year
		// If you are experiencing problems with the value 'kdy' especially the days very first 'kdy' is equal to the last one of the previous day comment in the next line and comment out the following one. Be aware: This is !!!untested!!! Don't slap me if something's going wrong!
		// $result = @mysql_query("SELECT MONTH($table.created) AS month, kmt FROM $table, (SELECT MAX(created) AS created FROM $table WHERE YEAR(created)=$date[year] GROUP BY MONTH(created)) AS sublog WHERE $table.created=sublog.created AND YEAR($table.created)=$date[year]") or die(mysql_error());
		$result = @mysql_query("SELECT MONTH(created) AS month, MAX(kmt) AS kmt FROM $table WHERE YEAR(created)=$date[year] GROUP BY MONTH(created)") or die(mysql_error());

		if (mysql_num_rows($result) == 0)
		{
			// No data...create dummy image
			$image = imagecreatetruecolor(10, 10);
			$white = imagecolorallocate($image, 255, 255, 255);
			imagefill($image, 0, 0, $white);
			imagepng($image, $image_name);
			imagedestroy($image);
			return $GLOBALS["error3".$GLOBALS['lang']] . '<br />';
		}

		// include monthly predictions
		include 'solarertrag_month_predictions.php';

		// Initialize kdy image
		// Pixel per month determines bar width
		$px_per_month = 64;
		// Width = months * px per month + gap
		$width = $date['mon'] * $px_per_month + 52;
		$maxkwh = 2200;
		// How many kWh per diagram line
		$step_w = 200;
		// How many px per diagram line (W/px = $step_w / $vert_px)
		$vert_px = 40;
		// Height = number of lines * px per line + px per line (for 0-line) + gap
		$gap = 60;
		$height = $maxkwh / $step_w * $vert_px + $gap;
		// Create image, prepare colors and set background to white
		$image = imagecreatetruecolor($width, $height);
		// get the colors
		include 'colors.php';
		imagefill($image, 0, 0, $white);

		if (preg_match('/gridyear/', $show_text)) {
			// Draw horizontal lines with some space above and below
			for ($i = 0; $i <= $maxkwh / $step_w; $i++) {
				// Create horizontal grid line
				$ypos = $height - $i * $vert_px - $gap + 10;
				imageline($image, 12, $ypos, $width - 35, $ypos, $gray);
				// Draw the kWh value at the end of the horizontal line
				imagefttext($image, 8, 0, $width - 30, $ypos + 4, $black, $fontfile, $i * $step_w);
			}
			// Draw vertical lines with some space at the left and right
			for ($i = 1; $i <= $date['mon']; $i++) {
				// Create vertical grid line
				$xpos = $i * $px_per_month;
				imageline($image, $xpos, 5, $xpos, $height - $gap + 15, $gray);
				// Draw the hour value at the end of the vertical line
				imagefttext($image, 8, 0, $xpos - 4, $height - $gap + 28, $black, $fontfile, $i);
			}
		}
		// Draw kdy values
		while($row = mysql_fetch_assoc($result)) {
			// Determine x position
			$xpos = $row['month'] * $px_per_month - $px_per_month / 2;
			// Transform kWh to pixel height
			$kwh = $row['kmt'] / $step_w * $vert_px;

			// Draw kWh bar and prediction line
			$pred_month = ${'m_'.date('m', mktime(0, 0, 0, $row['month'], 1, 0))};
			$pred = $pred_month / $step_w * $vert_px;
			imagefilledrectangle($image, $xpos + 2, $height - $gap + 10, $xpos + $px_per_month - 2, $height - $gap - $kwh + 10, $green);
			imageline($image, $xpos, $height - $pred - $gap + 10, $xpos + $px_per_month, $height - $pred - $gap + 10, $blue);
			if (preg_match('/numbersyear/', $show_text)) {
				imagefttext($image, 12, 90, $xpos + 31, $height - $gap - $kwh + 4, $black, $fontfile, $row['kmt']);
				imagefttext($image, 7, 0, $xpos + 45, $height - $gap - $pred + 8, $blue, $fontfile, $pred_month);
				if (preg_match('/percent/', $show_text)) {
					imagefttext($image, 6 , 90, $xpos + 42, $height - $gap - $kwh + 10, $black, $fontfile, "(".round($row['kmt'] / $pred_month,3) *100 ." %)");
				}
			}
		}

		//explain colored lines
		imageline($image, $width - ($width * 2 / 4) - 55, $height - 15, $width - ($width * 2 / 4) - 70, $height - 15, $blue);
		imagefttext($image, 7, 0, $width - ($width * 2 / 4) - 40, $height - 12, $black, $fontfile, $GLOBALS["graphyear2".$GLOBALS['lang']]);
		imagepng($image, $image_name);
		imagedestroy($image);
		return '<p>' . $GLOBALS["graphyear1".$GLOBALS['lang']] . '</p>';
	}
?>
