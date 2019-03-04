<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');
  require('includes/pending.php');

  
  $db_query  = "SELECT user_id FROM `$db_img` WHERE image_id='$id' LIMIT 1";
  $db_result = mysql_query($db_query);
  if (mysql_numrows($db_result) == 0) {
    mysql_free_result($db_result);
    redirect_404();
  }
  if (mysql_result($db_result, 0, "user_id") == $user_id) $page_rights = 'owner';
  mysql_free_result($db_result);

  
  //this must come before sending headers, as it may redirect the user
  pending_tags(1, $db_imglink, $db_imgpend, 'image_id', 'meta_id');
  

  //this must come before sending headers, as it may redirect the user
  update_desc($db_img, 'image_desc', 'image_id');

 
  $page_keys = 'Photo';
  $db_query = "SELECT m.meta_data FROM `$db_meta` m, `$db_imglink` l WHERE m.meta_id=l.meta_id AND l.image_id='$id'";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);
  
  for ($i=0; $i<$db_count; $i++) {
    $md_data = mysql_result($db_result, $i, "meta_data");
    $page_keys .= ", $md_data";
  }
  
  mysql_free_result($db_result);
  

  $page_name = $db_local[$language]['image'] . ": $id";

  require('includes/header.php');

  $db_query  = "SELECT i.`image_url`, i.`image_desc`, i.`image_width`, i.`image_height`, i.`image_twidth`, i.`image_theight`, i.`image_size`, u.`user_id`, u.`user_name` FROM `$db_img` i, `$db_users` u WHERE i.`image_id`='$id' AND i.`user_id`=u.`user_id` LIMIT 1";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);

  $img_url  = mysql_result($db_result, 0, 'image_url');  //TODO: change all to single quotes
  $img_desc = mysql_result($db_result, 0, 'image_desc');
  $img_w    = mysql_result($db_result, 0, 'image_width');
  $img_h    = mysql_result($db_result, 0, 'image_height');
  $img_tw   = mysql_result($db_result, 0, 'image_twidth');
  $img_th   = mysql_result($db_result, 0, 'image_theight');
  $img_size = mysql_result($db_result, 0, 'image_size');
  $u_id     = mysql_result($db_result, 0, 'user_id');
  $u_name   = mysql_result($db_result, 0, 'user_name');

  $img_size = floor($img_size / 1024);
  if ($u_id === $user_id) $u_name = "<b>$u_name</b>";


  if ($mode != 'editdesc') {
    if ($user_rights == 'mod'  ||  $user_rights == 'admin'  ||  $page_rights == 'owner') {
      echo "<h1 class=\"edit\"><a href=\"$PHPSELF?id=$id&amp;mode=editdesc\">" . $db_local[$language]['editdesc'] . "</a></h1>\n";
    }
  }

  echo "<h1>$page_name</h1><div class=\"imgpage\">\n";

  echo "<table><tr><td class=\"tdimg\" valign=\"top\" rowspan=\"5\">\n<a href=\"$img_url\">";

  if (file_exists("$thumb_dir/$id.jpg")) {
    $margin = floor(($thumb_size - $img_th) / 2);
    $style  = 'width:' . $img_tw . 'px;height:' . $img_th . 'px;margin-top:' . $margin . 'px';
    echo "<img src=\"$thumb_dir/$id.jpg\" alt=\"Image: $id\" style=\"$style\" />";
  } else {
    echo $db_local[$language]['image'];
  }
  echo "</a></td>\n";

  echo "<th>" . $db_local['en-us']['username'] . ":</th><td><a href=\"profile.php?id=$u_id\">$u_name</a></td></tr>\n";
  echo "<tr><th>" . $db_local['en-us']['url'] . ":</th><td><a href=\"$img_url\">$img_url</a></td></tr>\n";
  echo "<tr><th>" . $db_local['en-us']['dimension'] . ":</th><td>" . $img_w . " x " . $img_h . "</td></tr>\n";
  echo "<tr><th>" . $db_local['en-us']['filesize'] . ":</th><td>" . $img_size . " kb</td></tr>\n";

  echo "<tr><td colspan=\"2\" class=\"imgdesc\">\n";
  if ($mode == 'editdesc') {
    echo "<form action=\"$PHPSELF\" method=\"POST\">\n";
    echo "<input type=\"hidden\" name=\"submit\" value=\"" . $db_local[$language]['updatedesc'] . "\">\n";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";
    echo "<textarea name=\"desc\" rows=\"10\" cols=\"75\">$img_desc</textarea><br/>\n";
    echo "<input type=\"submit\" name=\"submit\" value=\"" . $db_local[$language]['updatedesc'] . "\">\n";
    echo "</form>\n";
  } else {
    if ($img_desc == '') {
      echo "<i>" . $db_local[$language]['nodesc'] . "</i>\n";
    } else {
      echo $img_desc;
    }
  }

  echo "</td></tr></table>\n</div>\n";
  mysql_free_result($db_result);


  $db_tables     = "`$db_imglink` l, `$db_meta` m, `$db_metatype` t";
  $db_pending    = "`$db_imgpend` l, `$db_meta` m, `$db_metatype` t";
  $db_conditions = "l.image_id='$id' AND l.meta_id=m.meta_id AND m.metatype_id=t.metatype_id";
  display_tags($db_tables, $db_pending, $db_conditions);



  require('includes/footer.php');
?>
