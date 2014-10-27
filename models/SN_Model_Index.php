<?php

/**
 * SN_Model_Index
 *
 * @property integer $ID
 * @property string $Number
 * @property string $Ebene
 * @property string $Name
 * @property string $Ort
 * @property string $PLZ
 * @property string $url
 * @property integer $latitude
 * @property integer $longitude
 * @property integer $parent_id
 */
class SN_Model_Index extends ArrayObject {
	private $children = array();

    function __construct($array) {
        parent::__construct($array);
    }

    public function __get($name) {
        return $this[$name];
    }

    public function getChildren() {
	    return $this->children;
    }

    public function addChild(&$child) {
	    $this->children[] = $child;
    }

}
