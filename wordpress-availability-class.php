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

	// HEADER

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
	body {padding-top: 30px; }
	.website { color: red; }
	.status6 { color: green; }
	.black { color: black; }
	/* Sortable tables */
	table.sortable thead {
	    background-color:#eee;
	    color:#666666;
	    font-weight: bold;
	    cursor: default;
	}

	table td,table th { padding:1px 10px; text-align:center;}
	table td.alignLeft { text-align:left; }
    </style>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-responsive.min.css" rel="stylesheet">

    <!-- scripts -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.2/underscore-min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.2.1/bootstrap.min.js"></script>
    <script src="https://www.kryogenix.org/code/browser/sorttable/sorttable.js"></script>
  </head>
  <body>

    <div class="container">
<h2>Wordpress Availability Check - '.date("Y-m-d h:i:s").'</h2>
';


//start table
$this->output.='<table class="sortable">
<thead><tr>
<th>Domain</th>
<th>DNS domain</th>
<th>DNS www.domain</th>
<th>Website up</th>
<th>Has wordpress</th>
<th>WP-Admin accessible</th>
<th>WP-admin display login</th>
<th>Check time</th>
<th>Status</th>
</tr></thead><tbody>';
	foreach ($websiteList as $site){
		$this->check(trim(strtolower($site)));
	}

//end table
$this->output.='</tbody></table>';


	//FOOTER
	$this->output.='
    </div> <!-- /container -->
  </body>
</html>';

    }


    function check($site){

	$status=0;

	$this->site_report = "<td class='alignLeft'><h4>".$site."</h4></td>";

	$status += $this->checkDNS($site);

        $status += $this->checkDNS("www.".$site);

	$webData = $this->isSiteAvailable($site,"website");

	if ($webData){
		$status++;

		$status += $this->findTheme($webData);

	        $adminData = $this->isSiteAvailable($site."/wp-admin","admin");

		if ($adminData){
			$status++;

			$status += $this->findLoginButton($adminData);
		}  else { $this->site_report .= '<td>ERROR</td>'; }

	} else { $this->site_report .= '<td>ERROR</td>'; }


	$this->output .= '<tr class="website status'.$status.'">';
	$this->output .= $this->site_report;
        $this->output .= "<td class='black'>".date("Y-m-d h:i:s")."</td>";
	$this->output .= '<td>'.$status.'</td>';
	$this->output .= '</tr>';

    }

    function checkDNS($site){
	$ip=gethostbyname($site);
	if ($site==$ip){
		$this->site_report .=  "<td>ERROR</td>";
		return false;
	} else {
		$this->site_report .=  "<td><span class='black'>".$ip."</span></td>";
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
		$this->site_report .=  "<td><span class='black'>ok</span></td>";
		return $response;
	} elseif ($responseInfo['http_code']=="301" && strtolower($responseInfo['redirect_url'])!='http://www.'.strtolower($url).'/'){
              	$this->site_report .=  "<td>Warning - website redirected to ".$responseInfo['redirect_url']."</td>";
		return true;
	} else {
		$this->site_report .=  "<td>ERROR</td>";
		return false;
	}

     }

     function findTheme($content){
	$pattern='/wp-content\/themes/';
	if (preg_match($pattern,$content)){
		$this->site_report .=  "<td><span class='black'>ok</span></td>";
		return true;
	} else {
		$this->site_report .=  "<td>ERROR</td>";
		return false;
	}
     }

     function findLoginButton($content){
        $pattern='/forgetmenot/';
        if (preg_match($pattern,$content)){
                $this->site_report .=  "<td><span class='black'>ok</span></td>";
                return true;
        } else {
                $this->site_report .=  "<td>ERROR</td>";
                return false;
        } 
     }




}

?>
