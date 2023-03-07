<?php
// Designed by Jason Matthews c.11/12/2008
// Enhanced by David Pickwell c.18/12/2008
// Version 2!

// Start Session
session_start();

if(isset($_GET['string']))
{
	unset($_SESSION['string']);
}

if(isset($_GET['fieldName']))
{
	unset($_SESSION['fieldName']);
}

if(!isset($_SESSION['string']) || $_SESSION['string'] == NULL)
{		
	$_SESSION['string'] = str_replace("@_@","+",urldecode($_GET['string']));
}
else 
{	
	$_SESSION['string'] = $_SESSION['string'];
}

if(!isset($_SESSION['fieldName']) || $_SESSION['fieldName'] == NULL)
{		
	$_SESSION['fieldName'] = str_replace("@_@","+",urldecode($_GET['fieldName']));
}
else 
{	
	$_SESSION['fieldName'] = $_SESSION['fieldName'];
}

$string = $_SESSION['string'];

// Check Spelling
function SpellCheck($string)
{
    $pspell_link = pspell_new("en");
    
    preg_match_all("/[A-Z\']{1,16}/i", $string, $words);

    for ($i = 0; $i < count($words[0]); $i++) {

        if (!pspell_check($pspell_link, $words[0][$i])) 
        {
            //$string = str_replace($words[0][$i], "<a href='spellCheck.php?word=" . $words[0][$i] . "' onclick=''><font color=\"#FF0000\">" . $words[0][$i] . "</font></a>", $string); 
            $string = str_replace($words[0][$i], "<font color=\"#FF0000\">" . $words[0][$i] . "</font>", $string);
        } 
    }

    return $string;

}

// Replace incorrect words.
function ReplaceSpellings($string)
{
	$pspell_link = pspell_new("en");
    
    preg_match_all("/[A-Z\']{1,16}/i", $string, $words);

    for ($i = 0; $i < count($words[0]); $i++) {

        if (!pspell_check($pspell_link, $words[0][$i])) 
        {
            if(isset($_GET['correction' . $i]) != "" && $_GET['correction' . $i] != "")
            {
            	$string = str_replace($words[0][$i], "<strong>" . $_GET['correction' . $i] . "</strong>", $string);
            		
            }
            else
            {
            	$string = str_replace($words[0][$i], "<em>" . $words[0][$i] . "</em>", $string);
            }
        }
    }

    return $string;
}

// Show suggestions for incorrect spellings.
function SpellCheckSuggestions($string)
{
	$f = 0;
	
	$pspell_link = pspell_new("en");
    
    preg_match_all("/[A-Z\']{1,16}/i", $string, $words);
    
    for ($i = 0; $i < count($words[0]); $i++)
    {
        if (!pspell_check($pspell_link, $words[0][$i])) 
        {
            $suggestions = pspell_suggest($pspell_link, $words[0][$i]);

		    echo "Incorrect Word: <strong>" . $words[0][$i] . "</strong><br /><br />";
		    
            echo "<select style='width: 100px;' onChange=\"document.spellCheck.correction$i.value = URLDecode(this.value);\">";
            echo "<option value=''>Please Select</option>";
            
		    foreach ($suggestions as $suggestion) 
		    {
		        echo "<option value='" . urlencode($suggestion) . "'>$suggestion</option>";
		    }
		    
		    echo "</select> <input type='text' name='correction$i' size='14'>";
		    
		    echo "<hr />";
		    
		    $f++;
        }
    }
    
    if($f == 0)
    {
    	echo "Spell Check Complete.  No Errors Found.<br /><br />";
    }
}

?>

<html>
<head>
<title>Scapa Spell Check</title>
<link rel="stylesheet" href="/css/default.css" />
<script language="javascript" src="/javascript/spellCheck.js">-</script>
</head>

<body>
<h1>Scapa Spell Check</h1>
<p>Original Text to Spell Check is shown below.</p>
<table width="100%" cellpadding="5" cellspacing="2" style="background-color: #DFDFDF;">
<tr>
	<td>
<?php

	if($string == "")
	{
		echo "This textbox is empty.<br />";
	}
	
	echo "<strong>" . SpellCheck($string) . "</strong>";
	
	if(!isset($_GET['correct']))
	{
?>
	</td>
</tr>
</table>

<form id="spellCheck" name="spellCheck" action="/controls/spellCheck/spellCheck.php">
<table width="100%" cellpadding="2" cellspacing="2" style="background-color: #F5F5F5;">
<tr>
	<td>
		
		<?php 

			echo SpellCheckSuggestions($string);
			
		?>
		
	<?php 
	
		if($string != "")
		{
			echo "<input type='submit' id='correct' name='correct' value='Correct' />";
		}
	?>
	
	<input type="hidden" value="correction" />
	
	<input type="button" onclick="window.close()" value="Close" />
	
	</td>
</tr>
</table>

</form>

<?php

	}
	
	if(isset($_GET['correct']) && $_GET['correct'] == "Correct")
	{
		echo "<br /><br />Corrected words are in <strong>Bold</strong>.<br />None Corrected words are in <em>Italic.</em><br /><br />";
		
		echo ReplaceSpellings($string);
		
		// This removes the used html tags, as strip_tags removes all the <> and text between. Not good!
		$removeWords = array("<strong>","</strong>","<em>","</em>");
		$returnString = str_replace($removeWords, "", ReplaceSpellings($string));
		
		echo "<br /><br /><input type='submit' onclick=\"javascript:finishSpellCheck('" . urlencode($returnString) . "','" . $_SESSION['fieldName'] . "')\" value='Apply and Close' />";
		
		//echo "<br /><br /><a href=\"#\" onclick=\"javascript:finishSpellCheck('" . strip_tags(ReplaceSpellings($string)) . "','" . $_SESSION['fieldName'] . "')\">Apply and Close</a>";
			
	}
?>

</body>

</html>