<?php
/**
 * @package apps
 * @subpackage Help
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 12/03/2009
 */
class helpWindow extends page 
{
	public $type; // The type of item (application, snapin, form etc)
	public $application; // The name of the application or snapin
	public $animation; // The name of the SWF file.
	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('Help');
		
		if(isset($_REQUEST['app']) && $_REQUEST['app'] != "" && isset($_REQUEST['type']) && $_REQUEST['type'] != "")
		{
			$this->type = $_REQUEST['type'];
			$this->application = $_REQUEST['app'];
			
			// Grabs info from database
			$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM help WHERE type = '" . $this->type . "' AND app = '" . $this->application . "';");
			$fields = mysql_fetch_array($dataset);
			
			// Check to see if the SWF in current language exists, and views it
			//	If not then  checks for the english version and views it.
			// if still not then outouts a simple message
			if(file_exists("./apps/help/flash/" . $this->application . "/" . $this->application . "_" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLanguage() . ".swf"))
			{
				$this->animation = $this->application . "_" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLanguage() . ".swf";
			}
			elseif(file_exists("./apps/help/flash/" .$this->type ."/" . $this->application . "/" . $this->application . "_ENGLISH.swf"))
			{
				$this->animation = $this->application . "_ENGLISH.swf";
			}
			else
			{
				$this->animation = false;
			}
			
		
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 //EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Scapa Intranet - Help</title>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"/>
		<link rel="stylesheet" href="/css/defaultSmall.css"/>
		<link rel="Shortcut Icon" href="/favicon.ico"/>
		<link rel="stylesheet" href="/css/dev.css"/>
	</head>
	
	<body onload="Javascript:window.resizeTo(800,<? echo $fields['swfYsize'] + 180 < 400 ?  540 : $fields['swfYsize'] + 180; ?>);">	
	
		<div class="snapin" id="snapin_{@area}|{@class}">
		
			<div class="snapin_top"><div class="snapin_top_3">			
				<p style="margin: 0; font-weight: bold; color: #FFFFFF;">
					<?
						echo translate::getInstance()->translate($this->application); 
						
						if($this->animation)
						{
					?>
						(<a href="#" class="helpWindow" title="<? echo translate::getInstance()->translate("click_to_fit_window_to_animation"); ?>" onclick="Javascript:window.resizeTo(800,<? echo $fields['swfYsize'] + 180 ?>);"><? echo translate::getInstance()->translate("resize_window"); ?></a>)
					<?
						}
					?>					
				</p>
			</div></div>
			<div class="snapin_content"><div class="snapin_content_3">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="50%" valign="top" style="padding-right:7px; padding-left:7px;">
							<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
								<table width="100%" height="20px">
									<tr>
										<td align="center">
											<strong>
												<? 
													echo translate::getInstance()->translate("description"); 
												?>
									 		</strong>
								 		</td>
								 	</tr>
							 	</table>
							</div></div></div></div>
						</td>
						<td width="50%" valign="top" style="padding-right:7px; padding-left:7px;">
							<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
								<table width="100%" height="20px">
									<tr>
										<td align="center">
											<strong>
												<?
													echo translate::getInstance()->translate("animation"); 
												?>
										 	</strong>
										 </td>
									</tr>
								</table>
							</div></div></div></div>
						</td>
					</tr>
					<tr>
						<td align="center" valign="top" style="padding-top:5px; padding-right:20px;padding-left: 20px">
							<table cellpadding="5px"  height="400px">
								<tr valign="top">
									<td align="left">
										<? 
											// checks for translation in the HELP table, defaults to English if not found.
											if($fields[usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLanguage()] == "")
											{
												$split = explode("\r\n", $fields['ENGLISH']);
										
												for ($i=0; $i < count($split); $i++)
												{
													echo $split[$i] . "<br />";
												}
											}
											else 
											{
												$split = explode("\r\n", $fields[usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLanguage()]);

												for ($i=0; $i < count($split); $i++)
												{
													echo $split[$i] . "<br />";
												}
											}
											
											if($this->type == "snapin" && $this->application != "snapin")
											{
												echo "<br /><br />";
												echo translate::getInstance()->translate("more_on_snapins") . ",";
												?>
													<a href="./helpWindow?type=snapin&app=snapin">
												<?
												echo translate::getInstance()->translate("click_here");
												?>
													</a>
												<?
											}
										?>
	
									</td>
								</tr>
							</table>
						</td>
						<td  valign="top" style="background: url(/images/dotted_background.gif) repeat-y top left; padding-top:5px" align="center">
							<table cellpadding="10px" cellspacing="10">
								<tr>
									<?
										if($this->animation)
										{
											?>
												<td bgcolor="White" style="border: 1px Black Solid;">
													<embed src="../flash/<? echo $this->type ?>/<? echo $this->application ?>/<? echo $this->animation ?>" name='detectme' height="<? echo $fields['swfYsize']+1 ?>" width="<? echo $fields['swfXsize']+1 ?>" align="middle" />
												</td>
											<?
										}
										else
										{
											?>
												<td bgcolor="White" style="border: 1px Black Solid;">
													<? echo translate::getInstance()->translate("no_animation_available"); ?>
												</td>
											<?
										}
										?>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<br />
			</div></div>
		</div>		
	</body>
</html>

<?
		}
		else 
		{
			echo translate::getInstance()->translate("help_not_found");
		}
			
	}
}

?>