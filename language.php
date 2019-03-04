<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $page_name = "Locals Validation";

  require('includes/header.php');

  echo "<h1>Locals Validation</h1><div>\n";


  unset($lang);
  reset($db_local);
  $count = 0;
  $main = '';

  while ($lang_item = each($db_local)) {
    $lang[] = $lang_item[0];
    if ($count == 0) $main = $lang_item[0];
    $count++;
  }

  reset($lang);

  echo "<table border=\"1\"><tr>\n<th>&nbsp;</th>\n";

  for ($i=0; $i<$count; $i++) {
    $item = $lang[$i];
    echo "<th>$item</th>\n";
  }

  echo "</tr><tr>\n";

  reset($db_local[$main]);
  while ($row_item = each($db_local[$main])) {
    $row = $row_item[0];
    echo "<th>$row</th>\n";

    for ($i=0; $i<$count; $i++) {
      if ($i > 0  &&  !isset($db_local[$lang[$i]][$row])) {
        echo "<td class=\"error\"><i>UNSET VALUE</i></td>\n";
      } else {
        $item = $db_local[$lang[$i]][$row];
        $class = '';
        if ($lang[$i] !== $main  &&  $item === $db_local[$main][$row]) $class='class="error"';
        echo "<td $class>$item</td>\n";
      }
    }

    echo "</tr><tr>\n";
  }

  echo "</tr></table>\n";

  echo "</div>\n";

  require('includes/footer.php');

?>