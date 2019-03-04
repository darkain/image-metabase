<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  sscanf($_SERVER["REMOTE_ADDR"], "%d.%d.%d.%d", $ip0, $ip1, $ip2, $ip3);
  $ses_ip      = ($ip0 << 24) | ($ip1 << 16) | ($ip2 << 8) | ($ip3);
  $ses_timeout = 60 * 60 * 24 * 7;              // 1 Week, in seconds
  $ses_exp     = time() + $ses_timeout;
  $user_id     = 0;
  $user_name   = '';
  $user_logout = (isset($_GET['logout'])) ? $_GET['logout'] : 0;
  $user_agent  = mysql_safe($_SERVER['HTTP_USER_AGENT']);
  $user_rights = 'guest';
  $page_rights = 'guest';
  
  
  ini_set('session.use_cookies',      TRUE);
  ini_set('session.use_only_cookies', TRUE);
  ini_set('session.cookie_lifetime',  $ses_timeout);
  ini_set('session.cache_limite',     'private');


  session_start();
  $ses_id      = mysql_safe(session_id());
  session_write_close();


  reset($ses_ignore);
  while ($item = each($ses_ignore)) {
    if (stripos($user_agent, $item[1]) !== FALSE) {
      $user_rights = 'spider';
      break;
    }
  }

  
  if ($user_rights != 'spider') {
    mysql_query("LOCK TABLES `$db_session` WRITE, `$db_users` READ");
    $db_query  = "SELECT ses_timeout, user_id FROM `$db_session` WHERE ses_id='$ses_id' LIMIT 1";
    $db_result = mysql_query($db_query);
    $db_count  = mysql_numrows($db_result);

    if ($db_count > 0) {
      $ses_end = mysql_result($db_result, 0, 'ses_timeout');
      if ($ses_exp < $ses_end  ||  $user_logout == 1) {
        $db_query = "UPDATE `$db_session` SET `ses_timeout`='$ses_exp', `user_id`='0' WHERE `ses_id`='$ses_id' LIMIT 1";
        mysql_query($db_query);
      } else {
        $user_id  = mysql_result($db_result, 0, 'user_id');
        $db_query = "UPDATE `$db_session` SET `ses_timeout`='$ses_exp' WHERE `ses_id`='$ses_id' LIMIT 1";
        mysql_query($db_query);
      }
    } else {
      $db_query = "INSERT INTO `$db_session` (`ses_id`, `ses_ip`, `user_id`, `ses_timeout`, `ses_agent`) VALUES ('$ses_id', '$ses_ip', '0', '$ses_exp', '$user_agent')";
      mysql_query($db_query);
    }
    mysql_free_result($db_result);


    if ($user_id > 0) {
      $db_query    = "SELECT user_name, user_rights, user_lang FROM `$db_users` WHERE user_id='$user_id' LIMIT 1";
      $db_result   = mysql_query($db_query);
      $user_name   = mysql_result($db_result, 0, 'user_name');
      $user_rights = mysql_result($db_result, 0, 'user_rights');
      $language    = mysql_result($db_result, 0, 'user_lang');
      mysql_query($db_query);
      mysql_free_result($db_result);
    }


    mysql_query("UNLOCK TABLES");
  }
?>