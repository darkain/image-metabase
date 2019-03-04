<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  function import_deviantart($page) {  
    $page = str_replace('/deviation/', '/view/', $page);
    
    $out_file = rand() . '.tmp';
    
    $file = fopen($page, "r");
    $text = '';
    while (!feof($file)) {
      $text .= fread($file, 8192);
    }
    fclose($file);
    
    $pos = strpos($text, 'id="deviation-view"');
    if ($pos === FALSE) return FALSE;
    
    $begin = strpos($text, 'src="', $pos);
    if ($begin === FALSE) return FALSE;
    $begin += 5;

    $end = strpos($text, '"', $begin);
    if ($end === FALSE) return FALSE;

    $in_file = substr($text, $begin, $end-$begin);
    if (!copy($in_file, $out_file)) return FALSE;

    $result = resize_image($out_file);
    if ($result === FALSE) {
      unlink($out_file);
      return FALSE;
    }
    
    $result['page'] = $page;
    $result['file'] = $out_file;
    return $result;
  }
  
  
  
  function import_cosplaycom($page) {
    $begin = strpos($page, 'photo=');
    if ($begin === FALSE) return FALSE;
    $begin += 6;
    
    $id_array = sscanf(substr($page, $begin), '%d');
    $image_id = $id_array[0];
    if ($image_id < 100) return FALSE;
    
    $out_file = rand() . '.tmp';
    $section = substr($image_id, 0, 2);
    $in_file = "http://images.cosplay.com/photos/$section/$image_id.jpg";
    if (!copy($in_file, $out_file)) return FALSE;
    
    $result = resize_image($out_file);
    if ($result === FALSE) {
      unlink($out_file);
      return FALSE;
    }
    
    $result['page'] = $page;
    $result['file'] = $out_file;
    return $result;
  }
  
  
  
  function import_url($path) {
    global $user_id, $db_img, $thumb_dir;
    
    if ($user_id == 0) return FALSE;
    
    $path = mysql_safe($path);
    $result = FALSE;
    
    if (stripos($path, 'http://www.deviantart.com/deviation/') === 0) $result = import_deviantart($path);
    else if (stripos($path, 'http://www.deviantart.com/view/') === 0) $result = import_deviantart($path);
//    else if (stripod($path, 'http://backend.deviantart.com/rss.xml') == 0) $result = import_deviantart_gallery($path);
    else if (stripos($path, 'http://images.cosplay.com/showphoto.php') === 0) $result = import_cosplaycom($path);
    
    if ($result === FALSE) return FALSE;
    
    mysql_query("LOCK TABLES `$db_img` WRITE");
    
    $db_query = "SELECT COUNT(*) FROM `$db_img` WHERE image_url='" . $result['page'] . "' OR image_hash='" . $result['hash'] . "' LIMIT 1";
    $db_result = mysql_query($db_query);
    $db_count  = mysql_result($db_result, 0, "COUNT(*)");
    mysql_free_result($db_result);

    if ($db_count == 0) {
      $db_query = "INSERT INTO `$db_img` (`image_url`, `image_desc`, `image_width`, `image_height`, `image_twidth`, `image_theight`, `image_size`, `image_hash`, `user_id`) VALUES ('" . $result['page'] . "', NULL, '" . $result['w'] . "', '" . $result['h'] . "', '" . $result['tw'] . "', '" . $result['th'] . "', '" . $result['size'] . "', '" . $result['hash'] . "', '" . $user_id . "')";
      mysql_query($db_query);
      
      $new_file = "./$thumb_dir/" .  mysql_insert_id() . ".jpg";
      rename($result['file'], $new_file);
    }
    
    else {
      unlink($result['file']);
    }
    
    mysql_query("UNLOCK TABLES");
  }
  
?>