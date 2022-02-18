<?php


class Html extends DomEl { 
  
  public function __construct()  { parent::__construct("html"); $this->rootNode($this); $this->addChild(new Head); $this->addChild(new Body); } 
  public function addHeadChild(DomEl $el) { $this->_children[0]->addChild($el); return $this; }
  public function addBodyChild(DomEl $el) { $this->_children[1]->addChild($el); return $this; }
  public function asHtml() { $this->propagateRootNode(); return parent::asHtml(); }
  
}
