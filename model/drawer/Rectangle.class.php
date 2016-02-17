<?php 

class Rectangle {
  
  private $topA;
  private $leftA;
  private $topB;
  private $leftB;
  
  public function __construct($topA, $leftA, $topB, $leftB) {
    $this->topA = $topA;
    $this->leftA = $leftA;
    $this->topB = $topB;
    $this->leftB = $leftB;
  }
  
  /**
   * Save the rectangle into the database
   */
  
  public function save () {
    return $this->isValid() && DbHelper::insert(
      array (
        self::DB_COL_TOP_A => (int) $this->topA,
        self::DB_COL_LEFT_A => (int) $this->leftA,
        self::DB_COL_TOP_B => (int) $this->topB,
        self::DB_COL_LEFT_B => (int) $this->leftB
      ),
      self::DB_TABLE
    );
  }
  
  /**
   * A valid rectangle is a rectangle with 2 different corners
   * @return boolean
   */
  
  public function isValid () {
    return $this->topA !== $this->topB && $this->leftA !== $this->leftB;
  }
  
  const DB_TABLE = "rectangles";
  const DB_COL_ID = "rectid";
  const DB_COL_TOP_A = "recttopa";
  const DB_COL_LEFT_A = "rectlefta";
  const DB_COL_TOP_B = "recttopb";
  const DB_COL_LEFT_B = "rectleftb";
  
}