<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  if ($id == 0) redirect_404();
  
  
  if (isset($_POST['submit']) && isset($_POST['tags'])) {
    if ($user_rights == 'mod'  ||  $user_rights == 'admin') {

      mysql_query("LOCK TABLES `$db_meta` WRITE");

      $tags = explode(',', $_POST['tags']);
      reset($tags);
      while ($item = each($tags)) {
        $tag = mysql_safe($item[1]);
        if (strlen($tag) > 0) {

          $db_query  = "SELECT * FROM `cosplay_meta` WHERE `metatype_id`='$id' AND `meta_data` LIKE '$tag' LIMIT 1";
          $db_result = mysql_query($db_query);
          $db_count  = mysql_numrows($db_result);
          mysql_free_result($db_result);

          if ($db_count == 0) {
            $db_query  = "INSERT INTO `$db_meta` (`metatype_id`, `meta_data`) VALUES ('$id', '$tag')";
            mysql_query($db_query);
          }

        }
      }

      mysql_query("UNLOCK TABLES");
    }
    redirect_self();
  }


  //this must come before sending headers, as it may redirect the user
  update_desc($db_metatype, 'metatype_desc', 'metatype_id');


  $db_query  = "SELECT metatype_name, metatype_desc FROM `$db_metatype` m WHERE m.metatype_id='$id' LIMIT 1";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);
  
  if ($db_count == 0) {
    mysql_free_result($db_result);
    redirect_404();
  }
  
  $mt_name   = mysql_result($db_result, 0, "metatype_name");
  $mt_desc   = mysql_result($db_result, 0, "metatype_desc");
  $page_name = "Meta Type: $mt_name";
  mysql_free_result($db_result);


  require('includes/header.php');


  if ($mode != 'editdesc') {
    if ($user_rights == 'mod'  ||  $user_rights == 'admin') {
      echo "<h1 class=\"edit\"><a href=\"$PHPSELF?id=$id&amp;mode=editdesc\">" . $db_local[$language]['editdesc'] . "</a></h1>\n";
    }
  }

  echo "<h1><a href=\"metatypes.php\">" . $db_local[$language]['metatype'] . "</a>: $mt_name</h1><div>";
  if ($mode == 'editdesc') {
    echo "<form action=\"$PHPSELF\" method=\"POST\">\n";
    echo "<input type=\"hidden\" name=\"submit\" value=\"" . $db_local[$language]['updatedesc'] . "\">\n";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";
    echo "<textarea name=\"desc\" rows=\"10\" cols=\"75\">$mt_desc</textarea><br/>\n";
    echo "<input type=\"submit\" name=\"submit\" value=\"" . $db_local[$language]['updatedesc'] . "\">\n";
    echo "</form>\n";
  } else {
    if ($mt_desc == '') {
      echo "<i>" . $db_local[$language]['nodesc'] . "</i>\n";
    } else {
      echo $mt_desc;
    }
  }
  echo "</div>\n\n";


  $db_query  = "SELECT * FROM `$db_meta` m WHERE m.metatype_id='$id' ORDER BY meta_data ASC";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);

  echo "<h1>" . $db_local[$language]['reltags'] . "</h1><div class=\"reltags buttons\">\n";
  if ($db_count == 0) echo "<i>" . $db_local[$language]['none'] . "</i>\n";

  $prev_letter = '';
  for ($i=0; $i<$db_count; $i++) {
    $md_id   = mysql_result($db_result, $i, "meta_id");
    $md_data = mysql_result($db_result, $i, "meta_data");

    $this_letter = substr($md_data, 0, 1);
    if ($this_letter == $prev_letter) {
      echo ", \n";
    } else {
      echo "<h4>$this_letter</h4>";
      $prev_letter = $this_letter;
    }

    echo "<a href=\"meta.php?id=$md_id\">$md_data</a>";
  }
  mysql_free_result($db_result);

  echo "</div>\n";


  if ($user_rights == 'mod'  ||  $user_rights == 'admin') {
    echo "<h1>" . $db_local[$language]['addtags'] . "</h1><div>\n";
    echo "<form method=\"POST\" action=\"metatype.php\">\n";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
    echo "<input type=\"text\" name=\"tags\" size=\"100\" />\n";
    echo "<input type=\"hidden\" name=\"submit\" value=\"" . $db_local[$language]['addtags'] . "\" />\n";
    echo "<input type=\"submit\" name=\"submit\" value=\"" . $db_local[$language]['addtags'] . "\" />\n";
    echo "</form></div>\n";
  }
  

  require('includes/footer.php');
?>
