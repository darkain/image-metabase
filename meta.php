<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');
  require('includes/pending.php');
  
  if ($id == 0) redirect_404();
  

  //this must come before sending headers, as it may redirect the user
  update_desc($db_meta, 'meta_desc', 'meta_id');
  
  
  if (isset($_POST['submit']) && isset($_POST['newname'])) {
    if ($user_rights == 'mod'  ||  $user_rights == 'admin') {
      $name = htmlspecialchars(mysql_safe($_POST['newname']));
      if (strlen($name) > 0) {
        $db_query = "UPDATE `$db_meta` SET `meta_data`='$name' WHERE `meta_id`='$id' LIMIT 1";
        mysql_query($db_query);
      }
    }
    redirect_self();
  }

  
  
  //this must come before sending headers, as it may redirect the user
  pending_tags(2, $db_metalink, $db_metapend, 'meta_id', 'meta_child');
  
  $page_keys = 'Metadata';
  $db_query = "SELECT m.meta_data FROM `$db_meta` m, `$db_metalink` l WHERE ((m.meta_id=l.meta_id AND l.meta_child='$id') OR (m.meta_id=l.meta_child AND l.meta_id='$id'))";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);
  
  for ($i=0; $i<$db_count; $i++) {
    $md_data = mysql_result($db_result, $i, "meta_data");
    $page_keys .= ", $md_data";
  }
  
  mysql_free_result($db_result);
  

  $db_query  = "SELECT m.meta_data, m.meta_desc, t.metatype_name, t.metatype_id FROM `$db_meta` m, `$db_metatype` t WHERE m.meta_id='$id' AND m.metatype_id=t.metatype_id LIMIT 1";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);
  
  if ($db_count == 0) {
    mysql_free_result($db_result);
    redirect_404();
  }
  
  $md_name   = mysql_result($db_result, 0, "meta_data");
  $md_desc   = mysql_result($db_result, 0, "meta_desc");
  $mt_name   = mysql_result($db_result, 0, "metatype_name");
  $mt_id     = mysql_result($db_result, 0, "metatype_id");
  mysql_free_result($db_result);
  
  $page_name  = "$mt_name: $md_name";
  $page_keys .= ", $md_name";
  
  require('includes/header.php');
  

  if ($mode != 'editdesc'  &&  $mode != 'editname') {
    if ($user_rights == 'mod'  ||  $user_rights == 'admin') {
      echo "<h1 class=\"edit\"><a href=\"$PHPSELF?id=$id&amp;mode=editdesc\">" . $db_local[$language]['editdesc'] . "</a></h1>\n";
      echo "<h1 class=\"edit\"><a href=\"$PHPSELF?id=$id&amp;mode=editname\">" . $db_local[$language]['editname'] . "</a></h1>\n";
    }
  }

  if ($mode == 'editname') {
    echo "<form action=\"$PHPSELF\" method=\"post\"><h1>Name:\n";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
    echo "<input type=\"hidden\" name=\"submit\" value=\"newname\" />\n";
    echo "<input type=\"text\" name=\"newname\" value=\"" . htmlspecialchars($md_name) . "\" />\n";
    echo "<input type=\"submit\" value=\"" . $db_local['en-us']['updatename'] . "\" />\n";
    echo "</h1></form><div>";
  } else {
    echo "<h1><a href=\"metatype.php?id=$mt_id\">$mt_name</a>: $md_name</h1><div>";
  }
  
  if ($mode == 'editdesc') {
    echo "<form action=\"$PHPSELF\" method=\"POST\">\n";
    echo "<input type=\"hidden\" name=\"submit\" value=\"" . $db_local[$language]['updatedesc'] . "\">\n";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";
    echo "<textarea name=\"desc\" rows=\"10\" cols=\"75\">$md_desc</textarea><br/>\n";
    echo "<input type=\"submit\" name=\"submit\" value=\"" . $db_local[$language]['updatedesc'] . "\">\n";
    echo "</form>\n";
  } else {
    if ($md_desc == '') {
      echo "<i>" . $db_local[$language]['nodesc'] . "</i>\n";
    } else {
      echo $md_desc;
    }
  }
  echo "</div>\n\n";  


  $db_tables     = "`$db_metalink` l, `$db_meta` m, `$db_metatype` t";
  $db_pending    = "`$db_metapend` l, `$db_meta` m, `$db_metatype` t";
  $db_conditions = "((l.meta_id='$id' AND m.meta_id=l.meta_child AND m.metatype_id=t.metatype_id) OR (l.meta_child='$id' AND m.meta_id=l.meta_id AND m.metatype_id=t.metatype_id))";
  display_tags($db_tables, $db_pending, $db_conditions);


  if ($mode == '1') {
    $db_query    = "SELECT i.image_id, i.image_twidth, i.image_theight FROM `$db_img` i, `$db_imglink` l WHERE l.meta_id='$id' AND i.image_id=l.image_id ORDER BY `image_id` DESC";
    $count_query = "SELECT COUNT(*) FROM `$db_img` i, `$db_imglink` l WHERE l.meta_id='$id' AND i.image_id=l.image_id";
    list_thumbs("Related Images", $db_query, $count_query, "&amp;id=$id");
  }


  require('includes/footer.php');
?>
