<?php
class wbUtil extends Object
{
    public static function &jsonDecode($data){

        if (empty($data)){
            $x = array();
            return $x;
        }
        
        $items = json_decode($data, true);
        
        if ($items == NULL){
            throw new Exception('JSON items could not be decoded');
        }

        return $items;
    }

    public static function checkUploadedImage($file){
        if (empty($file['name'])) throw new Exception("Nama file upload kosong");
        
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Kemungkinan percobaan penyerangan sistem melalui upload");
        }
        
        if (empty($file['type']) || empty($file['tmp_name']) || empty($file['size']) || $file['error'] != 0){
             throw new Exception("File yang anda upload tidak valid. Mohon periksa kembali");
        }
        
        $path = strtolower(strrchr($file['name'], '.'));
        if(($path!='.jpeg') && ($path!='.jpg') && ($path!='.gif') && ($path!='.png')){
        	throw new Exception("Tipe file image '".$file['type']."' tidak diperkenankan. Mohon upload file image dengan tipe 'jpg', 'gif', 'png' atau 'bmp'");
        }
        
        return true;
    }

    public static function createThumbnailImage ($gdver, $src, $newFile1, $maxw='' ,$maxh='') {
    	if (!function_exists ("imagecreate") || !function_exists ("imagecreatetruecolor")) {
    		throw new Exception("No Image Create Functions");
    	}
    	$size = @getimagesize ($src);
    	if (!$size) {
    		throw new Exception("Image File Not Found");
    	} else {
    		
    		if($size[0] > $maxw){
    		
    			$imgratio= $size[0]/$size[1]; 
    			if($imgratio>1) {
    					$newx=$maxw;
    					$newy=$maxw/$imgratio;
    			} else {
    					$newy=$maxw;       
    					$newx=$maxw*$imgratio;
    			}
    		}else{
    		    copy($src, $newFile1);
    		    return;
    		}
    		if ($gdver == 1) {
    			$destimg =  imagecreate ($newx, $newy );
    		} else {
    			$destimg = @imagecreatetruecolor ($newx, $newy );
    			
    			if (!$destimg) throw new Exception("Cannot Use GD2");
    		}
    		if ($size[2] == 1) {
    			if (!function_exists ("imagecreatefromgif")) {
    				throw new Exception("Cannot Handle GIF Format");
    			} else {
    				$sourceimg = imagecreatefromgif ($src);
    				if ($gdver == 1){
    					imagecopyresized ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
    				}else{
    					$r = @imagecopyresampled ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
    					
    					if (!$r) throw new Exception("Cannot use GD2");
                    }
    				imagegif ($destimg, $newFile1);
    			}
    		}elseif ($size[2]==2) {
    			$sourceimg = imagecreatefromjpeg ($src);
    			if ($gdver == 1){
    				imagecopyresized ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
    			}else{
    				$r = @imagecopyresampled ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
    			    if (!$r) throw new Exception("Cannot use GD2");
    			}
    			imagejpeg ($destimg, $newFile1);
    			
    		}elseif ($size[2] == 3) {
    			$sourceimg = imagecreatefrompng ($src);
    			if ($gdver == 1){
    				imagecopyresized ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
    			}else{
    				$r = @imagecopyresampled ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
    			    if (!$r) throw new Exception("Cannot use GD2 here!");
    			}
    			imagepng ($destimg, $newFile1);
    		}else {
    			throw new Exception("Image Type Not Handled");
    		}
    	}
    	imagedestroy ($destimg);
    	imagedestroy ($sourceimg);
    }
    
    public static function createErrorImage ($text, $maxw = 0) {
        $len = 	strlen($text);
        if($maxw < 100) $errw = 100;
        $errh = 	25;
        $chrlen = 	intval (4 * $len);
        $offset = 	intval (($errw - $chrlen) / 2);
        $im = 	imagecreate ($errw, $errh); /* Create a blank image */
        $bgc = 	imagecolorallocate ($im, 153, 63, 63);
        $tc = 	imagecolorallocate ($im, 255, 255, 255);
        imagefilledrectangle ($im, 0, 0, $errw, $errh, $bgc);
        imagestring ($im, 2, $offset, 7, $text, $tc);
        imagejpeg ($im);
        imagedestroy ($im);
        exit;
    }    
    
    public static function startExcel($filename = "laporan.xls") {
    
	   header("Content-type: application/vnd.ms-excel");
	   header("Content-Disposition: attachment; filename=$filename");
	   header("Expires: 0");
	   header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
	   header("Pragma: public");
	    
	}
	
	public static function startDoc($filename = "laporan.doc") {
	    
	   header("Content-type: application/vnd.ms-word");
	   header("Content-Disposition: attachment; filename=$filename");
	   header("Expires: 0");
	   header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
	   header("Pragma: public");
	    
	}
	
	public static function convertDate($date){
        
    	$date = trim($date);
    	if (empty($date))    
    	    return NULL;
    	
    	$pieces = explode('-', $date);
    	if (count($pieces) != 3)
    	    return NULL;
    	
    	return $pieces[2].'-'.$pieces[1].'-'.$pieces[0];    
	}
	
	public static function dateToString($date){
		if(empty($date)) return "";
		
    	$monthname = array(0  => '-',
    	                   1  => 'Januari',
    	                   2  => 'Februari',
    	                   3  => 'Maret',
    	                   4  => 'April',
    	                   5  => 'Mei',
    	                   6  => 'Juni',
    	                   7  => 'Juli',
    	                   8  => 'Agustus',
    	                   9  => 'September',
    	                   10 => 'Oktober',
    	                   11 => 'November',
    	                   12 => 'Desember');    
    	
    	$pieces = explode('-', $date);
    	
    	return $pieces[2].' '.$monthname[(int)$pieces[1]].' '.$pieces[0];
	}

    public static function dateTimeToString($datetime){
        $pieces = explode(' ', $datetime);
        
        return self::dateToString($pieces[0]).' '.$pieces[1];
    }

	public static function numberFormat($number, $decimals =  2, $dec_point = ',' , $thousands_sep = '.'){
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }
    
    /**
     * compareDate
     *
     * Melakukan perbandingan dua tanggal
     *
     * @param string $from_date : batas tanggal awal dalam format yyyy-mm-dd
     * @param string $to_date   : batas tanggal akhir dalam format yyyy-mm-dd
     * @return 1 jika from_date < to_date, 2 jika from_date > to_date, 3 jika from_date = to_date
     */
    public static function compareDate($from_date, $to_date) {
        $from_date = trim($from_date);
        $to_date = trim($to_date);
        
        if ($from_date == $to_date){
            return 3;
        }
        
        $waktu_awal = explode("-",$from_date);
        $waktu_akhir = explode("-",$to_date);
        if ($waktu_awal[0] > $waktu_akhir[0]){
            return 2;
        }else if ($waktu_awal[0] == $waktu_akhir[0]){
            if ($waktu_awal[1] > $waktu_akhir[1]){
                return 2;
            }else if ($waktu_awal[1] == $waktu_akhir[1]){
                if ($waktu_awal[2] > $waktu_akhir[2]){
                    return 2;
                }
            }
        }
        return 1;
    }    
    
    public static function maxDay($bulan, $tahun) {
    	$max_day = array(31,28,31,30,31,30,31,31,30,31,30,31);
    	if($tahun % 4 == 0 || $tahun % 100 == 0) {
    		$max_day[1] = 29;	
    	}
    	
    	return $max_day[$bulan-1];
    }
    
	public static function week_from_monday($date) {
	    // Assuming $date is in format DD-MM-YYYY
	    list($day, $month, $year) = explode("-", $date);
	
	    // Get the weekday of the given date
	    $wkday = date('l',mktime('0','0','0', $month, $day, $year));
	
	    switch($wkday) {
	        case 'Monday': $numDaysToMon = 0; break;
	        case 'Tuesday': $numDaysToMon = 1; break;
	        case 'Wednesday': $numDaysToMon = 2; break;
	        case 'Thursday': $numDaysToMon = 3; break;
	        case 'Friday': $numDaysToMon = 4; break;
	        case 'Saturday': $numDaysToMon = 5; break;
	        case 'Sunday': $numDaysToMon = 6; break;   
	    }
	
	    // Timestamp of the monday for that week
	    $monday = mktime('0','0','0', $month, $day-$numDaysToMon, $year);
	
	    $seconds_in_a_day = 86400;
	
	    // Get date for 7 days from Monday (inclusive)
	    for($i=0; $i < 7; $i++) {
	        $dates[$i] = date('Y-m-d',$monday+($seconds_in_a_day*$i));
			$just_in_date = $dates[$i];
			$item = explode("-", $just_in_date);
			
			if($item[1] != $month) {
				unset($dates[$i]);
			}
	    }
		
	    return array_values($dates);
	}
	
	public static function ddValue($date) {
		list($yyyy,$mm,$dd) = explode("-",$date);
		return (int)$dd;	
	}
	
	public static function rangeDaysInWeek($date) {
		
		$days = self::week_from_monday($date);
		$result = array();
		if(count($days) > 0) {
			if(count($days) != 1) {
				$result['min'] = self::ddValue( $days[0] );
				$result['max'] = self::ddValue( $days[count($days)-1] );	
			}else {
				$result['min'] = self::ddValue( $days[0] );
				$result['max'] = self::ddValue( $days[0] );
			}
		}
		return $result;
	}
	
	public static function weeksInMonth($month, $year) {
		
		$num_weeks = 1 + ceil( ( date( "t", mktime( 0, 0, 0, $month, 1, $year ) )
	    				- ( 8 - date( "w", mktime( 0, 0, 0, $month, 1, $year ) ) )
	    				) / 7 );
		return $num_weeks;
	}
	
	public static function getMonth($month) {
		$months = array("Jan", "Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");	
		return $months[$month-1];
	}
	
	public static function getMonthNames($month) {
		$months = array("Januari", "Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");	
		return $months[$month-1];
	}
	
	public static function getMonthFull($month) {
		$months = array("January", "February","March","April","May","June","July","August","September","October","November","December");	
		return $months[$month-1];
	}
	
	public static function toDate($d,$m,$yyyy) {
		if($d < 10)
			$d = "0".$d;
		if($m < 10)
			$m = "0".$m;
		
		return $d."-".$m."-".$yyyy;		
	}
	
	public static function debugvar($var) {
		echo "<pre>";
			print_r($var);
		echo "</pre>";
		exit;	
	}
	
	
	/**
	* isBetweenDate
	* Mengetahui apakah suatu tanggal berada dalam range suatu periode
	* String $compared_date. format yyyy-mm-dd
	* String $from_date. format yyyy-mm-dd
	* String $to_date. format yyyy-mm-dd
	*
	*/
	public function isBetweenDate($compared_date, $from_date, $to_date){
    	if(self::compareDate($compared_date, $from_date) == 2 && self::compareDate($compared_date, $to_date) == 1){
    	 return true;
    	}else{
    	 return false;
    	}
    }

    function isValidEmail ($email)
    {
        if (!eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$', $email)) {
            return false;
        }

        return true;
    }
    
    /**
     * validateDate
     * fungsi untuk memvalidasi nilai tanggal yang diberikan
     *
     * @author agung.hp
     * @since 09/10/2005 20:40
     * @param string date dengan format dd-mm-yyyy
     * @return boolean true atau false
     */
    function validateDate($date){
        // 1. check empty
        $date = trim($date);
        if (empty($date))
            return false;

        // 2. check length
        if (strlen($date) < 8){
            return false;
        }
        // 3. check num index
        $pieces = explode('-', $date);
        if (count($pieces) != 3)
            return false;

        $month = $pieces[1];
        $day = $pieces[0];
        $year = $pieces[2];

        // 4. check data type
        if (!is_numeric($month) || !is_numeric($day) || !is_numeric($year))
            return false;

        // 5. check date value
        if (!checkdate($month, $day, $year))
            return false;

        // valid beneran gitu lohh....
        return true;
    }
    
    function isValidTime($time){
		$pieces = explode(':', $time);
			
		if (count($pieces) != 2) return false;
			
		$pieces[0] = (int) $pieces[0];
		$pieces[1] = (int) $pieces[1];

		if (!is_int($pieces[0]) && !is_int($pieces[1])) return false;
						
		if ($pieces[0] < 0 || $pieces[0] > 23) return false;
			
		if ($pieces[1] < 0 || $pieces[1] > 59) return false;
    	
    	return true;
    }

    function sendmail($args){
        require_once('lib/phpmailer/class.phpmailer.php');

        $mail = new PHPMailer(); // defaults to using php "mail()"

        try {

          $mail->SetFrom($args['from'], $args['from_name']);
          $mail->AddReplyTo($args['from'], $args['from_name']);
          $mail->AddAddress($args['to']);


          $mail->Subject = $args['subject'];

          $mail->MsgHTML($args['body']);
          $mail->Send();
        } catch (phpmailerException $e) {
          throw new Exception($e->errorMessage()); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
          throw $e;
        }

        return true;

    }
    
    public function isSessionTimeOut() {
        
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 180)) {
            
            // last request was more than 30 minutes ago
            session_unset();     // unset $_SESSION variable for the run-time 
            session_destroy();   // destroy session data in storage
            
            return true;
        }
        return false;
    }
    
}

