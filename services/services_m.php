<?php 

function saveRectangle () {
  if (!isset($_POST['topA']) || !isset($_POST['topB']) || !isset($_POST['leftA']) || !isset($_POST['leftB'])) {
    return array ("success" => false, "error" => "Wrong formatted request");
  }
  $rectangle = new Rectangle (
    (int) $_POST['topA'],
    (int) $_POST['leftA'],
    (int) $_POST['topB'],
    (int) $_POST['leftB']
  );
  if ($rectangle->save()) {
    return array ("success" => true);
  }
  return array ("success" => false, "error" => $rectangle->isValid() ? "Oops, something went wrong when saving the rectangle" : "Rectangle with no width and height cannot be registered.");
}

function getSavedRectangles () {
  return DbHelper::select(Rectangle::DB_TABLE, array (
    Rectangle::DB_COL_TOP_A,
    Rectangle::DB_COL_LEFT_A,
    Rectangle::DB_COL_TOP_B,
    Rectangle::DB_COL_LEFT_B
  ));
}