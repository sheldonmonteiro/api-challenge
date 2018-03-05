<?php
include_once('dbutil.php');
include_once('show.php');

$conn = connectDB();
$show = new Show($conn);

$input = json_decode(file_get_contents("php://input"));

if(!is_null($input)) {
	$showsPerPage = $input->showsPerPage;
	$pageNumber = $input->pageNumber;
	$sortBy = $input->sortBy;
	$sortDirection = strtoupper($input->sortDirection);
	$show->listShows($showsPerPage, $pageNumber, $sortBy, $sortDirection);
}

$conn = null;
?>