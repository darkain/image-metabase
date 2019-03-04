<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $page_name = $db_local[$language]['recentimage'];

  require('includes/header.php');


  $db_query    = "SELECT `image_id`, `image_twidth`, `image_theight` FROM `$db_img` WHERE 1 ORDER BY `image_id` DESC";
  $count_query = "SELECT COUNT(*) FROM `$db_img`";
  list_thumbs($db_local[$language]['recentimage'], $db_query, $count_query);


  require('includes/footer.php');
?>
