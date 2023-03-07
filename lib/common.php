<?php

require "email.php";
require "emailAttachment.php";
require "mysql.php";
require "mysqlExt.php"; // Added By Jason Matthews - For querying external site.
require "mysqlKayako.php"; // Added By Jason Matthews - For querying Kayako Support Suite
require "control.php";
require "user.php";
require "usercache.php";
require "cache.php";
require "currentuser.php";
require "translate.php";
require "session.php";
require "charts.php";
require "sapClass.php";
require "sapClassCustomers.php";
require "sapcache.php";
require "sapcachecustomers.php";
require "chatcache.php";
require "chatClass.php";
require "generalMath.php";
require "discovery.php";
require "discoverycache.php";
require "charts/calculateTrend.php";
require "fusionChartsCache.php";


class common
{
    public static function getIntranetServerIP()
    {
    	return "10.1.50.11"; // SI IP Address
    }

    public static function getIntranetServerHostname()
    {
    	return "ukdunapp006"; // SI Hostname
    	//return "10.1.50.11";
    }

    public static function getMainDC()
	{
		return "10.1.199.11"; // UKDUNDC001
	}

	public static function getBackupDC()
	{
		return "10.14.199.11"; // UKASHDCF011
    }

	public static function getRoot()
    {
            return $_SERVER["DOCUMENT_ROOT"];
    }

    public static function getTime()
    {
            $mtime = microtime();
            $mtime = explode(" ",$mtime);
            $mtime = $mtime[1] + $mtime[0];

            return $mtime;
    }

    /**
	 * Return a month name from the month number
	 *
	 * @param int $monthNumber (1,2,3,4,5,6,7,etc)
	 * @return string (January, etc)
	 */
	public static function getMonthNameByNumber($monthNumber)
	{
		$monthName = date("F", strtotime(date("Y") . "-" . $monthNumber . "-" . "01"));

		return $monthName;
    }

    public static function isValidEmailAddress($emailAddress)
    {
    	// Check email address is in correct format
    	if(eregi("^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3})$", $emailAddress))
    	{
			return true;
		}
		else
		{
			return false;
		}

    }

	public static function transformDateForMYSQL($date)
    {
            $dateArray = explode("/",$date);                //array stores day as [0], month as [1], year as [2]
            if (count($dateArray) == 3)
            {
                    return date("Y-m-d",mktime(0,0,0,$dateArray[1],$dateArray[0],$dateArray[2]));
            }
    }

    public static function transformDateTimeToDateForPHP($dateTime)
	{
		return date("d/m/Y", strtotime($dateTime));
	}

	public static function transformDateTimeToSpecialDateForPHP($dateTime)
	{
		return date("jS M H:i", strtotime($dateTime));
	}

	public static function transformDateForPHP($date)
	{
		$dateArray = explode("-", $date);

		if ($date == "0000-00-00" || $date == "1999-11-30") // Needed as some fields are not entered when they should be
		{
			$date = "";
			return $date;
		}
		else
		{
			if (count($dateArray) == 3)
			{
				return date("d/m/Y", mktime(0,0,0,$dateArray[1],$dateArray[2],$dateArray[0]));
			}
			else
			{
				return $date;
			}
		}
	}


    public static function transformDateTimeForPHP($date)
    {
        $items = explode(" ", $date);

		if($date == null)
		{
			// do nothing
		}
		else
		{
			return page::transformDateForPHP($items[0]) . " " . $items[1];
        }
    }

	public static function nowDateForMysql()
    {
        return date("Y-m-d");
    }


    public static function nowDateTimeForMysql()
    {
        return date("Y-m-d H:i:s");
    }

    public static function nowDateForPHP()
    {
        return date("d/m/Y");
    }

    public static function nowDateTimeForMysqlMinusTwentyDays()
	{
		$minusTwentyDays = time() - (24 * 60 * 60 * 20);

		return date("Y-m-d", $minusTwentyDays);
	}

	public static function nowDateTimeForMysqlPlusTwentyDaysDateOnly()
	{
		$plusTwentyDays = time() + (24 * 60 * 60 * 20);

		return date("Y-m-d", $plusTwentyDays);
    }

    public static function nowDateTimeForMysqlPlusOneDay()
    {
        $plusOneDay = time() + (24 * 60 * 60);

        return date("Y-m-d H:i:s", $plusOneDay);
    }

    public static function nowDateTimeForMysqlPlusTenDays()
    {
        $plusTenDays = time() + (24 * 60 * 60 * 10);

        return date("Y-m-d H:i:s", $plusTenDays);
    }

	public static function getTimeDifference($start, $end)
    {
        $uts['start'] = strtotime($start);
        $uts['end'] = strtotime($end);
        if($uts['start']!==-1 && $uts['end']!==-1 )
        {
            if( $uts['end'] >= $uts['start'] )
            {
                $diff = $uts['end'] - $uts['start'];
                if($days=intval((floor($diff/86400))))
                    $diff = $diff % 86400;
                if($hours=intval((floor($diff/3600))))
                    $diff = $diff % 3600;
                if($minutes=intval((floor($diff/60))))
                    $diff = $diff % 60;
                $diff = intval( $diff );

                return(array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff));
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
	}

	// USA Mail Server
	public static function getUSAMailHost()
	{
			//return "10.40.1.229";
			return "10.18.198.4";
	}

	public static function getUSAMailUsername()
	{
			return "apache";
	}

	public static function getUSAMailPassword()
	{
			return "";
	}

	// UK Mail Server
	public static function getMailHost()
	{
			//return "10.1.199.21";
			return "10.18.198.4";
	}

	public static function getMailUsername()
	{
			return "Apache";
	}

	public static function getMailPassword()
	{
			return "";
    }

    // Scapa AP (Asia) Mail Server - mail.scapa-ap.com
    public static function getAPMailHost()
    {
            //return "mail.scapa-ap.com"; // 202.75.39.238
			return "10.18.198.4";
    }

    public static function getAPMailUsername()
    {
            return "jasonmatthews@scapa-ap.com";
    }

    public static function getAPMailPassword()
    {
            return "";
    }

    // Scapa CN (China) Mail Server - mail.scapatapes.com.cn
    public static function getCNMailHost()
    {
            return "mail.scapatapes.com.cn"; // 210.72.224.2
    }

    public static function getCNMailUsername()
    {
            return "jasonmatthews@scapatapes.com.cn";
    }

    public static function getCNMailPassword()
    {
            return "";
    }

	// wrapper, to be used in scripts that may be used via command line
    public static function addDebug($debug, $file, $line)
    {
        if (!isset($GLOBALS['isCommandLine']))
        {
                page::addDebug($debug, $file, $line);
        }
    }


    public static function error($friendlyMessage, $adminMessage, $file, $line)
    {
        if (isset($GLOBALS['isCommandLine']))
        {
                die ($friendlyMessage);
        }
        else
        {
                page::error($friendlyMessage, $adminMessage, $file, $line);
        }
    }

    /**
	 * Hit Counter for the homepage
	 * Not unique hits but total hits
	 */
	public function hitCounter($app)
	{
		// If admin do not count, otherwise add hit to counter
		if(!currentuser::getInstance()->hasPermission("admin"))
		{
			// Insert data to database
			mysql::getInstance()->selectDatabase("intranet")->Execute("INSERT INTO hitCounter (NTLogon, hitDate, app) VALUES ('" . currentuser::getInstance()->getNTLogon() . "','" . common::nowDateForMysql() . "','" . $app . "')");
		}
	}
}
