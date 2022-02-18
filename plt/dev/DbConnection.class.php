<?php

class DbConnection { 
  public $_data = [];

  public function __construct($data = []) { $this->_data = $data; }

  public function getTableLastUpdate($tblName) { return time(); }
  public function getTableContent($tblName)    { return $this->_data[$tblName]; }
}
