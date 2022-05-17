<?php

// Imports
require_once("functions.php");
require_once("printer.php");

// Constants
define("DB_AUTH", ".dbaccess");
define("UPLOAD_DIR", "uploads");

// Main
if (isset($_POST["run"])) {
	echo "<h1>Konkordanz</h1>";
	// Determine data:
	$filepath = uploadCSV($_FILES["csv_file"], UPLOAD_DIR);
	$data = getDataFromCSV($filepath, getValuesFromDB(DB_AUTH));
	$faculties = getFaculties($data);
	if (isset($faculties)) {
		// Print refresh link:
		echo "<hr />".printRefresh();
		// Print faculty table and chart:
		echo printData($faculties, "Konkordanz");
		// Print back button:
		echo printBackButton();
		// Print debug faculty sums per journal:
		echo "<hr />".printDebug($data);
	} else {
		// Print back button:
		echo printBackButton();
	}
	// Clean up uploaded files:
	removeCSV($filepath);
} else {
	require_once("form.html");
}

?>
