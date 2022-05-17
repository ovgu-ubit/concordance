<?php

// Functions
function harmonize(&$input) {
	/* Rules:
	    - Convert umlauts
	    - Uppercase each first character in a word
	   Caution: only apply on 'subject' key index fields!
	*/
	$search = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "´");
	$replace = array("Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", "");
	return ucwords(str_replace($search, $replace, $input));
}

function normalize(&$input) {
	/* Rules:
	    - Remove any leading, trailing or double spaces
	   Usage: apply on each key index field.
	*/
	return preg_replace("!\s+!", " ", trim(strval($input)));
}

function uploadCSV(&$file, $upload_dir) {
	$filename = $file["name"];
	if (empty($filename)) {
		echo "<b>FEHLER: </b>Es wurde keine Datei angegeben!<br />";
		return null;
	} else {
		$filepath = $upload_dir."/".$filename;
		$target_type1 = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
		if ($target_type1 != "csv") {
			echo "<b>FEHLER: </b>Die hochzuladene Datei <b>".$filename."</b> muss im CSV-Format vorliegen!<br />";
			return null;
		}
		if ((file_exists($filepath)) && (!unlink($filepath))) {
			echo "<b>FEHLER: </b>Die hochzuladene Datei <b>".$filename."</b> konnte nicht geschrieben werden!<br />";
			return null;
		}
		if (!move_uploaded_file($file["tmp_name"], $filepath)) {
			echo "<b>FEHLER: </b>Es gab ein Problem beim Hochladen der Datei <b>".$filename."</b>!<br />";
			return null;
		}
	}
	return $filepath;
}

function removeCSV(&$filepath) {
	if (!unlink($filepath)) {
		echo "<b>FEHLER: </b>Die hochgeladene Datei <b>".$filename."</b> konnte nicht wieder entfernt werden!<br />";
		return null;
	}
	return;
}

function getDataFromCSV(&$filepath, &$values) {
	$journals = null;
	$colors = null;
	if ((isset($filepath)) && (isset($values))) {
		if (($handle = fopen($filepath, "r")) !== false) {
			while (($data = fgetcsv($handle, 0, "\t")) !== false) {
				if ($data != array(null)) {
					$subjects = explode(";", utf8_encode($data[4]));
					$count = floatval(count($subjects));
					if (($count > 0) && (!empty($subjects[0])) && ($subjects[0] != "Fach")) {
						$journal = utf8_encode($data[1]);
						foreach ($values["distributions"] as $faculty => $distribution) {
							$sum = 0.0;
							foreach ($subjects as $subject) {
								$sum += $distribution[harmonize(normalize($subject))];
							}
							$journals[normalize($journal)][normalize($faculty)] = floatval($sum / $count);
						}
					}
				}
			}
			fclose($handle);
		} else {
			echo "<b>FEHLER: </b>Die CSV-Datei konnte nicht geöffnet werden!<br />";
			return null;
		}
		foreach ($values["colors"] as $faculty => $color) {
			$colors[normalize($faculty)] = $color;
		}
	} else {
		echo "<b>FEHLER: </b>Die Datenquellen (CSV-Datei bzw. Datenbank) konnten nicht gefunden werden!<br />";
		return null;
	}
	return array("journals" => $journals, "colors" => $colors);
}

function getFaculties(&$data) {
	$faculties = null;
	if (isset($data)) {
		$journals = $data["journals"];
		$count = floatval(count($journals));
		foreach ($journals as $journal) {
			foreach ($journal as $faculty => $distribution) {
				$faculties[$faculty]["distribution"] += $distribution;
			}
		}
		foreach ($faculties as $faculty => $distribution) {
			$faculties[$faculty]["distribution"] /= $count;
			$faculties[$faculty]["color"] = $data["colors"][$faculty];
		}
	}
	return $faculties;
}

function getValuesFromDB($db_auth) {
	$distributions = null;
	$colors = null;
	$db_auth = parse_ini_file($db_auth);
	$connection_string = "host=".$db_auth["host"]." port=".$db_auth["port"]." dbname=".$db_auth["database"]." user=".$db_auth["username"]." password=".$db_auth["password"];
	if (($connection = pg_connect($connection_string)) === false) {
		echo "<b>FEHLER: </b>Es gab ein Problem beim Verbinden zur Datenbank <i>".$db_auth["database"]."</i>! Bitte überprüfen Sie nochmals Ihre Zugangsdaten und die ausgewählte Datenbank ...<br />";
		return null;
	}
	$query_distributions = "SELECT \"faculties\".\"faculty\" AS \"faculty\", \"subjects\".\"subject\" AS \"subject\", \"l_distributions\".\"distribution\" AS \"distribution\" FROM \"l_distributions\"
			INNER JOIN \"faculties\" ON \"l_distributions\".\"faculty_id\" = \"faculties\".\"id\"
			INNER JOIN \"subjects\" ON \"l_distributions\".\"subject_id\" = \"subjects\".\"id\"
			ORDER BY \"faculties\".\"faculty\", \"subjects\".\"subject\", \"l_distributions\".\"distribution\"";
	if (($result = pg_query($connection, $query_distributions)) !== false) {
		while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			$distributions[normalize($row["faculty"])][harmonize(normalize($row["subject"]))] = floatval($row["distribution"]);
		}
	} else {
		echo "<b>FEHLER: </b>Es gab ein Problem beim Abrufen der Verteilungen aus der Datenbank <i>".$db_auth["database"]."</i>! Bitte überprüfen Sie nochmals die ausgewählte Datenbank ...<br />";
		pg_close($connection);
		return null;
	}
	$query_colors = "SELECT \"faculties\".\"faculty\" AS \"faculty\", \"faculties\".\"color\" AS \"color\" FROM \"faculties\" ORDER BY \"faculties\".\"id\"";
	if (($result = pg_query($connection, $query_colors)) !== false) {
		while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			$colors[normalize($row["faculty"])] = strval($row["color"]);
		}
	} else {
		echo "<b>FEHLER: </b>Es gab ein Problem beim Abrufen der Farben aus der Datenbank <i>".$db_auth["database"]."</i>! Bitte überprüfen Sie nochmals die ausgewählte Datenbank ...<br />";
		pg_close($connection);
		return null;
	}
	pg_close($connection);
	return array("distributions" => $distributions, "colors" => $colors);
}

?>
