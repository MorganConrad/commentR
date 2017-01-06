<?php

/*
Utility functions
*/

// get is used a ton so for convenience put it outside of a class in global space

/**
* Gets $assoc[$key], returning $def if absent (instead of throwing an error)
* @param  assoc  $assoc  if null, $def will be returned
* @param  string $key    if null, return $assoc
* @param  mixed  $def    what to return if assoc[key] not present
* @return mixed
*/
function get($assoc, $key, $def=null) {
   if (isset($assoc)) {
      if ($key)
      return (isset($assoc[$key])) ?  $assoc[$key] : $def;
      else
      return $assoc;
   }
   return $def;
}


class Utils {

   static function err($msg, $code = 404) {
      http_response_code($code);
      if ($msg) {// also log
         error_log($msg);
         echo('Error: '.$msg);
      }
      exit;
   }

   
   /*
     If email is valid (via FILTER_VALIDATE_EMAIL) and < 64 chars return a cleansed version, else return null
   */
   static function sanitizeEmail($email) {
      if ($email) {
         $clean = filter_var($email, FILTER_SANITIZE_EMAIL);
         if ( (strlen($clean) < 64) && 
               filter_var($clean, FILTER_VALIDATE_EMAIL) )
            return $clean;
      }
      
      return null;
   }


   /*
     If name  is valid return a cleansed version, else return null
     Name must be less than 64 chars and consist of letters, `, comma, dot, space or hyphen
   */
   static function sanitizeName($name) {
      if ($name) {
         $forbidden = "/[^a-z',\. -]/i";
         $clean = trim(stripslashes($name));
         if ( (strlen($clean) < 64) &&
              !preg_match($forbidden, $clean) )
            return $clean;
      }
      
      return null;
   }


   /*
      Comment must be 2-511 chars.  What else should be tested?
   */
   static function sanitizeComment($comment) {
      if ($comment) {
         $comment = trim($comment);
         if ( (strlen($comment) > 1) && ( strlen($comment) < 512) ) {
            
            $clean = $comment;
            // $clean = htmlentities($clean, ENT_NOQUOTES); // ??
            // TEST MORE??  
            return $clean;
         }
      }
      
      else
         return null;
   }



/**
 * Sets up an array of often used information from the server data
 * Does some validation
 * @param  assoc $server usually $_SERVER
 * @return assoc
 */
   static function prepare($server) {
      $method = $server['REQUEST_METHOD'];
      
      /*
        Do a little data validation.  Probably should do more!
      */

      $path = substr(get($server, "PATH_INFO", "/"),1);  // remove the leading /
      
      // disallow funny stuff in path
      if ((strpos($path, '..') !== false) or (strpos($path, '~') !== false) || !$path)
         err("bad input");
     
      
      // check that IP address looks valid
      $ipAddr = $server['REMOTE_ADDR'];
      if (!filter_var($ipAddr, FILTER_VALIDATE_IP))
         err("bad input");
      // TODO - check ipAddr vs. known Spammers?
      
      $queryStr = $server["QUERY_STRING"];
      $query = array();
      parse_str($queryStr, $query);

      // mock "path" after the script name  (unused)
      $mockURI = $queryStr ? "{$path}?{$queryStr}" : $path;
      
      // Compute redirect path for posts
      $redirectURL = (get($server, 'HTTPS') === 'on') ? "https" : "http" . "://$server[HTTP_HOST]/$path";

      return array (
         "method" => $method,
         "path" => $path,
         "query" => $query,
         "queryStr" => $queryStr,
         "requestURI" => $server['REQUEST_URI'],
         "mockURI" => $mockURI,
         "user-agent" => $server['HTTP_USER_AGENT'],
         "ipAddr" => $ipAddr,
         "redirectURL" => $redirectURL,
         "server" => $server
      );

   }


   static function isCommandLine() {
      return Utils::startsWith(php_sapi_name(), "cli");
   }


}

?>
