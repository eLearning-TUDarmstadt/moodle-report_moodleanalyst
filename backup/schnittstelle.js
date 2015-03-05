/**
 * Interaktive Anzeige ÃƒÂ¼ber Anteil der Schnittstellenkurse
 */

function drawTable(divIDlinks, divIDrechts, result) {
	var data = new google.visualization.DataTable();
	data.addColumn('number', 'ID');
	data.addColumn('string', 'Kategorie');
	data.addColumn('number', 'SUMME');
	data.addColumn('number', 'Manuell');
	data.addColumn('number', 'Schnittstelle');
	var array = new Array();
	$.each(result, function(id, felder) {
		// console.log(felder);

		var subarray = new Array();
		subarray.push(parseInt(felder.id));
		subarray.push(felder.name);
		subarray.push(parseInt(felder.gesamt));
		subarray.push(parseInt(felder.manuell));
		subarray.push(parseInt(felder.schnittstelle));
		array.push(subarray);

	});
	data.addRows(array);

	var table = new google.visualization.Table(document
			.getElementById(divIDlinks));
	table.draw(data, {
		showRowNumber : false
	});

	// Setup listener
	google.visualization.events.addListener(table, 'select', selectHandler);

	// Select Handler. Call the table's getSelection() method
	function selectHandler() {
		var selection = table.getSelection();
		var category = data.getValue(selection[0].row, 0);
		DrawGUI(divIDlinks, divIDrechts, category)
	}
}

function drawChart(divIDlinks, divIDrechts, result) {
	var tucanGruen = "#b1bd00";
	var moodleGelb = "#F5A300";
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Kategorie');
	// data.addColumn('number', 'SUMME');
	data.addColumn('number', 'Manuell');
	data.addColumn('number', 'Schnittstelle');
	var array = new Array();
	$.each(result, function(id, felder) {
		// console.log(felder);

		var subarray = new Array();
		subarray.push(felder.name);
		// subarray.push(parseInt(felder.gesamt));
		subarray.push(parseInt(felder.manuell));
		subarray.push(parseInt(felder.schnittstelle));
		array.push(subarray);

	});
	data.addRows(array);
	var options = {
		// title: '',
		colors : [ moodleGelb, tucanGruen ],
		height : 450,
		isStacked : true,
	// hAxis: {title: 'Year', titleTextStyle: {color: 'red'}}
	};
	var chart = new google.visualization.ColumnChart(document
			.getElementById(divIDrechts));

	chart.draw(data, options);

}

function drawPieCharts(divIDUnten, result) {
	var PiesProZeile = 4;
	var Anteil = 12 / PiesProZeile;
	var panelTemplate = '<div class="col-sm-' + Anteil + '">'
			+ '<div class="panel panel-default">'
			+ '<div class="panel-heading">'
			+ '<h3 class="panel-title" id="CATEGORY">NAME</h3>' + '</div>'
			+ '<div class="panel-body">CONTENT' + '</div>' + '</div>'
			+ '</div>' + '</div>';
	var htmlCode = '';
	var counter = 0;

	$.each(result, function(id, felder) {

		if (counter == 0) {
			htmlCode = htmlCode + '<div class=row>';
		}
		//console.log(felder);

		mitCategory = panelTemplate.replace(/CATEGORY/g, felder.id);
		mitName = mitCategory.replace(/NAME/g, felder.name);
		mitContent = mitName.replace(/CONTENT/g, "<div id='content" + felder.id
				+ "'></div>");

		htmlCode = htmlCode + mitContent;
		counter = counter + 1;
		if (counter > PiesProZeile - 1) {
			htmlCode = htmlCode + '</div>';
			counter = 0;
		}

	});
	$('#' + divIDUnten).html(htmlCode);

	$.each(result, function(id, felder) {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Art');
		data.addColumn('number', 'Anzahl');
		var array = new Array();
		var subarray = new Array();
		subarray.push(felder.manuell.toString());
		subarray.push(felder.manuell);
		array.push(subarray);
		var subarray = new Array();
		subarray.push(felder.schnittstelle.toString());
		subarray.push(felder.schnittstelle);
		array.push(subarray);
		data.addRows(array);
		var tucanGruen = "#b1bd00";
		var moodleGelb = "#F5A300";
		var options = {
			title: 'Kurse: ' + felder.gesamt.toString(),
			pieHole : 0.4,
			slices: {
	            0: { color: moodleGelb},
	            1: { color: tucanGruen }
	          }
		};
		var container = 'content' + felder.id;
		var chart = new google.visualization.PieChart(document
				.getElementById(container));
		chart.draw(data, options);
	});
}

function DrawGUI(divIDlinks, divIDrechts, parentCategory) {
	$(".loader").html(loader);
	$("#linksOben").html(
			'<div>-> Zurück</div>');
	$.ajax(
			{
				url : "/report/moodleanalyst/rest/router.php/Schnittstelle/"
						+ parentCategory,
				dataType : 'json',
			}).done(function(result) {
		drawTable(divIDlinks, divIDrechts, result);
		drawChart(divIDlinks, divIDrechts, result);
		drawPieCharts('unten', result);
	});
	$.ajax({
		url : "/report/moodleanalyst/rest/router.php/Kategorie/" + parentCategory,
		dataType : 'json',
	}).done(
			function(res) {
				// console.log(res);
				$("#linksOben").html(
						'<div onclick=\'DrawGUI(\"'
								+ divIDlinks + '\", \"' + divIDrechts + '\", '
								+ res.parentID + ');\'>-> Zurück</div>');
			});
}