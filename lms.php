<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);
header('Content-Type: text/html; charset=utf-8');
session_start();
?>
<html>
<head>
  <title>IMS Learning Tools Interoperability</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body style="font-family:sans-serif;">
<p><b>IMS LTI 1.1 Consumer Launch</b></p>
<p>This is a very simple reference implementation of the 
LMS side (i.e. consumer) for 
<a href="http://developers.imsglobal.org/" target="_blank">IMS Learning 
Tools Interoperability</a>.</p>
<?php
require_once("lib/lti_util.php");

    $cur_url = curPageURL();

    $lmsdata = array(
      "resource_link_id" => "120988f929-274612",
      "resource_link_title" => "Weekly Blog",
      "resource_link_description" => "A weekly blog.",
      "user_id" => "292832126",
      "roles" => "Instructor",  // or Learner
      "lis_person_name_full" => 'Jane Q. Public',
      "lis_person_name_family" => 'Public',
      "lis_person_name_given" => 'Given',
      "lis_person_contact_email_primary" => "user@ischool.edu",
      "lis_person_sourcedid" => "ischool.edu:user",
      "context_id" => "456434513",
      "context_label" => "SI106",
      "context_title" => "Introduction to Programming",
      "tool_consumer_info_product_family_code" => "ims",
      "tool_consumer_info_version" => "1.1",
      "tool_consumer_instance_guid" => "lmsng.ischool.edu",
      "tool_consumer_instance_description" => "University of Information",
      "custom_assn" => "free",
	  "custom_due" => "2016-12-12 10:00:00.5",
	  // http://www.php.net/manual/en/timezones.php
	  "custom_timezone" => "Pacific/Honolulu",
	  "custom_penalty_time" => "" . 60*60*24,
	  "custom_penalty_cost" => "0.2"
      // 'launch_presentation_return_url' => $cur_url
      );

  foreach ($lmsdata as $k => $val ) {
      if ( $_POST[$k] && strlen($_POST[$k]) > 0 ) {
          $lmsdata[$k] = $_POST[$k];
      }
  }

  $key = trim($_REQUEST["key"]);
  if ( ! $key ) $key = "12345";
  $secret = trim($_REQUEST["secret"]);
  if ( ! $secret ) $secret = "secret";
  $endpoint = trim($_REQUEST["endpoint"]);
  $b64 = base64_encode($key.":::".$secret);
  if ( ! $endpoint ) $endpoint = str_replace("lms.php","lti.php",$cur_url);
  $cssurl = str_replace("lms.php","lms.css",$cur_url);

  $outcomes = trim($_REQUEST["outcomes"]);
  if ( ! $outcomes ) {
      $outcomes = str_replace("lms.php","common/tool_consumer_outcome.php",$cur_url);
      $outcomes .= "?b64=" . htmlentities($b64);
  }

  $tool_consumer_instance_guid = $lmsdata['tool_consumer_instance_guid'];
  $tool_consumer_instance_description = $lmsdata['tool_consumer_instance_description'];

?>
<script language="javascript"> 
  //<![CDATA[ 
function lmsdataToggle() {
    var ele = document.getElementById("lmsDataForm");
    if(ele.style.display == "block") {
        ele.style.display = "none";
    }
    else {
        ele.style.display = "block";
    }
} 
  //]]> 
</script>
<?php
  echo("<form method=\"post\">\n");
  echo("<input type=\"submit\" name=\"launch\" value=\"Launch\">\n");
  echo("<input type=\"submit\" name=\"debug\" value=\"Debug Launch\">\n");
echo('<input type="submit" onclick="javascript:lmsdataToggle();return false;" value="Toggle Input Data">');
  if ( isset($_POST['launch']) || isset($_POST['debug']) ) {
    echo("<div id=\"lmsDataForm\" style=\"display:none\">\n");
  } else {
    echo("<div id=\"lmsDataForm\" style=\"display:block\">\n");
  }
  echo("<fieldset><legend>LTI Resource</legend>\n");
  $disabled = '';
  echo("Launch URL: <input size=\"60\" type=\"text\" $disabled size=\"60\" name=\"endpoint\" value=\"$endpoint\">\n");
  echo("<br/>Key: <input type\"text\" name=\"key\" $disapbled size=\"60\" value=\"$key\">\n");
  echo("<br/>Secret: <input type\"text\" name=\"secret\" $disabled size=\"60\" value=\"$secret\">\n");
  echo("</fieldset><p>");
  echo("<fieldset><legend>Launch Data</legend>\n");
  foreach ($lmsdata as $k => $val ) {
      echo($k.": <input type=\"text\" size=\"30\" name=\"".$k."\" value=\"");
      echo(htmlspecialchars($val));
      echo("\"><br/>\n");
  }
  echo("</fieldset>\n");
  echo("</div>\n");
  echo("</form>\n");

  $parms = $lmsdata;
  // Cleanup parms before we sign
  foreach( $parms as $k => $val ) {
    if (strlen(trim($parms[$k]) ) < 1 ) {
       unset($parms[$k]);
    }
  }

  // Add oauth_callback to be compliant with the 1.0A spec
  $parms["oauth_callback"] = "about:blank";
  if ( $outcomes ) {
    $parms["lis_outcome_service_url"] = $outcomes;
    $parms["lis_result_sourcedid"] = "feb-123-456-2929::28883";
  }
    
  $parms['launch_presentation_css_url'] = $cssurl;

if ( isset($_POST['launch']) || isset($_POST['debug']) ) {
  $parms = signParameters($parms, $endpoint, "POST", $key, $secret, 
"Finish Launch", $tool_consumer_instance_guid, $tool_consumer_instance_description);

  $content = postLaunchHTML($parms, $endpoint, isset($_POST['debug']), 
     "width=\"100%\" height=\"900\" scrolling=\"auto\" frameborder=\"1\" transparency");
  echo("<hr>\n");
  print($content);
}

?>
