<?php
set_time_limit(0);
// TODO: MAKE GRAPHICAL / PRETTIER
// TODO: MAYBE ADD SCREENSHOTS http://www.phpgang.com/how-to-capture-website-screenshot-using-php-phantomjs_1117.html 

class Availability {

/*    var $output = "text";

    public function setOutput($output) { 
        $this->output = $output; 
    }
*/

    // MUST FEED ARRAY WITH WEBSITE LIST
    function parse($websiteList) {
	foreach ($websiteList as $site){
		$this->check(trim(strtolower($site)));
	}
    }


    function check($site){


	echo $site;
        echo "<br />\n";

	$this->checkDNS($site);

        $this->checkDNS("www.".$site);
        echo "<br />\n";

	$webData = $this->isSiteAvailable($site,"website");
        echo "<br />\n";

	if ($webData){

		$this->findTheme($webData);
	        echo "<br />\n";

	        $adminData = $this->isSiteAvailable($site."/wp-admin","admin");
	        echo "<br />\n";

		if ($adminData){
			$this->findLoginButton($adminData);
		        echo "<br />\n";
		}

	}

        echo "<br />\n";
	flush();
    }

    function checkDNS($site){
	$ip=gethostbyname($site);
	if ($site==$ip){
		echo " ERROR - DNS failed ".$site;
		return false;
	} else {
		echo " DNS ok - $site - ".$ip;
		return true;
	}
    }

    function isSiteAvailable($url,$tag){
	$cl = curl_init($url);
	curl_setopt($cl,CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($cl,CURLOPT_HEADER,true);
	curl_setopt($cl,CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($cl,CURLOPT_AUTOREFERER, true );

	$response = curl_exec($cl);
	$responseInfo = curl_getinfo($cl);

	curl_close($cl);

	if ($response) {
		echo " $tag seems up - code ".$responseInfo['http_code'];
		if ($responseInfo['http_code']=="301" && strtolower($responseInfo['redirect_url'])!='http://www.'.strtolower($url).'/'){
		        echo "<br />\n";
			echo " Warning - website redirected to ".$responseInfo['redirect_url'];
		}

		return $response;

	} else {
		echo " ERROR - $tag is down";
		return false;
	}

     }

     function findTheme($content){
	$pattern='/wp-content\/themes/';
	if (preg_match($pattern,$content)){
		echo " website has wordpress installed";
		return true;
	} else {
		echo " ERROR - no wordpress theme found";
		return false;
	}
     }

     function findLoginButton($content){
        $pattern='/forgetmenot/';
        if (preg_match($pattern,$content)){
                echo " admin page found";
                return true;
        } else {
                echo " ERROR - admin page not found";
                return false;
        } 
     }




}

?>
