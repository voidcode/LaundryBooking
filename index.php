<?php 
session_start();
if(!isset($_GET["date"]))
{
	header('Location: ?date='.date('Y-m-d'));
}
/*************************************************************************************************************/
/*************************************************************************************************************/
/*************************************************************************************************************/
$HOST  = "localhost";
$USER  = "root";
$PASS  = "";
$DB    = "LaundryBooking";//do not alter this
$TABLE = "maskiner";//do not alter this
$mysqli = new mysqli($HOST, $USER, $PASS, $DB);
date_default_timezone_set('Europe/Copenhagen');// set default timezone****************************************/
/*************************************************************************************************************/
/*************************************************************************************************************/
/*************************************************************************************************************/
/*-- Table structure for table `maskiner` SQL:

CREATE TABLE IF NOT EXISTS `maskiner` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `data` text COLLATE utf32_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf32 COLLATE=utf32_bin AUTO_INCREMENT=212 ;

**************************************************************************************************************/

$SQL = "SELECT * FROM ".$TABLE." WHERE date='".$mysqli->real_escape_string($_GET["date"])."' LIMIT 0 , 1";
if($rs = $mysqli->query($SQL))
{
	if($rs->num_rows==0) //insert an emtry day, if is current date is not in db
	{
		$stmt = $mysqli->prepare("INSERT INTO ".$TABLE." (`id`, `date`, `data`)
			VALUES (NULL, '".(isset($_GET["date"]) ? $_GET["date"]:date("Y-m-d"))."', '||||||||');
		");
		$stmt->execute();
		$stmt->close();
	}
}
?>
<!doctype="html">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scaleable=no"> 
		<title>LaundryBooking</title>
		<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
		<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
		<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
			
	</head>
	<body>
		<style>
		</style>
		<div data-role="page" data-theme="a">
			<div data-role="header" data-theme="a">
				<a rel="external" href="?date=<?php echo date('Y-m-d', strtotime($_GET['date'].'-1 day')); ?>" data-role="button"><?php echo date('d-m-Y', strtotime($_GET['date'].'-1 day')); ?></a>
				<h1><a onMouseOver="this.style.textDecoration='underline'" onMouseOut="this.style.textDecoration='none'" rel="external" style="color:#ffffff; text-decoration: none;" href="?date=<?php echo $_GET['date']; ?>"><?php echo date('d-m-Y', strtotime($_GET['date'])); ?></a></h1>
				<a rel="external" href="?date=<?php echo date('Y-m-d', strtotime($_GET['date'].'+1 day')); ?>" data-role="button"><?php echo date('d-m-Y', strtotime($_GET['date'].'+1 day')); ?></a>
			</div>
			<div data-role="content" data-theme="c" id="content">
				<div data-role="fieldcontain" id="page0">
					<form action="" method="post" data-ajax="false">
					<h1>LaundryBooking</h1>
					<p>BALLERUP SPORTS KOLLEGIE</p>
					<p><i>MAX 1 Reservation per week, per apartment</i></p>
					<hr/>
					<fieldset data-role="controlgroup">
						<legend>Booking</legend>
						<label for="user-adress">Address</label>
						<input type="hidden" name="date" value="<?php echo $_GET['date']; ?>"/>
						<div data-type="horizontal" data-role="controlgroup" data-inline="true" >
							<input data-inline="true" required="required" type="text" name="user_adress" id="user-adress" value="<?php echo $_POST['user_adress']; ?>" placeholder="Your address" <?php echo (isset($_POST['user_adress'])) ? 'disabled="disabled"':""; ?> autofocus/>
							<?php if(!isset($_POST['user_adress']))
								echo '<input data-inline="true" type="submit" data-role="button" name="submit_user_adress" value="Choose time" data-theme="b"/>';
							?>
						</div>
					</fieldset>
					</form>
				</div>	
				<!---------------------------------------------------------------------------------------------------------->
				<div data-role="fieldcontain" id="page1">
					<?php
						$timeslots = array("06.30-08.30", "08.30-10.30", "10.30-12.30", "12.30-14.30", "14.30-16.30", "16.30-18.30", "18.30-20.30", "20.30-22.30", "22.30-00.30");
						if(isset($_POST["submit_user_adress"]) && $_POST["user_adress"] !="")
						{	
							$_POST["user_adress"] = strtolower($_POST["user_adress"]);
							$_SESSION["user_adress"] = $_POST["user_adress"];
							$SQL = "SELECT * FROM ".$TABLE."
									WHERE date='".$mysqli->real_escape_string($_POST["date"])."'
									LIMIT 0 , 1";
							if($rs = $mysqli->query($SQL) or die($mysqli->error.__LINE__))
							{
								while($row = $rs->fetch_assoc()) 
								{
									echo '<form action="" method="post" data-ajax="false">';
									echo '<fieldset data-role="controlgroup">';
									echo '<legend>Booking date: '.date('d-m-Y', strtotime($_POST["date"])).'</legend>';
									$i=0;
									$ShowSaveBtn = true;
									foreach(explode("|", $row["data"]) as $item)
									{
										if(trim($item)==trim($_POST["user_adress"]))
										{
											$ShowSaveBtn = false;
											echo '<input value="'.$i.'" data-theme="b" type="radio" name="radio_choice" id="radio-choice-'.$i.'" checked/>';
												echo '<label for="radio-choice-'.$i.'">'.$timeslots[$i].' (reserve by you)
													<!--<a href="" style="float:right;color:#fff;text-decoration:none;" onMouseOver="this.style.textDecoration=\'underline\'" onMouseOut="this.style.textDecoration=\'none\'" id="cancelbooking">Cancel Booking</a>-->
												</label>';
											
										}
										else if(trim($item) == "")//free to book an slot 
										{
											echo '<input value="'.$i.'" type="radio" name="radio_choice" id="radio-choice-'.$i.'"/>';											echo '<label for="radio-choice-'.$i.'">'.$timeslots[$i].'</label>';
										}
										else // if($ShowSaveBtn)//this slot is book by an user 
										{
												echo '<input disabled="disabled" data-theme="a" value="" type="radio" name="radio_choice" id="radio-choice-'.$i.'"/>';
												echo '<label for="radio-choice-'.$i.'">'.$timeslots[$i].' (reserve by: '.trim($item).')</label>';
										}
										$i++;
									}
									echo '</fieldset>';
									echo '<br/>';
									echo '<input type="hidden" name="id" value="'.$row["id"].'"/>';
									echo '<input type="hidden" name="data" value="'.$row["data"].'"/>';
									echo '<input type="hidden" name="user_adress" value="'.$_POST["user_adress"].'"/>';
									echo '<hr/>';
									echo '<input type="submit" name="savebooking" data-role="button" data-theme="a" value="Save your booking" '.(($ShowSaveBtn==false) ? 'disabled="disabled"':"").'/>';
									
									echo '</form>';
								}
								
							}
							$result->close();
						}
/*savebooking------------------------------------------------------------------------------------------------------------------------*/
						if(isset($_POST["savebooking"]) || isset($_POST["cancelbooking"]))
						{	
							$i=0;
							$sqlDataRowBuilder='';
							foreach(explode("|", $_POST["data"]) as $item)
							{
								if($i==$_POST["radio_choice"])
								{
									$sqlDataRowBuilder .= $_POST["user_adress"].'|';
								}
								else
									$sqlDataRowBuilder .= trim($item).'|'; 
								$i++;
							}
							$stmt = $mysqli->prepare("UPDATE ".$TABLE." 
							SET `data` = '".substr($sqlDataRowBuilder, 0, -1)."'
							WHERE id =".$_POST["id"].";
							");
							$stmt->execute();
							$stmt->close();
							//echo "<u>Your booking is now saved!</u>";
						}
					?>
				</div>	
				<script>
				$(function(){
					$("#page1").hide();
					$("#btnPicktime").on("click", function(e){
						$("#page1").show();
					});
				});
				</script>
			</div>
		</div>
	</body>
</html>
