<?php
	header('Content-type: text/html; charset=utf-8');
	setlocale(LC_TIME, 'en_IN.UTF8');
	date_default_timezone_set('Asia/Kolkata');

	// CONNECT TO DATABASE
	
	/* REGIONS */
	// $result = mysqli_query($db, 'SELECT id,country,region,meta FROM `holiday_countries` ORDER BY country, region');
	$regionNames = array();
	$regionIDs = array();
	$tArray = array();
	$allDays = array();
	$regionMeta = array();
	
	/* DATE PREPARATIONS */
	// http://php.net/manual/en/function.date.php
	$today = date('Y-m-d');
	
	$requYMD = $today; // makes it first of month
	$startpage = true;
	if(isset($_GET['m']))
    {
		$requYMD = preg_replace("/[^0-9\-]/i", '', $_GET['m']).'-01';
		$startpage = false;
	}
	// block hack, required yyyy-mm-dd
	if(strlen($requYMD)!=10)
    {
		exit();
	}
	
	// get current month
	$curMonthTS = strtotime($requYMD); // add 4 hours 
	$monthNr = date('n', $curMonthTS); // numeric representation of current month, without leading zeros
	// echo strftime('%s %H:%M:%S %z %Z %a., %d. %B %Y', $curMonthTS);
	
	// http://stackoverflow.com/questions/13346395/php-array-containing-all-dates-of-the-current-month
	// number of days in the given month
	$num_of_days = date('t', $curMonthTS);
	$x_year = date('Y', $curMonthTS);
	$x_month = date('m', $curMonthTS);
	for($i=1; $i<=$num_of_days; $i++) 
	{
		$dates[]= $x_year."-".$x_month."-".str_pad($i,2,'0', STR_PAD_LEFT);
	}
	
	// fill Arrays with data
	
		// create all days in month as array entries
		$d = 1; // id starts with 1, we dont have an id==0
		while($d <= $num_of_days) {
			$allDays[$d] = ' ';
			 echo $d++;
		}
	
	
	
	
	/* OUTPUT function */
	function getAllHolidays() 
    {
		global $dates;
		global $regionNames;
		global $regionIDs; // IDs of all regions
		global $tArray; // contains all holiday periods for each region
		global $allDays;
		global $regionMeta;
		global $today;
		global $requYMD;
		global $curMonthTS;
		global $monthNr;
		global $num_of_days;
		$allMetas = array();
		
		$output = '
	<table id="table_1" class="bordered">
	<tr>
		<th style="text-align:left !important;background:#FFD !important;">
		<span style="display:none;">Holidays in </span>'.strftime('%B %Y', $curMonthTS).'
		</th>
	';
		
		// all number days of current month
		foreach($dates as $day) {
			// set id for today to color the column, but only if showing this month
			$cssToday = '';
			if($day == $today && substr($today,5,2)==$monthNr) {
				$cssToday = ' class="today" title="Der heutige Tag!"';
			}
			// format 2013-10-01 to 01 and remove if necessary the 0 by ltrim
			$output .= '<th'.$cssToday.'>'.ltrim( substr($day,8,2) , '0').'</th>'; // alternative: output $day and let JS convert the day to weekday
		}
	$regionTerm = ('$countryCode'=='ch') ? 'Kantone' : 'Bundesländer';
	$output .= '
	</tr>
	
	<tr class="weekdays"><td><span style="display:none;">'.$regionTerm.'</span></td>';
		$wdaysMonth = array();
		// week days
		$i = 1;
		foreach($dates as $day) {
			// echo '<td>'.date('D', strtotime($day)).'</td>';
			$weekdayName = strftime('%a', strtotime($day));
			$wkendcss = '';
			$todayWDcss = '';
			//if($weekdayName=='Sa' || $weekdayName=='So'){
			if($day == $today) {
				$todayWDcss = 'class="activeday"';
			}
			else if($weekdayName=='So'){
				$wkendcss = 'class="wkend"';
			}
			// write day date in array field
			$wdaysMonth[$i++] = strftime('%A %e. %B %Y', strtotime($day));
			$output .= '<td '.$todayWDcss.$wkendcss.' title="'.strftime( '%A %e. %B %Y', strtotime($day) ).'">'.$weekdayName.'</td>';
		}
		
	$hasData = false;
	$output .= '
	</tr>
	';

		
			

			$k = 0;
			foreach($allDays as $day) 
            {
				$k++;
				if($day=='x') 
                {
					$output .= '<td class="free" title="'.$wdaysMonth[$k].'<br />'.$allMetas[$id][$k].'">'.$day.'</td>';
				}
				else 
                {
					$output .= '<td>'.$day.'</td>';
				}
			}
			$output .= '</tr>
			';
		
		
		$output .= '</table>';
		
		
		return $output;
	}
	
	// $mnthyear = strftime('%b %G', $curMonthTS);
	$mnthyear = strftime('%b %Y', $curMonthTS);
	
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="author" content="Not me" />
	<meta name="robots" content="index,follow" />
	
	<title>Hoirzontal calendar</title>
	
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400" type="text/css" />
	<link rel="stylesheet" href="styles.css" type="text/css" />
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="/js/jquery/3.2.1/jquery.min.js"><\/script>')</script>
	
	<script type="text/javascript" src="script.js"></script>

