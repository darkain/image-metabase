<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  require('includes/config.php');

  $search_query = (isset($_GET['search'])) ? $_GET['search'] : '';
  $page_name    = $db_local[$language]['search'] . ": $search_query";
  $search_url   = urlencode($search_query);

  require('includes/header.php');

  $search_items = array();
  $search_count = 0;
  $insert_count = 0;

  $parts = explode('"', $search_query);
  while ($item = each($parts)) {
    $thisitem = mysql_safe($item[1]);
    if (strlen($thisitem) > 0) {
      if ($search_count % 2 == 0) {
        $subparts = explode(' ', $thisitem);
        while ($subitem = each($subparts)) {
          $thissubitem = mysql_safe($subitem[1]);
          if (strlen($thissubitem) > 0) {
            $search_items[$insert_count] = $thissubitem;
            $insert_count++;
          }
        }
      } else {
        $search_items[$insert_count] = $thisitem;
        $insert_count++;
      }
    }
    $search_count++;
  }

 
  reset($search_items);
  $query_params = '';
  for ($i=0; $i<$insert_count; $i++) {
    if ($i > 0) $query_params .= ' AND ';
    $query_item = $search_items[$i];

    $query_item = str_replace('\\', '\\\\', $query_item);
    $query_item = str_replace('[', '\\\\[', $query_item);
    $query_item = str_replace(']', '\\\\]', $query_item);
    $query_item = str_replace('|', '\\\\|', $query_item);
    $query_item = str_replace('(', '\\\\(', $query_item);
    $query_item = str_replace(')', '\\\\)', $query_item);
    $query_item = str_replace('{', '\\\\{', $query_item);
    $query_item = str_replace('}', '\\\\}', $query_item);
    $query_item = str_replace('$', '\\\\$', $query_item);
    $query_item = str_replace('.', '\\\\.', $query_item);
    $query_item = str_replace('^', '\\\\^', $query_item);
    $query_item = str_replace('+', '\\\\+', $query_item);
    $query_item = str_replace('-', '\\\\-', $query_item);
    $query_item = str_replace('*', '\\\\*', $query_item);
    $query_item = str_replace('?', '\\\\?', $query_item);
    
    $query_params .= "`image_id` IN (SELECT `image_id` FROM `$db_meta` m, `$db_imglink` l WHERE m.meta_id=l.meta_id AND `meta_data` REGEXP '[[:<:]]" . $query_item . "[[:>:]]')";
  }


  if ($insert_count == 0) {
    $create_query = "CREATE TEMPORARY TABLE `#temp` (`image_id` INT)";
  } else {
    $create_query = "CREATE TEMPORARY TABLE `#temp` SELECT `image_id`, `image_twidth`, `image_theight` FROM `$db_img` WHERE $query_params ORDER BY `image_id` DESC";
  }
  mysql_query($create_query);

  $db_query    = "SELECT `image_id`, `image_twidth`, `image_theight` FROM `#temp`";
  $count_query = "SELECT COUNT(*) FROM `#temp`";
  list_thumbs($db_local[$language]['result'], $db_query, $count_query, "&search=$search_url");

  mysql_query("DROP TABLE `#temp`");

  require('includes/footer.php');
?>