function debugvar($var){
    wbUtil::debugvar($var);
}

function numericToString($nilai){
  
  $checkSprtrThousand = explode(".",(number_format($nilai, 0, chr(44), ".")));
  $a = count($checkSprtrThousand)-1;
  $sayValue = "";
  $b=0;
  while($a >= 0)
    {
		
		$b++;
		
		//cari satuan per separator ribuan
    $chars = preg_split('//', $checkSprtrThousand[$a], -1, PREG_SPLIT_NO_EMPTY);
		$x = count($chars)-1;
		$y=0;
		
		switch(count($chars))
			{
				case "1" : $getValue = getOne($chars); 
				           break;
				case "2" : $getValue = getTwo($chars); 
				           break;
				case "3" : $getValue = getThree($chars);
				           break;
			}
    if(!empty($getValue))
        {
        	switch($b)
						{
							case	"2"	:	$getValue=$getValue." Ribu ";
											if($getValue=="Satu Ribu "){$getValue="Seribu ";}
											break;
							case	"3"	:	$getValue=$getValue." Juta ";
											if($getValue=="Satu Juta "){$getValue="Sejuta ";}
											break;
							case	"4"	:	$getValue=$getValue." Milyar ";break;
							case	"5"	:	$getValue=$getValue." Trilyun ";break;
						}
				}
		$sayValue = $getValue.$sayValue;
    $a = $a-1;
        }//endwhile($a < count($checkSprtrThousand))
  return $sayValue;
}

