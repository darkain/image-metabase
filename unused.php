<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $page_name = $db_local[$language]['unusedtags'];

  require('includes/header.php');


  $db_query = "SELECT m.meta_data, m.meta_id, t.metatype_name, t.metatype_id FROM `$db_meta` m, `$db_metatype` t WHERE m.meta_id NOT IN (SELECT DISTINCT meta_id FROM `$db_imglink`) AND m.metatype_id=t.metatype_id ORDER BY t.metatype_name ASC, m.meta_data ASC";
  $db_result = mysql_query($db_query);
  $db_count  = mysql_numrows($db_result);

  echo "<h1>" . $db_local[$language]['unusedtags'] . "</h1><div>";
  
  if ($db_count == 0) {
    echo "<i>" . $db_local[$language]['none'] . "</i>\n";
  } else {
    echo "<table class=\"related buttons\" cellspacing=\"0\">\n";
  }


  $lasttype = '';
  for ($i=0; $i<$db_count; $i++) {
    $md_id   = mysql_result($db_result, $i, "meta_id");
    $md_data = mysql_result($db_result, $i, "meta_data");
    $mt_id   = mysql_result($db_result, $i, "metatype_id");
    $mt_name = mysql_result($db_result, $i, "metatype_name");

    if ($mt_name != $lasttype) {
      if ($lasttype != '') echo "</td></tr>\n";
      $lasttype = $mt_name;
      echo "<tr><th valign=\"top\"><a href=\"metatype.php?id=$mt_id\">$mt_name</a></th><td>";
    } else {
      echo ', ';
    }

    echo "<a href=\"meta.php?id=$md_id\">$md_data</a>";
  }

  if ($db_count > 0) echo "</td></tr>\n</table>\n";
  echo "</div>\n";

  mysql_free_result($db_result);

  require('includes/footer.php');
?>