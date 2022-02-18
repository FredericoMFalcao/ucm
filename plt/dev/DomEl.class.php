<?php

/* @class: DomEl
*
*  @func: __construct ( tag )
*
*  INPUT:
*  @func: attr ( key , value )
*  @func: 
*
*  OUTPUT:
*  @func: asHtml()
*/

class DomEl {
  protected $_tag;
  protected $_attrs = [];
  public    $_children = [];
  protected $_templateText = "";
  protected $_dependsOn = [];

  protected $_rootNode;

  protected $_updates = [];
  protected $_lastUpdated = 0;
  protected $_localData = [];

  /*
  *
  * 1. INPUT
  *
  */

  /* 1.1 GRAPHICS DOM */
  public function __construct($tag = null) {
    $this->_tag = $tag;
  }

  public function attr(string $key, $value = null) {
    if (is_null($value)) return $this->_attrs[$key];
    $this->_attrs[$key] = str_replace('"','&quot;', $value);
    $this->_updates[] = '.setAttribute("'.$key.'","'.str_replace('"', '\\"', $value).'")';
    return $this;
  }
  public function text($str = null) { 
    if (is_null($str)) return $this->_templateText; 
    else $this->_templateText = $str; 
    $this->_updates[] = '.textContent = "'.str_replace('"','\\"',$this->_templateText).'"';
    return $this; 
  }
  public function renderText() {
    foreach($this->_localData as $aliasName => $content)
      $$aliasName = $content;
    ob_start(); eval("?".">".$this->_templateText); $this->_renderedText = ob_get_clean();
    $this->_updates[] = '.textContent = "'.str_replace('"','\\"',$this->_renderedText).'"';
    return $this;
  }

  /* 1.2 TREE RELATED */
  public function addChild(DomEl $el) {
    $this->_children[] = $el;
    return $this;
  }

  public function rootNode(&$root = null) {
    if (is_null($root)) return $this->_rootNode;
    $this->_rootNode = $root;
    return $this;
  }
  public function propagateRootNode() {
    foreach($this->_children as $k => $dummy)
      $this->_children[$k]->rootNode($this->_rootNode)->propagateRootNode();
    return $this;
  }

  /* 1.3 DYNAMIC DATA RELATED */
  public function dependsOn($tblName, $aliasName = null) {
    if (is_null($aliasName)) return $this->_dependsOn[$tblName];
    $this->_dependsOn[$tblName] = $aliasName;
    return $this;
  }
  public function syncWithDatabase(DbConnection $dbConn) {
    $updated = false;
    foreach($this->_dependsOn as $tblName => $alias)
      if ($this->_lastUpdated < $dbConn->getTableLastUpdate($tblName)) {
        $updated = true; $this->_localData[$alias] = $dbConn->getTableContent($tblName);
      }
    $this->_lastUpdated = time();

    foreach($this->_children as $k => $dummy)
      $this->_children[$k]->syncWithDatabase($dbConn);

    if ($updated) $this->renderText();
        

    return $this;
  }


  /*
  *
  * 2. OUTPUT
  *
  */
  public function preOutput()  { $this->_lastUpdated = time(); }
  public function postOutput() { $this->_updates = []; }

  public function asHtml() {

    $this->preOutput();

    $output = "";
    /* 1. Open tag */
    if (!is_null($this->_tag)) {
      $output .= "<".$this->_tag;

      /* 2. Add attributes */
      foreach($this->_attrs as $k => $v) $output .= " $k=\"$v\"";

      $output .= ">";
    }

    /* 10. Children */
    foreach($this->_children as $child) $output .= $child->asHtml();

    /* 15. Add text */
    if (!empty($this->_templateText))  {
      $output .= $this->_templateText;
    }

    /* 20. Close tag */
    if (!is_null($this->_tag))  $output .= "</".$this->_tag.">";

    $this->postOutput();

    return $output;
  }

  public function flushAndPrintUpdatesAsJs($elIdentifier) {

    $this->preOutput();

    $output = "";
    foreach($this->_children as $childNo => $child) 
      $output .= $child->flushAndPrintUpdatesAsJs($elIdentifier.".children[$childNo]");
    foreach($this->_updates as $up) 
      $output .= $elIdentifier.$up.";";

    // Flush / clear updates
    $this->_updates = [];
    
    $this->postOutput();

    return $output;
  }


}

