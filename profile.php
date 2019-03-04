<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');
  require('includes/importer.php');
  
  if (isset($_POST['submit']) && isset($_POST['url'])) {
    $url = $_POST['url'];
    import_url($url);
    redirect_self();
  }


  $profile_id = $id;
  if ($profile_id == 0) $profile_id = $user_id;


  $db_query  = "SELECT `user_name` FROM `$db_users` WHERE `user_id`='$profile_id' LIMIT 1";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);
  if ($db_count < 1) redirect_404();
  $u_name    = mysql_result($db_result, 0, "user_name");
  mysql_free_result($db_result);
  

  //TODO:  update with username
  $page_name = $db_local[$language]['profile'] . ": $u_name";

  require('includes/header.php');


  if ($profile_id == 0) {
    echo "<h1>" . $db_local[$language]['profile'] . "</h1><div>\n";
    echo "<i>" . $db_local[$language]['nologin'] . "</i>\n";
    echo "</div>\n";
  } else {
    if ($profile_id == $user_id) {
      echo "<h1>" . $db_local['en-us']['upload'] . "</h1><div>\n";
      echo "<form action=\"$PHPSELF\" method=\"post\"><div>Image URL: \n";
      echo "<input type=\"hidden\" name=\"submit\" value=\"upload\" />\n";
      echo "<input type=\"text\" name=\"url\" size=\"50\" />\n";
      echo "<input type=\"submit\" value=\"" . $db_local['en-us']['upload'] . "\" />\n";
      echo "</div></form>\n";
      echo "Cosplay.com example: <b>http://images.cosplay.com/showphoto.php?photo=639639</b><br/>\n";
      echo "DeviantArt example: <b>http://www.deviantart.com/view/30981008/</b></div>\n";
    }
    
    $thumb_title = $db_local[$language]['myimages'];
    if ($profile_id != $user_id) $thumb_title = $u_name . $db_local[$language]['images'];

    $db_query    = "SELECT `image_id`, `image_twidth`, `image_theight` FROM `$db_img` WHERE user_id='$profile_id' ORDER BY `image_id` DESC";
    $count_query = "SELECT COUNT(*) FROM `$db_img` WHERE user_id='$user_id'";
    list_thumbs($thumb_title, $db_query, $count_query, "&amp;id=$profile_id");
  }


  require('includes/footer.php');
?>
