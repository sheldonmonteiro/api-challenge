<?php
include_once('dbutil.php');
include_once('show.php');

$conn = connectDB();
$show = new Show($conn);

$input = json_decode(file_get_contents("php://input"));

if(!is_null($input)) {
	$show->title = $input->title;
	$show->description = $input->description;
	$show->duration = $input->duration;
	$show->originalAirDate = $input->originalAirDate;
	$show->rating = $input->rating;
	$show->keywords = $input->keywords;
	$show->createShow();
}

$conn = null;
?>