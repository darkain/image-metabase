<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $page_name = $db_local[$language]['untagged'];

  require('includes/header.php');


  $db_query    = "SELECT `image_id`, `image_twidth`, `image_theight` FROM `$db_img` WHERE `image_id` NOT IN (SELECT `image_id` FROM `$db_imglink`) ORDER BY `image_id` DESC";
  $count_query = "SELECT COUNT(*) FROM `$db_img` WHERE `image_id` NOT IN (SELECT `image_id` FROM `$db_imglink`)";
  list_thumbs($db_local[$language]['untagged'], $db_query, $count_query);


  require('includes/footer.php');
?>
