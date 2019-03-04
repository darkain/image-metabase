<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  if (!isset($language)  ||  !isset($db_local[$language])) {
    echo "No language defined";
    exit;
  }


  $PHPSELF = $_SERVER["PHP_SELF"];


  $id = (isset($_POST['id'])) ? $_POST['id'] : 0;
  if ($id == 0) $id = (isset($_GET['id'])) ? $_GET['id'] : 0;
  $id = mysql_safe($id);

  $mode = (isset($_POST['mode'])) ? $_POST['mode'] : 1;
  if ($mode == 1) $mode = (isset($_GET['mode'])) ? $_GET['mode'] : 1;
  $mode = mysql_safe($mode);

  $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
  if ($page < 1) $page = 1;
  $page = mysql_safe($page);



  function id_exists($db_table, $db_column, $db_id) {
    $db_query  = "SELECT COUNT(*) FROM `$db_table` WHERE `$db_column`='$db_id' LIMIT 1";
    $db_result = mysql_query($db_query);
    $db_count  = mysql_result($db_result, 0, "COUNT(*)");
    mysql_free_result($db_result);
    return ($db_count > 0);
  }


  function redirect_self() {
    global $PHPSELF, $id;
    if ($id > 0) {
      header("Location: $PHPSELF?id=$id");
    } else {
      header("Location: $PHPSELF");
    }
    exit;
  }

  function redirect_404() {
    header("Location: error404.php");
    exit;
  }


  // this function was taken from:  http://us2.php.net/manual/en/function.mysql-real-escape-string.php
  function mysql_safe($value) {
    $value = trim($value);
  
    // Stripslashes
    if (get_magic_quotes_gpc()) {
      $value = stripslashes($value);
    }
     
    // Quote if not a number or a numeric string
    if (!is_numeric($value)) {
      $value = mysql_real_escape_string($value);
    }
     
    return $value;
  }



  function resize_image($file) {
    global $thumb_size;

    $imagedata = @getimagesize($file);
    if ($imagedata === FALSE) return FALSE;
    
    $w = $imagedata[0];
    $h = $imagedata[1];
    $t = $imagedata[2];
  
    if ($h > $w) {
      $new_w = floor(($thumb_size / $h) * $w);
      $new_h = $thumb_size;  
    } else {
      $new_h = floor(($thumb_size / $w) * $h);
      $new_w = $thumb_size;
    }

    $imagedata['hash'] = md5_file($file);
    $imagedata['size'] = filesize($file);
    $imagedata['w']    = $w;
    $imagedata['h']    = $h;
    $imagedata['tw']   = $new_w;
    $imagedata['th']   = $new_h;
  
    if      ($t == 1) $image = @imagecreatefromgif($file);
    else if ($t == 2) $image = @imagecreatefromjpeg($file);
    else if ($t == 3) $image = @imagecreatefropng($file);
    else return FALSE;
    
    if (!$image) return FALSE;
    
    $im2 = imagecreatetruecolor($new_w, $new_h);
    imagecopyresampled ($im2, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
    imagedestroy($image);
    
    imagejpeg($im2, $file);
    imagedestroy($im2);
    
    return $imagedata;
  }


  function update_desc($db_table, $db_desc, $db_id) {
    global $user_rights, $page_rights, $id;
    
    if (isset($_POST['submit']) && isset($_POST['desc'])) {
      if ($user_rights == 'mod'  ||  $user_rights == 'admin'  ||  $page_rights == 'owner') {
        $desc = mysql_safe($_POST['desc']);
        if (strlen($desc) == 0) {
          $db_query = "UPDATE `$db_table` SET `$db_desc`=NULL WHERE `$db_id`='$id' LIMIT 1";
        } else {
          $db_query = "UPDATE `$db_table` SET `$db_desc`='$desc' WHERE `$db_id`='$id' LIMIT 1";
        }
        mysql_query($db_query);
      }
      redirect_self();
    }
  }


  function list_thumbs($title, $db_query, $count_query, $url_params='', $img_params='') {
    global $PHPSELF, $page_width, $page_height, $page_size, $thumb_dir, $thumb_size;
    global $db_local, $language, $page;

    $page_offset = ($page - 1) * $page_size;
    $db_query   .= " LIMIT $page_offset, $page_size";

    $db_result   = mysql_query($count_query);
    $img_count   = mysql_result($db_result, 0, "COUNT(*)");
    mysql_free_result($db_result);

    $db_result = mysql_query($db_query);
    $db_count  = mysql_numrows($db_result);

    if ($page_offset + $page_size < $img_count) {
      echo "<h1 class=\"edit\"><a href=\"$PHPSELF?page=" . ($page+1) . "$url_params#images\">" . $db_local[$language]['nextpage'] . "</a></h1>\n";
    }

    if ($page > 1) {
      echo "<h1 class=\"edit\"><a href=\"$PHPSELF?page=" . ($page-1) . "$url_params#images\">" . $db_local[$language]['prevpage'] . "</a></h1>\n";
    }

    echo "<h1><a id=\"images\"></a>$title</h1><div class=\"image\">\n";

    if ($db_count == 0) {
      echo "<i>" . $db_local[$language]['none'] . "</i>\n";

    } else {
      echo "<table class=\"imagegroup\" cellpadding=\"0\"><tr>\n";
      $count = 0;

      for ($i=0; $i<$db_count; $i++) {
        $img_id     = mysql_result($db_result, $i, "image_id");
        $img_width  = mysql_result($db_result, $i, "image_twidth");
        $img_height = mysql_result($db_result, $i, "image_theight");
        $margin     = floor(($thumb_size - $img_height) / 2);
        $style      = 'width:' . $img_width . 'px;height:' . $img_height . 'px;margin-top:' . $margin . 'px';
        
        echo "<td><a href=\"image.php?id=$img_id$img_params\"><img src=\"$thumb_dir/$img_id.jpg\" alt=\"Image: $img_id\" style=\"$style\" /></a></td>\n";

        $count++;
        if ($count == $page_width  &&  $i < $db_count-1) {
          echo "</tr><tr>\n";
          $count = 0;
        }
      }

      echo "</tr></table>\n";
    }

    if ($img_count == 1) {
      echo "<span class=\"nav buttons\">$img_count " . $db_local[$language]['totalimg'];
    } else {
      echo "<span class=\"nav buttons\">$img_count " . $db_local[$language]['totalimgs'];
    }

    echo  " - \n" . $db_local[$language]['page'] . ' ';

    $page_count = (int)($img_count / $page_size);
    if (($page_count * $page_size) > $img_count) $page_count++;
    $page_begin = ($page > 3) ? ($page - 3) : (1);
    $page_end   = ($page < $page_count-2) ? ($page+3) : ($page_count+1);

    if ($page > 1) echo "<a href=\"$PHPSELF?page=" . (1) . "$url_params#images\">" . $db_local[$language]['navfirst'] . "</a>\n";
    if ($page > 2) echo "<a href=\"$PHPSELF?page=" . ($page-1) . "$url_params#images\">" . $db_local[$language]['navprev'] . "</a>\n";

    for ($i=$page_begin; $i<=$page_end; $i++) {
      if ($i == $page) {
        echo "<i>$i</i>\n";
      } else {
        echo "<a href=\"$PHPSELF?page=" . ($i) . "$url_params#images\">$i</a>\n";
      }
    }

    if ($page < $page_count+0) echo "<a href=\"$PHPSELF?page=" . ($page+1) . "$url_params#images\">" . $db_local[$language]['navnext'] . "</a>\n";
    if ($page < $page_count+1) echo "<a href=\"$PHPSELF?page=" . ($page_count+1) . "$url_params#images\">" . $db_local[$language]['navlast'] . "</a>\n";
    echo "</span>\n";
    echo "</div>\n";
    mysql_free_result($db_result);
  }



  function display_tags($db_tables, $db_pending, $db_conditions, $db_query_param='') {
    global $PHPSELF, $language, $db_local, $user_id;
    global $id, $db_meta, $db_metatype;
    global $mode;


    $db_query  = "SELECT COUNT(*) FROM $db_tables WHERE $db_conditions";
    $db_result = mysql_query($db_query);
    $count_tags = mysql_result($db_result, 0, "COUNT(*)");
    mysql_free_result($db_result);

    $db_query  = "SELECT COUNT(*) FROM $db_pending WHERE $db_conditions";
    $db_result = mysql_query($db_query);
    $count_pend = mysql_result($db_result, 0, "COUNT(*)");
    mysql_free_result($db_result);
    

    if ($mode == 'removetags') {
      echo "<h1 class=\"edit\"><a href=\"$PHPSELF?id=$id#tags\">" . $db_local[$language]['prevpage'] . "</a></h1>\n";
      echo "<h1><a id=\"tags\"></a>" . $db_local[$language]['remtags'] . "</h1><div class=\"reltags\">\n";

    } else if ($mode == 'pendingtags') {
      echo "<h1 class=\"edit\"><a href=\"$PHPSELF?id=$id#tags\">" . $db_local[$language]['prevpage'] . "</a></h1>\n";
      echo "<h1><a id=\"tags\"></a>" . $db_local[$language]['pendingtags'] . "</h1><div class=\"reltags\">\n";
      

    } else {
      if ($count_tags > 0) {
        if ($user_id == 0) {
          echo "<h1 class=\"edit\"><a href=\"login.php\">" . $db_local[$language]['loginedit'] . "</a></h1>\n";
        } else {
          echo "<h1 class=\"edit\"><a href=\"$PHPSELF?id=$id&amp;mode=removetags#tags\">" . $db_local[$language]['remtags'] . "</a></h1>\n";
        }
      }
      if ($count_pend > 0) echo "<h1 class=\"edit\"><a href=\"$PHPSELF?id=$id&amp;mode=pendingtags#tags\">" . $db_local[$language]['pendingtags'] . "</a></h1>\n";
      echo "<h1><a id=\"tags\"></a>" . $db_local[$language]['reltags'] . "</h1><div class=\"reltags\">\n";
    }


    $db_query = '';
    if ($mode == 'pendingtags') {
      $db_query  = "SELECT m.meta_id, m.meta_data, t.metatype_id, t.metatype_name, l.pend_action, l.pend_vote, COUNT(pend_vote) FROM $db_pending WHERE $db_conditions GROUP BY l.pend_vote, l.meta_id, m.meta_data ORDER BY t.metatype_name ASC, m.meta_data ASC, l.pend_vote ASC";
    } else {
      $db_query  = "SELECT m.meta_id, m.meta_data, t.metatype_id, t.metatype_name FROM $db_tables WHERE $db_conditions ORDER BY t.metatype_name ASC, m.meta_data ASC";
    }
    $db_result = mysql_query($db_query);
    $db_count  = mysql_numrows($db_result);


    if ($db_count != 0  &&  $mode != 'removetags'  &&  $mode != 'pendingtags') {
      echo "<table class=\"related buttons\" cellspacing=\"0\">\n";
    }

    $lasttype = '';


    if ($mode == 'removetags') {
      echo "<form method=\"post\" action=\"$PHPSELF\"><div>\n";
      echo "<input type=\"hidden\" name=\"submit\" value=\"" . $db_local[$language]['remseltags'] . "\" />\n";
      echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
      echo "<input type=\"hidden\" name=\"vote_action\" value=\"remove\" />\n";

      for ($i=0; $i<$db_count; $i++) {
        $md_id   = mysql_result($db_result, $i, 'meta_id');
        $md_data = mysql_result($db_result, $i, 'meta_data');
        $mt_name = mysql_result($db_result, $i, 'metatype_name');
        $mt_id   = mysql_result($db_result, $i, 'metatype_id');

        if ($mt_id != $lasttype) {
          if ($lasttype != 0) echo "<br/>";
          echo "<h4><a href=\"metatype.php?id=$mt_id\">$mt_name</a></h4>\n";
          $lasttype = $mt_id;
        }
        echo "<input type=\"checkbox\" name=\"addmeta[$md_id]\" id=\"yes$md_id\" value=\"yes\" /><label for=\"yes$md_id\"> $md_data</label><br/>\n";
      }

      echo "<br/><input type=\"submit\" value=\"" . $db_local[$language]['remseltags'] . "\" />\n";
      echo "</div></form>\n";



    } else if ($mode == 'pendingtags') {
      echo "<form method=\"post\" action=\"$PHPSELF\"><div>\n";
      echo "<input type=\"hidden\" name=\"submit\" value=\"" . 'Add Votes' . "\" />\n";
      echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";


      for ($i=0; $i<$db_count; $i++) {
        $md_id   = mysql_result($db_result, $i, 'meta_id');
        $md_data = mysql_result($db_result, $i, 'meta_data');
        $mt_name = mysql_result($db_result, $i, 'metatype_name');
        $mt_id   = mysql_result($db_result, $i, 'metatype_id');
        $pn_act  = mysql_result($db_result, $i, 'pend_action');
        $y_cnt   = mysql_result($db_result, $i, 'COUNT(pend_vote)');
        $n_cnt   = 0;

        if ($i < $db_count-1) {
          if (mysql_result($db_result, $i+1, 'meta_id') == $md_id) {
            if (mysql_result($db_result, $i+1, 'pend_vote') == 'no') {
              $n_cnt = mysql_result($db_result, $i+1, 'COUNT(pend_vote)');
              $i++;
            }
          }
        }

        if ($mt_id != $lasttype) {
          if ($lasttype != 0) echo "<br/>";
          echo "<h4><a href=\"metatype.php?id=$mt_id\">$mt_name</a></h4>\n";
          $lasttype = $mt_id;
        }

        if ($pn_act == 'add') {
          $pn_act = $db_local[$language]['addtag'];
        } else {
          $pn_act = $db_local[$language]['remtag'];
        }

        if ($user_id != 0) {
          echo "<span class=\"yes\"><input type=\"radio\" name=\"addmeta[$md_id]\" id=\"yes$md_id\" value=\"yes\" /><label for=\"yes$md_id\"> " . $db_local[$language]['yes'] . " ($y_cnt)</label></span>\n";
          echo "<span class=\"no\"><input type=\"radio\" name=\"addmeta[$md_id]\" id=\"no$md_id\" value=\"no\" /><label for=\"no$md_id\"> " . $db_local[$language]['no'] . " ($n_cnt)</label></span>\n";
          echo "<span class=\"unsure\"><input type=\"radio\" name=\"addmeta[$md_id]\" id=\"unsure$md_id\" value=\"unsure\" /><label for=\"unsure$md_id\"> " . $db_local[$language]['unsure'] . "</label></span>\n - ";
        }
        echo "$pn_act: <a href=\"meta.php?id=$md_id\">$md_data</a><br/>\n";
      }

      if ($db_count == 0) {
        echo "<i>" . $db_local[$language]['none'] . "</i>\n";
      } else if ($user_id != 0) {
        echo "<br/><input type=\"submit\" value=\"" . 'Add Votes' . "\" />\n";
      }
      echo "</div></form>\n";



    } else {
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
    }

    mysql_free_result($db_result);
    if ($mode != 'removetags'  &&  $mode != 'pendingtags') {
      if ($lasttype != '') {
        echo "</td></tr>\n</table>\n";
      } else {
        echo "<i>" . $db_local[$language]['none'] . "</i>\n";
      }
    }


    if ($user_id != 0) {    
      echo "<div class=\"line\">&nbsp;</div>\n\n";

      if ($mode != 'addtags') echo "<h5>" . $db_local[$language]['addtags'] . "</h5>\n";
      echo "<form method=\"post\" action=\"$PHPSELF\"><div>\n";
      echo "<input type=\"hidden\" name=\"submit\" value=\"" . $db_local[$language]['submit'] . "\" />\n";
      echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";

      if (isset($_POST['submit']) && isset($_POST['tags'])) {
        echo "<input type=\"hidden\" name=\"addmeta[-1]\" value=\"0\" />\n";
        echo "<input type=\"hidden\" name=\"vote_action\" value=\"add\" />\n";

        $tags = explode(',', $_POST['tags']);
        reset($tags);

        $db_count  = 0;
        $tag_count = 0;
        $tmp_query = '';
        while ($item = each($tags)) {
          $tag = mysql_safe($item[1]);
          if (strlen($tag) > 0) {
            if ($tag_count > 0) $tmp_query .= ' OR ';
            $tmp_query .= "m.meta_data LIKE '%$tag%'";
            $tag_count++;
          }
        }

        if ($tag_count > 0) {
          $db_query = "SELECT m.meta_data, m.meta_id, t.metatype_name, t.metatype_id FROM `$db_meta` m, `$db_metatype` t WHERE ($tmp_query) $db_query_param AND m.metatype_id=t.metatype_id ORDER BY t.metatype_name, m.meta_data ASC";
          $db_result = mysql_query($db_query);
          $db_count  = mysql_numrows($db_result);

          $lasttype = 0;
          for ($i=0; $i<$db_count; $i++) {
            $md_id   = mysql_result($db_result, $i, "meta_id");
            $md_data = mysql_result($db_result, $i, "meta_data");
            $mt_id   = mysql_result($db_result, $i, "metatype_id");
            $mt_name = mysql_result($db_result, $i, "metatype_name");

            if ($mt_id != $lasttype) {
              if ($lasttype != 0) echo "<br/>";
              echo "<h4><a href=\"metatype.php?id=$mt_id\">$mt_name</a></h4>\n";
              $lasttype = $mt_id;
            }
            echo "<input type=\"checkbox\" name=\"addmeta[$md_id]\" id=\"yes$md_id\" value=\"yes\" /> <label for=\"yes$md_id\">$md_data</label><br/>\n";
          }
          
          mysql_free_result($db_result);
        }

        if ($db_count == 0  ||  $tag_count == 0) {
          echo "<i>" . $db_local[$language]['noresult'] . "</i>\n";
        } else {
          echo "<br/><input type=\"submit\" name=\"submit\" value=\"" . $db_local[$language]['addseltags'] . "\" />\n";
        }

      } else {
        echo "<input type=\"hidden\" name=\"mode\" value=\"addtags\" />\n";
        echo "<input type=\"text\" name=\"tags\" size=\"50\" />\n";
        echo "<input type=\"submit\" name=\"submit\" value=\"" . $db_local[$language]['search'] . "\" />\n";
      }
      echo "</div></form>\n";
    }
    echo "</div>\n";
  }

?>
