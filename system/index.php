<?php

header("Content-Type: text/html; charset=UTF-8");
date_default_timezone_set('America/Sao_Paulo');

# Parameters
# ------------------------------------------------------------------------------

$_base   = $_SERVER['DOCUMENT_ROOT'] . '/assets/';




# Configuration 
# ------------------------------------------------------------------------------

$_config = parse_ini_file('config.ini',true);

$action = (isset($_GET['action'])) ? $_GET['action'] : false;
$user   = (isset($_GET['user']))   ? $_GET['user']   : false;
$hash   = (isset($_GET['hash']))   ? $_GET['hash']   : false;


# Validations
# ------------------------------------------------------------------------------

$error = false;

// No user defined in URL
if(!$user) $error = 1;

// No user defined in config
if(!isset($_config[$user])) $error = 2;

// No correspondent folder in `assets/`
if(!is_dir($_base.$user)) $error = 3;

// Redirect if error
if($error) header('Location: /?e='.$error);




# Get Files list
# ------------------------------------------------------------------------------

$user_folder = $_base.$user.'/';

try
{
  $hdl = new DirectoryIterator($user_folder);
  $files_list = array();
  foreach($hdl as $item )
  {
    $hash_key = md5($user.$item);
    $files_list[$hash_key] = array(
                      'mtime' => date('d/m/Y H:i:s',$item->getMTime()),
                      'path' => $item->getPath(),
                      'pathname' => $item->getPathname(),
                      'filename' => $item->getFilename(),
                      'del_path' => "/u/{$user}/e/{$hash_key}",
                      'get_path' => "/u/{$user}/d/{$hash_key}",
                   );
  }
}
/*** if an exception is thrown, catch it here ***/
catch(Exception $e)
{
  $list = $e;
}




# Verify actions
# ------------------------------------------------------------------------------

if($action == 'd')
{
  $file = $files_list[$hash]['pathname'];
  $filename = $files_list[$hash]['filename'];
  // Extract the type of file which will be sent to the browser as a header
  $type = filetype($file);
   
  // Get a date and timestamp
  $today = date("F j, Y, g:i a");
  $time = time();
   
  // Send file headers
  header("Content-type: $type");
  header("Content-Disposition: attachment;filename=$filename");
  header("Content-Transfer-Encoding: binary");
  header('Pragma: no-cache');
  header('Expires: 0');
  // Send the file contents.
  set_time_limit(0);
  readfile($file);
  die; 


}


if($action == 'e')
{
  
  validate_hash($hash);

  $file = $files_list[$hash]['pathname'];
  if(is_file($file))
  {
    if(@unlink($file))
    {
      unset($files_list[$hash]);
    }
  }
}



include('view/header.php');
include('view/list.php');

function validate_hash($hash)
{
  global $files_list, $user;
  if(!isset($files_list[$hash]))
  {
    header("Location: /u/{$user}");
  }
}