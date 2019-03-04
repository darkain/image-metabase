<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $page_name = $db_local[$language]['metatypename'];

  require('includes/header.php');


  echo "<h1>" . $db_local[$language]['metatypename'] . "</h1><div class=\"reltags buttons\">\n";


  $db_params = '';
  $count = 0;


  if (isset($_GET['typeid'])) {
    $typeid = $_GET['typeid'];
    reset($typeid);
    
    while ($item = each($typeid)) {
      $mt_id   = mysql_safe($item[0]);
      $md_name = mysql_safe($item[1]);  //TODO: explode on comma

      if ($mt_id > 0  &&  strlen($md_name) > 0) {
        if ($count != 0) $db_params .= ' OR ';
        $db_params .= "(m.metatype_id='$mt_id' AND m.meta_data LIKE '%$md_name%')";
        $count++;
      }
    }

//    if ($count > 0) {
//      $db_query ="SELECT * FROM `$db_meta` m, `$db_metatype` t WHERE m.metatype_id=t.metatype_id AND ($db_params) ORDER BY t.metatype_name";
//      echo "$db_query<br/>\n";
//    }
  }


  if ($count > 0) {
    $create_query = "CREATE TEMPORARY TABLE `#temp` SELECT i.image_id, i.image_twidth, i.image_theight FROM `$db_img` i, `$db_imglink` l, `$db_meta` m, `$db_metatype` t WHERE i.image_id=l.image_id AND l.meta_id=m.meta_id AND m.metatype_id=t.metatype_id AND ($db_params) ORDER BY i.image_id DESC";
    mysql_query($create_query);

    $db_query    = "SELECT `image_id`, `image_twidth`, `image_theight` FROM `#temp`";
    $count_query = "SELECT COUNT(*) FROM `#temp`";
    list_thumbs($db_local[$language]['result'], $db_query, $count_query);

    mysql_query("DROP TABLE `#temp`");


  } else {
    $db_query  = "SELECT metatype_name, metatype_id FROM $db_metatype WHERE 1 ORDER BY metatype_name";
    $db_result = mysql_query($db_query);
    $db_count  = mysql_numrows($db_result);

    if ($db_count == 0) {
      echo "<i>" . $db_local[$language]['none'] . "</i>\n";
    } else {
      echo "<form action=\"$PHPSELF\" method=\"GET\">\n";
      echo "<table class=\"related\" cellspacing=\"0\">\n";
    }

    for ($i=0; $i<$db_count; $i++) {
      $mt_id   = mysql_result($db_result, $i, "metatype_id");
      $mt_name = mysql_result($db_result, $i, "metatype_name");

      echo "<tr><th><a href=\"metatype.php?id=$mt_id\">$mt_name</a></th>";
      echo "<td><input type=\"text\" name=\"typeid[$mt_id]\" size=\"50\" /></td></tr>\n";
    }
    mysql_free_result($db_result);

    if ($db_count != 0) {
      echo "<tr><th>&nbsp;</th><td>\n";
      echo "<input type=\"submit\" value=\"" . $db_local[$language]['search'] . "\" />\n";
      echo "</td></tr>\n</table>\n</form>\n";
    }
  }

  echo "</div>\n";

  require('includes/footer.php');
?>
