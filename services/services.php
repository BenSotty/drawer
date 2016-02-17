<?php 

include '../model/model.php';
include 'services_m.php';
$request = $_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST['request']) ? $_POST['request'] : null;

switch ($request) {
  case "saveRectangle" :
    echo json_encode(saveRectangle());
    exit();
  case "getSavedRectangles" :
    echo json_encode(getSavedRectangles());
    exit();
  default :
    echo json_encode(array("success" => false, "error" => "not-valid"));
    exit();
}
