<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');
  
  if ($user_rights != 'admin') redirect_404();
  
  require('includes/header.php');
  
  $db_query  = "SELECT u.user_name, u.user_id, s.ses_ip, ses_agent, ses_timeout FROM `$db_session` s, `$db_users` u WHERE s.user_id=u.user_id ORDER BY s.ses_timeout DESC";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);
  
  echo "<h1>" . $db_local[$language]['sessions'] . "</h1><div>\n";
  
  if ($db_count == 0) {
    echo "<i>" . $db_local[$language]['none'] . "</i>\n";
  } else {
    echo "<table border=\"1\">\n";
    echo "<tr><th>" . $db_local[$language]['username'] . "</th><th>" . $db_local[$language]['ipaddy'] . "</th><th>" . $db_local[$language]['timeout'] . "</th><th>" . $db_local[$language]['useragent'] . "</th></tr>\n";
    
    for ($i=0; $i<$db_count; $i++) {
      $u_name  = mysql_result($db_result, $i, "user_name");
      $u_id    = mysql_result($db_result, $i, "user_id");
      $u_ip    = mysql_result($db_result, $i, "ses_ip");
      $u_agent = mysql_result($db_result, $i, "ses_agent");
      $u_time  = mysql_result($db_result, $i, "ses_timeout");

      $u_date  = date('Y-m-d H:i:s O', $u_time);
      $address = (($u_ip >> 24) & 0xFF) . '.' . (($u_ip >> 16) & 0xFF) . '.' . (($u_ip >> 8) & 0xFF) . '.' . (($u_ip >> 0) & 0xFF);

      $style = ($u_time - time() > $ses_timeout - (60*5)) ? ' style="background:yellow;"' : '';
      
      echo "<tr$style><td><a href=\"profile.php?id=$u_id\">$u_name</a></td><td><a href=\"http://www.dnsstuff.com/tools/ipall.ch?domain=$address\">$address</a></td><td>$u_date</td><td>$u_agent</td></tr>\n";
      
    }
    
    echo "</table>\n";
  }
  
  echo "</div>\n";
  mysql_free_result($db_result);
  
  
  require('includes/footer.php');
?>