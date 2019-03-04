<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $page_name = $db_local[$language]['error'] . ": 404";

  require('includes/header.php');

  echo "<h1>$page_name</h1><div class=\"error\">\n";
  echo $db_local['en-us']['dberr5'];
  echo "</div>\n";

  require('includes/footer.php');
?>
