<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  function pending_tags($db_mode, $db_link, $db_pend, $db_id, $db_tag) {
    global $user_id, $user_rights, $page_rights, $id, $vote_limit;
    global $PHPSELF, $db_meta;

    if ($db_mode < 1  ||  $db_mode > 2) return;
    if ($user_rights == 'mod'  ||  $user_rights == 'admin'  ||  $page_rights == 'owner') $vote_limit = 1;

    $alias  = 'tbl';
    $alias2 = $alias . '2';
    
    if ($user_id == 0) return;
    if (!isset($_POST['submit'])) return;
    if (!isset($_POST['addmeta'])) return;

    $addmeta = $_POST['addmeta'];
    if (!is_array($addmeta)) return;
    reset($addmeta);

    mysql_query("LOCK TABLES `$db_link` WRITE, `$db_pend` WRITE, `$db_pend` AS $alias WRITE, `$db_pend` AS $alias2 WRITE, `$db_meta` READ");

/*
    // verify image still exists, must check within db lock for safety
    if (!id_exists($db_img, $db_id, $id)) {
      mysql_query("UNLOCK TABLES");
      return;
    }
*/

    // enum each item
    while ($meta = each($addmeta)) {
      $new_tag  = mysql_safe($meta[0]);
      $new_vote = mysql_safe($meta[1]);

      if ($db_mode == 2  &&  $id == $new_tag) $new_tag = 0;

      if ($new_tag > 0  &&  id_exists($db_meta, 'meta_id', $new_tag)  &&  ($new_vote == 'yes'  ||  $new_vote == 'no')) {

        $db_condition = '';
        if ($db_mode == 1) {
		  $db_condition = "$db_id='$id' AND $db_tag='$new_tag'";
		} else if ($db_mode == 2) {
		  $db_condition = "(($db_id='$id' AND $db_tag='$new_tag') OR ($db_tag='$id' AND $db_id='$new_tag'))";
		}

        //verify we havn't already voted for this item
        $db_query  = "SELECT COUNT(*) FROM `$db_pend` AS $alias WHERE $db_condition AND user_id='$user_id'";
        $db_result = mysql_query($db_query);
        $db_count  = mysql_result($db_result, 0, "COUNT(*)");
        mysql_free_result($db_result);


        if ($db_count == 0) {
          //this is indeed a new legit vote

          $db_check_query  = "SELECT COUNT(*) FROM `$db_link` WHERE $db_condition LIMIT 1";
          $db_check_result = mysql_query($db_check_query);
          $db_check_count  = mysql_result($db_check_result, 0, "COUNT(*)");
          mysql_free_result($db_check_result);

          //check if vote is fully aproved - TODO: LIMIT 2 ??
          $db_count_query = '';
          if ($db_mode == 1) {
            $db_count_query = "SELECT pend_action, pend_vote, COUNT(pend_vote) FROM `$db_pend` AS $alias WHERE $db_id='$id' AND $db_tag='$new_tag' GROUP BY $db_tag, pend_vote";
          } else if ($db_mode == 2) {
            $db_count_query = "SELECT pend_action, pend_vote, COUNT(pend_vote) FROM (SELECT $db_tag, pend_action, pend_vote FROM `$db_pend` AS $alias WHERE $db_id='$id' AND $db_tag='$new_tag' UNION ALL SELECT $db_id, pend_action, pend_vote FROM `$db_pend` AS $alias2 WHERE $db_tag='$id' AND $db_id='$new_tag') as $alias GROUP BY $db_tag, pend_vote";
          }

          $db_count_result = mysql_query($db_count_query);
          $db_count_count  = mysql_numrows($db_count_result);


          if ($db_count_count > 0) {
            //voting on pending

            $vote_action = mysql_result($db_count_result, 0, "pend_action");
            $vote_count  = 0;

            if (mysql_result($db_count_result, 0, "pend_vote") == $new_vote) {            
              $vote_count = mysql_result($db_count_result, 0, "COUNT(pend_vote)");
            } else if ( ($db_count_count > 1)  &&  (mysql_result($db_count_result, 1, "pend_vote") == $new_vote) ) {
              $vote_count = mysql_result($db_count_result, 1, "COUNT(pend_vote)");
            }

            if ($vote_count >= $vote_limit-1) {
              //vote aproved

              if ($vote_action == 'add') {
                //vote is to add a new tag

                if ($db_check_count == 0) {
                  if ($new_vote == 'yes') {
                    $db_query = "INSERT INTO `$db_link` (`$db_id`, `$db_tag`) VALUES ('$id', '$new_tag')";
                    mysql_query($db_query);
                  }

                  $db_query = "DELETE $alias FROM `$db_pend` AS $alias WHERE $db_condition";
                  mysql_query($db_query);
                }

              } else if ($vote_action == 'remove') {
                //vote is to remove an existing tag

                if ($new_vote == 'yes') {
                  $db_query = "DELETE FROM `$db_link` WHERE $db_condition LIMIT 1";
                  mysql_query($db_query);
                }

                $db_query = "DELETE $alias FROM `$db_pend` AS $alias WHERE $db_condition";
                mysql_query($db_query);

              }

            } else {
              // (dis)agree with current vote

              if (($vote_action == 'add'  &&  $db_check_count < 1)  ||  ($vote_action == 'remove'  &&  $db_check_count > 0)) {
                $db_query = "INSERT INTO `$db_pend` (`$db_id`, `$db_tag`, `user_id`, `pend_action`, `pend_vote`) VALUES ('$id', '$new_tag', '$user_id', '$vote_action', '$new_vote')";
                mysql_query($db_query);
              }

            }

          
          } else {
            //new vote

            if (isset($_POST['vote_action'])) {
              $vote_action = mysql_safe($_POST['vote_action']);
              $db_query = '';

              if ($vote_limit <= 1) {
                if ($vote_action == 'add') {

                  if ($db_check_count == 0) {
                    $db_query = "INSERT INTO `$db_link` (`$db_id`, `$db_tag`) VALUES ('$id', '$new_tag')";
                  }

                } else if ($vote_action == 'remove') {
                  $db_query = "DELETE FROM `$db_link` WHERE $db_id='$id' AND $db_tag='$new_tag' LIMIT 1";
                }

              } else {
                if (($vote_action == 'add'  &&  $db_check_count < 1)  ||  ($vote_action == 'remove'  &&  $db_check_count > 0)) {
                  $db_query = "INSERT INTO `$db_pend` (`$db_id`, `$db_tag`, `user_id`, `pend_action`, `pend_vote`) VALUES ('$id', '$new_tag', '$user_id', '$vote_action', '$new_vote')";
                }
              }

              if (strlen($db_query) > 0) mysql_query($db_query);
            }

          }

          mysql_free_result($db_count_result);
        }

      }
    }

    mysql_query("UNLOCK TABLES");
    redirect_self();
  }

?>