function getOne($chars)
{
	$getValue="";
	$sayNumber = getNumber($chars[0]);
	$getValue = $getValue.$sayNumber;
	return $getValue;
}


//function for converting if the group of thousand separator is two digits
function getTwo($chars)
{
	$getValue="";
	//cek apakah belasan
	if($chars[0] == 1)
		{
			if($chars[1] > 0)
			{
				$sayNumber = getNumber($chars[1]);
				$sayNumber = $sayNumber." Belas ";
				if($sayNumber == "Satu Belas "){$sayNumber="Sebelas ";}
				$getValue = $getValue.$sayNumber;
			}
			else{
				$sayNumber = "Sepuluh";
				$getValue  = $getValue.$sayNumber;
			}
		}
	   else
			{
				if($chars[1] > 0)
					{
						$sayNumber = getNumber($chars[1]);
						$getValue = $getValue.$sayNumber;
					}
				if($chars[0] > 0)
					{
						$sayNumber = getNumber($chars[0]);
						$sayNumber = $sayNumber." Puluh ";
						if($sayNumber=="Satu Puluh "){$sayNumber="Sepuluh ";}
						$getValue = $sayNumber.$getValue;
					}
			}
	return $getValue;
}

//function for converting if the group of thousand separator is three
function getThree($chars)
{
	$getValue="";
	//cek apakah belasan
	if($chars[1]==1)
		{
			if($chars[2] > 0)
			{
				$sayNumber = getNumber($chars[2]);
				$sayNumber = $sayNumber." Belas ";
				if($sayNumber == "Satu Belas "){$sayNumber="Sebelas ";}
				$getValue = $getValue.$sayNumber;
			}
			else{
				$sayNumber = "Sepuluh";
				$getValue  = $getValue.$sayNumber;
			}
		}
	   else
			{
				if($chars[2] <> 0)
					{
						$sayNumber = getNumber($chars[2]);
						$getValue = $getValue.$sayNumber;
					}
				if($chars[1] <> 0)
					{
						$sayNumber = getNumber($chars[1]);
						$sayNumber = $sayNumber." Puluh ";
						if($sayNumber=="Satu Puluh "){$sayNumber="Sepuluh ";}
						$getValue = $sayNumber.$getValue;
					}
			}
    if($chars[0] <> 0)
        {
            $sayNumber = getNumber($chars[0]);
            $sayNumber = $sayNumber." Ratus ";
			if($sayNumber=="Satu Ratus "){$sayNumber="Seratus ";}
            $getValue = $sayNumber.$getValue;
        }
	return $getValue;
}

//function for converting from hexadecimal number to phrase
function getNumber($number){
    
    switch($number)
       {
          case "0" : $sayNumber = "Nol";break;
          case "1" : $sayNumber = "Satu";break;
          case "2" : $sayNumber = "Dua";break;
          case "3" : $sayNumber = "Tiga";break;
          case "4" : $sayNumber = "Empat";break;
          case "5" : $sayNumber = "Lima";break;
          case "6" : $sayNumber = "Enam";break;
          case "7" : $sayNumber = "Tujuh";break;
          case "8" : $sayNumber = "Delapan";break;
          case "9" : $sayNumber = "Sembilan";break;
        }
    return $sayNumber;
}

function get_ip_address() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple ips exist in var
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (validate_ip($ip))
                    return $ip;
            }
        } else {
            if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];

    // return unreliable ip since all else failed
    return $_SERVER['REMOTE_ADDR'];
}
