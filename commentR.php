<?php

define('ROOT_DIR', realpath(dirname(__FILE__)));

/*
    All DB rows have a "status" column, which could be used to flag unmoderated content, etc.
    (Currently this is ignored)
    This is the default value for newly created comments and permalinks
*/
define('DEFAULT_CREATE_STATUS', 0);

/*
    As an anti-spam attempt, you can require that the first comment for any post be submitted by this ("secret") name
    So the blog creator must then submit a (possibly empty) comment under this name to open the post for comments.
    Set this to null or "" to allow anybody to start comments
*/
define('REQUIRED_NAME_FOR_FIRST_POST', "");  // anybody can start posts

/*
   path to default SQLite DB file, should start with a /
*/
define('DEFAULT_SQ3_DBNAME', "/db/commentR-demo.db3");


require_once("utils.php");
require_once("db3.php");

  $method = $_SERVER['REQUEST_METHOD'];
  $info = Utils::prepare($_SERVER);
  $result = null;
  $format = "";
  
  switch($method) {
      case "GET":  $result = doGet($info['path']); 
                   break;
       
      case "POST": $id = doPost($info, $_POST); 
                   $result = array( "status" => $id);
                   $format = get($_POST, 'format');
                   break;
      //case "PUT":  not implemented
      //case "DELETE" :  not implemented
      default:     Utils::err(403, "Illegal Method: ".$method);
  }
  
  
  // if they want HTML (like from the web page form) redirect after the POST
  if ($format == 'HTML') { 
     header('Location: ' . $info['redirectURL']);
     die();
  }
  else {
     $json = json_encode($result, JSON_PRETTY_PRINT+JSON_HEX_TAG+JSON_HEX_AMP+JSON_HEX_APOS+JSON_HEX_QUOT);
     header('Content-Type: application/json');
     echo $json;
  }
  exit;

  
  // Option to change this to MySQL, Postgress etc.
  function dbConnect($filename = ROOT_DIR . DEFAULT_SQ3_DBNAME) {
      $params = array("filename" => $filename);
      return new DB3($params);
  }
  
  
  /*
    Given the unique id (usually the path) to a web page, 
    return the database ID, or 0 for none
  */
   function getPermalinkID($db, $safeButUnescapedPath) {
      $path = $db->doEscapeParam($safeButUnescapedPath);
      $query = "SELECT * FROM permalinks WHERE path ='$path'";
      $rows = $db->doQuery($query);
      return (count($rows) === 1) ? $rows[0]['id'] : 0;
   }
  
  
  /*
    Create the permalink ID (typically upon receiving the first comment)
  */
   function createPermalinkID($db, $safeButUnescapedPath) {
      $path = $db->doEscapeParam($safeButUnescapedPath);
      $time = time();
      $status = DEFAULT_CREATE_STATUS;
      $query = "INSERT INTO permalinks (  path,   date,  status)
                VALUES                 ('$path', $time, $status)";
      return $db->doInsert($query);
   }
  

  /*
    Get all comments for the given uid, usually the path of the page
  */
    
   function doGet($safeButUnescapedPath) {
      $db = dbConnect();
      $comments = array();

      $plid = getPermalinkID($db, $safeButUnescapedPath);      
      if ($plid) {
         $query = "SELECT * FROM comments WHERE plid ='$plid'";
         $comments = $db->doQuery($query);
      }
      
      $db->close();  // TODO: put in a finally clause???
      
      return $comments;   
   }


   /*
     Post a comment
     Returns DB ID of new comment, or 0 on failure.
   */
   function doPost($info, $unsafePostData) {
      $ipAddr = $info['ipAddr'];
      // TODO - test ipAddr vs. some anti-spammy thing
      
      $formTime = get($unsafePostData, 'time', 0);
      $time = time();
      $diff = $time - $formTime;
      if ( ($diff < 0) ||
           ($diff > 60*60) )  // anti-spam attempt: check that the hidden field is set to somewhere in the last hour
         return 0;
         
      $safeButUnescapedPath = $info['path'];
      
      $db = dbConnect();
      $text = $db->doEscapeParam(Utils::sanitizeComment(get($unsafePostData, 'text')));
      $name = $db->doEscapeParam(Utils::sanitizeName(get($unsafePostData, 'name')));
      $email = $db->doEscapeParam(Utils::sanitizeEmail(get($unsafePostData, 'email')));
      $status = DEFAULT_CREATE_STATUS;
      
      $plid = getPermalinkID($db, $safeButUnescapedPath);
      if (!$plid) {  // first comment on this page - try creating
         $plid = createPLIDForFirstComment($db, $name, $safeButUnescapedPath);
      }
         
      if (!$plid)
         Utils::err("Sorry, commenting not open for this page", 500);
      
      if (!$text)  // no comment, ignore...
         return 0;
      
      /* 
         TODO - allow Markdown???
         e.g.   $text = Parsedown::instance()->text($text);
         Would need changes in commentR.js since Textarea doesn't take HTML formatting
      */
      
      $query = "INSERT INTO comments (  plid,    date,    status,    text,    name,    email)
                VALUES               ('$plid',  $time,  '$status', '$text', '$name', '$email')";
      $commentID = $db->doInsert($query);
       
      $db->close();  // TODO - put into finally clause???  
      return $commentID;
   }

   
   /*
     We have an option that the first comment for a post must be by a certain name
     As  possible security / anti-spam feature.  The blog creator "creates" a comment after posting
   */
   
   function createPLIDForFirstComment($db, $commenterName, $safeButUnescapedPath) {
       if (!REQUIRED_NAME_FOR_FIRST_POST || (REQUIRED_NAME_FOR_FIRST_POST == $commenterName)) {
            return createPermalinkID($db, $safeButUnescapedPath);
       }
       else
          return 0;       
   }


?>



