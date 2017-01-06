<?php

/*
   Encapsulate basic SQLite3 functionality so we can change to MySQL, XML etc. later
*/

class DB3 extends SQLite3 {
      
   public function __construct($params) {
      $this->open($params['filename']);
   }
     
   public function doEscapeParam($unsafe) {
      return SQLite3::escapeString($unsafe);
   }
     
   public function doQuery($query) {
      $resultSet = $this->query($query);
      $rows = [];
      while ($resultSet && ($row = $resultSet->fetchArray(SQLITE3_ASSOC)) )
         $rows[] = $row;
      
      return $rows;
   }
   
   public function doInsert($query) {
      $result = $this->exec($query);
      return $result? $this->lastInsertRowID() : 0;
   } 

}

?>
