<?php

// Functions
function printRefresh() {
	return "<p><a href=\"javascript:location.reload()\" class=\"jump_link\" title=\"Aktualisieren ...\">Aktualisieren</a></p>";
}

function printTable(&$faculties) {
	$table = "<table class=\"data\"><thead><tr><th>Fakultät</th><th>Prozent</th></tr></thead>";
	foreach ($faculties as $faculty => $value) {
		$table .= "<tr><td><span style=\"color: ".$value["color"]."\"><b>".$faculty."</b></span></td><td class=\"value\">".sprintf("%.2f%%", $value["distribution"] * 100)."</td></tr>";
	}
	$table .= "<tr><td colspan=\"2\" class=\"value\">Summe = ".sprintf("%.2f%%", array_sum(array_column($faculties, "distribution")) * 100)."</td></tr></table>";
	return $table;
}

function printChart(&$faculties, &$title) {
	return "<canvas id=\"pie_chart\" aria-label=\"".$title."\" role=\"img\">Diagramme werden von Ihrem Browser nicht unterstützt.</canvas>
		<script>
			Chart.defaults.global.defaultFontFamily = \"'Helvetica Neue', 'Helvetica', 'Arial', 'Lucida Grande', 'Lucida Sans', 'Open Sans', 'sans-serif'\";
			Chart.defaults.global.defaultFontSize = 16;
			var ctx = document.getElementById('pie_chart');
			var chart = new Chart(ctx, {
				type: 'pie',
				data: {
					datasets: [{
						data: [".implode(",", array_map(function($distribution) { return number_format($distribution * 100, 2, ".", ""); }, array_column($faculties, "distribution")))."],
						labels: ['".implode("', '", array_keys($faculties))."'],
						backgroundColor: ['".implode("', '", array_column($faculties, "color"))."'],
						borderColor: '#eeeeee'
					}],
					labels: ['".implode("', '", array_keys($faculties))."']
				},
				options: {
					responsive: true,
					legend: {
						position: 'bottom',
						fullWidth: false
					},
					title: {
						display: true,
						fontSize: 32,
						text: '".$title."'
					},
					tooltips: {
						enabled: true
					},
					animation: {
						animateScale: true,
						animateRotate: true
					}
				}
			});
		</script>";
}

function printData(&$faculties, $title) {
	return "<table><tr><td>".printTable($faculties)."</td><td>".printChart($faculties, $title)."</td></tr></table>";
}

function printBackButton() {
	return "<p><a href=\".\" class=\"jump_link\" title=\"Zurück ...\">Zurück</a></p>";
}

function printDebug(&$data) {
	$debug = "<p><a href=\"javascript:void(0)\" id=\"hide\"
			class=\"jump_link\"
			status=\"hide\"
			showcontent=\"Debug ausblenden\"
			showtitle=\"Debug ausblenden ...\"
			hidecontent=\"Debug einblenden\"
			hidetitle=\"Debug einblenden ...\"></a></p>";
	$debug .= "<div id=\"debug_frame\"><table class=\"data\"><thead><tr><th>Zeitschrift</th><th>Summe aller gesetzten Verteilungen</th></tr></thead>";
	foreach ($data["journals"] as $journal => $distributions) {
		$debug .= "<tr><td>".$journal."</td><td class=\"value\">".sprintf("%.2f%%", array_sum($distributions) * 100)."</td></tr>";
	}
	$debug .= "</table></div>";
	return $debug;
}

?>
