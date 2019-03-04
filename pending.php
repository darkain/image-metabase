<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $page_name = $db_local[$language]['imagepend'];

  require('includes/header.php');


  $db_query    = "SELECT `image_id`, `image_twidth`, `image_theight` FROM `$db_img` WHERE `image_id` IN (SELECT `image_id` FROM `$db_imgpend`) ORDER BY `image_id` DESC";
  $count_query = "SELECT COUNT(*) FROM `$db_img` WHERE `image_id` IN (SELECT `image_id` FROM `$db_imgpend`)";
  list_thumbs($db_local[$language]['imagepend'], $db_query, $count_query, '', '&amp;mode=pendingtags');


  require('includes/footer.php');
?>
