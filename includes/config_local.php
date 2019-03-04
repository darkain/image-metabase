<?php
  /*******************************************************\
  *                                                       *
  *   Image Metabase™ Copyright © 2006 Vincent Milum Jr   *
  *                                                       *
  \*******************************************************/


  $quick_lock  = FALSE;


  $time_start  = microtime(true);

  $site_name   = 'The Cosplay Metabase';
  $site_keys   = 'Cosplay, Search, Costume';

  $db_server   = 'localhost';
  $db_username = 'darkain_darkain';
  $db_password = 'password';
  $db_database = 'darkain_cosplay';
  $db_prefix   = 'cosplay_';

  $db_img      = $db_prefix . 'img';
  $db_imglink  = $db_prefix . 'imglink';
  $db_imgpend  = $db_prefix . 'imgpend';
  $db_meta     = $db_prefix . 'meta';
  $db_metalink = $db_prefix . 'metalink';
  $db_metapend = $db_prefix . 'metapend';
  $db_metatype = $db_prefix . 'metatype';
  $db_users    = $db_prefix . 'users';
  $db_session  = $db_prefix . 'sessions';

  $thumb_dir   = 'thumbs';
  $thumb_size  = 200;

  $page_width  = 4;
  $page_height = 5;
  $page_size   = $page_width * $page_height;

  $vote_limit  = 3;

  $language    = 'en-us';


  $ses_ignore[] = 'Google';
  $ses_ignore[] = 'MSN';
  $ses_ignore[] = 'Yahoo';
  $ses_ignore[] = 'Altavista';
  $ses_ignore[] = 'W3C';
  $ses_ignore[] = 'Validator';
  $ses_ignore[] = 'Spider';
  $ses_ignore[] = 'Crawl';
  $ses_ignore[] = 'Robot';
  $ses_ignore[] = 'Search';
  $ses_ignore[] = 'Ask Jeeves';


  if ($quick_lock) {
    echo "$site_name - Site is currently unavailable";
    exit;
  }


  @mysql_connect($db_server, $db_username, $db_password) or die('Database Error: ' . mysql_error());
  @mysql_select_db($db_database) or die('Database Error: ' . mysql_error());


  require('local-en.php');
  require('local-jp.php');
  require('local-hax.php');
  require('common.php');
  require('session.php');

?>
