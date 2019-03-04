<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/

  echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<title><?php echo (isset($page_name)) ? "$site_name - $page_name" : "$site_name"; ?></title>
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<meta http-equiv="Content-Language" content="<?php echo $language ?>" />
<meta name="language" content="<?php echo $language ?>" />
<meta name="generator" content="Image Metabase&trade;" />
<meta name="doc-class" content="Completed" />
<meta name="rating" content="general" />
<meta name="keywords" content="<?php echo (isset($page_keys)) ? "$site_keys, $page_keys" : "$site_keys"; ?>" />
<link rel="stylesheet" type="text/css" href="stylesheet.css" />
</head>

<body>


<div class="google">
<script type="text/javascript"><!--
google_ad_client = "pub-0556075448585716";
google_ad_width = 468;
google_ad_height = 60;
google_ad_format = "468x60_as";
google_ad_type = "text_image";
google_ad_channel ="";
google_color_border = "EEFFEE";
google_color_link = "000000";
google_color_bg = "EEFFEE";
google_color_text = "000000";
google_color_url = "000000";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script></div>


<table class="mainbar" cellspacing="0"><tr>
<th colspan="3" class="mainlogo"><a href="index.php"><img src="logo.png" alt="Cosplay Metabase" /></a></th>
</tr><tr>

<?
  echo "<td class=\"buttons\">\n";
  echo "<a href=\"recent.php\">" . $db_local[$language]['mainrecent'] . "</a>\n";
  if ($user_rights != 'guest') {
    echo "<a href=\"untagged.php\">" . $db_local[$language]['mainuntag'] . "</a>\n";
    echo "<a href=\"pending.php\">" . $db_local[$language]['mainpend'] . "</a>\n";
    if ($user_rights == 'mod'  ||  $user_rights == 'admin') {
      echo "<a href=\"unlinked.php\">" . $db_local[$language]['mainunlink'] . "</a>\n";
      echo "<a href=\"unused.php\">" . $db_local[$language]['mainunused'] . "</a>\n";
      if ($user_rights == 'admin') {
        echo "<a href=\"sessions.php\">" . $db_local[$language]['mainsession'] . "</a>\n";
      }
    }
  }
  echo "</td>\n";

  echo "<td class=\"login\">\n";
  if ($user_id == 0) {
    echo "<a href=\"login.php\">" . $db_local[$language]['loginreg'] . "</a>\n";
  } else {
    echo "<a href=\"profile.php\">$user_name</a> - <a href=\"login.php?logout=1\">logout</a>\n";
  }
  echo "</td>\n";


  $search_value = '';
  if (isset($search_query)) $search_value = htmlspecialchars($search_query);
  if (get_magic_quotes_gpc()) $search_value = stripslashes($search_value);
  
  echo "<td class=\"search\"><form action=\"search.php\" method=\"get\"><div>\n";
  echo "<input type=\"text\" name=\"search\" size=\"20\" value=\"$search_value\" />\n";
  echo "<input type=\"submit\" value=\"" . $db_local[$language]['search'] . "\" />\n";
//  echo "<a href=\"metatypes.php\">(advanced)</a>\n";
  echo "</div></form></td>\n";
?>
</tr>
</table>


