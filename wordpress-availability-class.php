<?php
set_time_limit(0);
// TODO: MAKE GRAPHICAL / PRETTIER
// TODO: MAYBE ADD SCREENSHOTS http://www.phpgang.com/how-to-capture-website-screenshot-using-php-phantomjs_1117.html 

class Availability {

    var $output = "";
    var $site_report ='';


    public function getOutput() { 
        return $this->output;
    }



/*
    public function setOutput($output) { 
        $this->output = $output; 
    }
*/

    // MUST FEED ARRAY WITH WEBSITE LIST
    function parse($websiteList) {


	$this->output.='
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Wordpress availability checker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- styles -->
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap.min.css" rel="stylesheet">
    <style> 
	body {padding-top: 60px; }
	.website { color: red; }
	.status6 { color: green; }
	.black { color: black; }
	h2 { line-height:10px; padding-bottom:0px; margin-bottom:0px; }
    </style>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-responsive.min.css" rel="stylesheet">

    <!-- scripts -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.2/underscore-min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.2.1/bootstrap.min.js"></script>
    <script src="https://www.kryogenix.org/code/browser/sorttable/sorttable.js"></script>
  </head>
  <body>

    <div class="container">';

	foreach ($websiteList as $site){
		$this->check(trim(strtolower($site)));
	}

	$this->output.='
    </div> <!-- /container -->

  </body>
</html>';

    }


    function check($site){

	$status=0;

	$this->site_report = "<h2>".$site."</h2>";
        $this->site_report .= "<br />\n";

	$status += $this->checkDNS($site);

        $status += $this->checkDNS("www.".$site);
        $this->site_report .=  "<br />\n";

	$webData = $this->isSiteAvailable($site,"website");
        $this->site_report .=  "<br />\n";

	if ($webData){
		$status++;

		$status += $this->findTheme($webData);
	        $this->site_report .=  "<br />\n";

	        $adminData = $this->isSiteAvailable($site."/wp-admin","admin");
	        $this->site_report .=  "<br />\n";

		if ($adminData){
			$status++;

			$status += $this->findLoginButton($adminData);
		        $this->site_report .=  "<br />\n";
		}

	}


	$this->output .= '<div class="website status'.$status.'">';
	$this->output .= $this->site_report;
	$this->output .= '</div>';
        $this->output .=  "<br />\n";

    }

    function checkDNS($site){
	$ip=gethostbyname($site);
	if ($site==$ip){
		$this->site_report .=  " ERROR - DNS failed ".$site;
		return false;
	} else {
		$this->site_report .=  "<span class='black'>DNS ok - $site - ".$ip."</span>&nbsp;";
		return true;
	}
    }

    function isSiteAvailable($url,$tag){
	$cl = curl_init();
	curl_setopt($cl,CURLOPT_URL, $url);
	curl_setopt($cl,CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($cl,CURLOPT_HEADER,true);
	curl_setopt($cl,CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($cl,CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($cl,CURLOPT_AUTOREFERER, true );

	$response = curl_exec($cl);
	$responseInfo = curl_getinfo($cl);

	curl_close($cl);

	if ($response && $responseInfo['http_code']=="200" ) {
		$this->site_report .=  "<span class='black'>$tag seems up</span>";
		return $response;
	} elseif ($responseInfo['http_code']=="301" && strtolower($responseInfo['redirect_url'])!='http://www.'.strtolower($url).'/'){
                $this->site_report .=  "<br />\n";
              	$this->site_report .=  " Warning - website redirected to ".$responseInfo['redirect_url'];
		return true;
	} else {
		$this->site_report .=  " ERROR - $tag is down";
		return false;
	}

     }

     function findTheme($content){
	$pattern='/wp-content\/themes/';
	if (preg_match($pattern,$content)){
		$this->site_report .=  "<span class='black'>website has wordpress installed</span>";
		return true;
	} else {
		$this->site_report .=  " ERROR - no wordpress theme found";
		return false;
	}
     }

     function findLoginButton($content){
        $pattern='/forgetmenot/';
        if (preg_match($pattern,$content)){
                $this->site_report .=  "<span class='black'>admin page found</span>";
                return true;
        } else {
                $this->site_report .=  " ERROR - admin page not found";
                return false;
        } 
     }




}

?>
