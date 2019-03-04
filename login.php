<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $action   = (isset($_POST['action']))   ? mysql_safe($_POST['action']  ) : '';
  $username = (isset($_POST['username'])) ? mysql_safe($_POST['username']) : '';
  $password = (isset($_POST['password'])) ? mysql_safe($_POST['password']) : '';
  $confirm  = (isset($_POST['confirm']))  ? mysql_safe($_POST['confirm'] ) : '';
  $email    = (isset($_POST['email']))    ? mysql_safe($_POST['email']   ) : '';
  $db_error = '';


  if ($action == 'register') {
    if ($password != $confirm) {
      $db_error = $db_local[$language]['dberr1'];
    }

    else if(!eregi('^([0-9a-z])+$', $username)  ||  !eregi('^([0-9a-z])+$', $password)
        ||  strlen($username) < 5    ||   strlen($password) < 5
        ||  strlen($username) > 16   ||   strlen($password) > 16
        ||  $username==$password) {
      
      $db_error = $db_local[$language]['dberr3'];
    }

    else if (!eregi("^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$", $email)) {
      $db_error = $db_local[$language]['dberr2'];
    }

    else {
      mysql_query("LOCK TABLES `$db_users` WRITE, `$db_session` WRITE");
      $md5_pass = md5($password);

      //TODO: check for bugs in registeration and the likes for "=" vs "LIKE"
      $db_query  = "SELECT COUNT(*) FROM `$db_users` WHERE user_name LIKE '$username' OR user_email LIKE '$email'";
      $db_result = mysql_query($db_query);
      $db_count  = mysql_result($db_result, 0, 'COUNT(*)');
      mysql_free_result($db_result);


      if ($db_count == 0) {
        $db_query  = "INSERT INTO `$db_users` (`user_name`, `user_pass`, `user_email`, `user_rights`) VALUES ('$username', '$md5_pass', '$email', '1')";
        mysql_query($db_query);

        $user_id = mysql_insert_id();

        $db_query = "UPDATE `$db_session` SET `user_id`='$user_id' WHERE `ses_id`='$ses_id'";
        mysql_query($db_query);

      } else {
        $db_error = $db_local[$language]['dberr4'];
      }

      mysql_query("UNLOCK TABLES");  
    }
  }


  else if ($action == 'login') {
    mysql_query("LOCK TABLES `$db_users` READ, `$db_session` WRITE");
    $md5_pass = md5($password);

    $db_query  = "SELECT user_id FROM `$db_users` WHERE user_name='$username' AND user_pass='$md5_pass' LIMIT 1";
    $db_result = mysql_query($db_query);
    $db_count  = mysql_numrows($db_result);

    if ($db_count > 0) {
      $user_id = mysql_result($db_result, 0, 'user_id');
    } else {
      $user_id = 0;
      $db_error = $db_local[$language]['dberr3'];
    }

    $db_query = "UPDATE `$db_session` SET `user_id`='$user_id' WHERE `ses_id`='$ses_id' LIMIT 1";
    mysql_query($db_query);

    mysql_free_result($db_result);
    mysql_query("UNLOCK TABLES");  
  }


  if ($user_id != 0) {
    header("Location: profile.php");
    exit;
  }


  require('includes/header.php');



  if ($action == ''  ||  $action == 'login') {
    echo "<h1>" . $db_local[$language]['login'] . "</h1><div>\n";

    if ($db_error != '') echo "<span class=\"error\">$db_error</span>\n";

    echo "<form action=\"$PHPSELF\" method=\"POST\">\n";
    echo "<table class=\"related\" cellspacing=\"0\">\n";
    echo "<tr><th>" . $db_local[$language]['username'] . "</th><td><input type=\"text\" name=\"username\" size=\"25\" /></td></tr>\n";
    echo "<tr><th>" . $db_local[$language]['password'] . "</th><td><input type=\"password\" name=\"password\" size=\"25\" /></td></tr>\n";
    echo "<tr><th>&nbsp;</th><td><input type=\"submit\" value=\"" . $db_local[$language]['login'] . "\" /><input type=\"hidden\" name=\"action\" value=\"login\" /></td></tr>\n";
    echo "</table>\n</form>\n</div>\n";
  }



  if ($action == ''  ||  $action == 'register') {
    echo "<h1>" . $db_local[$language]['register'] . "</h1><div>\n";

    if ($db_error != '') echo "<span class=\"error\">$db_error</span>\n";

    echo "<form action=\"$PHPSELF\" method=\"POST\">\n";
    echo "<table class=\"related\" cellspacing=\"0\">\n";
    echo "<tr><th>" . $db_local[$language]['username'] . "</th><td><input type=\"text\" name=\"username\" size=\"25\" value=\"$username\" /></td></tr>\n";
    echo "<tr><th>" . $db_local[$language]['password'] . "</th><td><input type=\"password\" name=\"password\" size=\"25\" value=\"\" /> <input type=\"password\" name=\"confirm\" size=\"25\" value=\"\" /></td></tr>\n";
    echo "<tr><th>" . $db_local[$language]['email'] . "</th><td><input type=\"text\" name=\"email\" size=\"25\" value=\"$email\" /></td></tr>\n";
    echo "<tr><th>&nbsp;</th><td><input type=\"submit\" value=\"" . $db_local[$language]['register'] . "\" /><input type=\"hidden\" name=\"action\" value=\"register\" /></td></tr>\n";
    echo "</table>\n</form>\n</div>\n";
  }


/*
  echo "<h1>" . $db_local[$language]['resetpass'] . "</h1><div>\n";
  echo "<form action=\"$PHPSELF\" method=\"POST\">\n";
  echo "<table class=\"related\" cellspacing=\"0\">\n";
  echo "<tr><th>" . $db_local[$language]['email'] . "</th><td><input type=\"text\" name=\"email\" size=\"25\" /></td></tr>\n";
  echo "<tr><th>&nbsp;</th><td><input type=\"submit\" value=\"" . $db_local[$language]['resetpass'] . "\" /><input type=\"hidden\" name=\"action\" value=\"recover\" /></td></tr>\n";
  echo "</table>\n</form>\n</div>\n";
*/

  require('includes/footer.php');
?>