</head>

<body class="holidaysm">

	<div id="nav">
		
		<div id="fastaccess">
		<?php
			// LIST Nav Buttons first or last 6 months of year - according to recent date year
			$requYear = substr($requYMD,0,4);
			$requMonth = substr($requYMD,5,2);
			$monthr = $requMonth<7 ? 1 : 7;
			$timestamp = $requYear.'-'.$monthr;
			
			$monthOut = array();
			$c = 0;
			$monthOut[$c][0] = date('Y-m', strtotime($timestamp));
			$monthOut[$c++][1] = strftime('%b %Y', strtotime($timestamp));
			$monthOut[$c][0] = date('Y-m', strtotime($timestamp.' +1 month')); // next month
			$monthOut[$c++][1] = strftime('%b %Y', strtotime($timestamp.' +1 month'));
			$monthOut[$c][0] = date('Y-m', strtotime($timestamp.' +2 month'));
			$monthOut[$c++][1] = strftime('%b %Y', strtotime($timestamp.' +2 month'));
			$monthOut[$c][0] = date('Y-m', strtotime($timestamp.' +3 month'));
			$monthOut[$c++][1] = strftime('%b %Y', strtotime($timestamp.' +3 month'));
			$monthOut[$c][0] = date('Y-m', strtotime($timestamp.' +4 month'));
			$monthOut[$c++][1] = strftime('%b %Y', strtotime($timestamp.' +4 month'));
			$monthOut[$c][0] = date('Y-m', strtotime($timestamp.' +5 month'));
			$monthOut[$c++][1] = strftime('%b %Y', strtotime($timestamp.' +5 month'));
			$c_out = 0;
			
		?>
			<a class="navpre" title="previous month" href="?m=<?php echo date('Y-m', strtotime($requYMD.' -1 month')); ?>">&laquo;</a> 
			<a <?php echo (substr($requYMD,0,7)==$monthOut[$c_out][0])? 'class="oranged" ' : '' ?>href="?m=<?php echo $monthOut[$c_out][0]; ?>"><?php echo $monthOut[$c_out++][1]; ?></a> 
			<a <?php echo (substr($requYMD,0,7)==$monthOut[$c_out][0])? 'class="oranged" ' : '' ?>href="?m=<?php echo $monthOut[$c_out][0]; ?>"><?php echo $monthOut[$c_out++][1]; ?></a> 
			<a <?php echo (substr($requYMD,0,7)==$monthOut[$c_out][0])? 'class="oranged" ' : '' ?>href="?m=<?php echo $monthOut[$c_out][0]; ?>"><?php echo $monthOut[$c_out++][1]; ?></a> 
			<a <?php echo (substr($requYMD,0,7)==$monthOut[$c_out][0])? 'class="oranged" ' : '' ?>href="?m=<?php echo $monthOut[$c_out][0]; ?>"><?php echo $monthOut[$c_out++][1]; ?></a> 
			<a <?php echo (substr($requYMD,0,7)==$monthOut[$c_out][0])? 'class="oranged" ' : '' ?>href="?m=<?php echo $monthOut[$c_out][0]; ?>"><?php echo $monthOut[$c_out++][1]; ?></a> 
			<a <?php echo (substr($requYMD,0,7)==$monthOut[$c_out][0])? 'class="oranged" ' : '' ?>href="?m=<?php echo $monthOut[$c_out][0]; ?>"><?php echo $monthOut[$c_out++][1]; ?></a> 
			<a class="navfwd" title="next month" href="?m=<?php echo date('Y-m', strtotime($requYMD.' +1 month')); ?>">&raquo;</a> 
			
			<a id="datepickbtn">Calender <input id="datepicker" name="request" type="text" value="<?php echo substr($requYMD,0,7); ?>" /></a>
			
		</div>
		
		<br />
		<div id="flags">
			<span style="padding-right:10px;">Anzeigen:</span>
			<div title="Deutschland" id="setDE" class="germany"></div> 
			<div title="Österreich" id="setAT" class="austria"></div> 
			<div title="Schweiz" id="setCH" class="swiss1">
			<div class="swiss2"></div>
			</div> 
		</div>

		<div id="calholdr">
			<div class="calendar"><?php echo substr($today,8,2); ?><em><?php echo strftime('%b %Y', strtotime($today)); ?></em></div>
			<div id="clock"></div>
		</div>
		
	</div>
		
	<div id="main">
	
		<div class="tabcont t_de">
			<h1>attendance (<?php echo $mnthyear; ?>)</h1>
			<?php
				// output holiday table for Germany
				echo getAllHolidays('de');
			?>
		</div> 

	</div>
	
</body>
</html>