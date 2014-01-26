google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(function () {
	var data = google.visualization.arrayToDataTable(chartData); 
	
	var options = {
        'width': 960,
        'height': 300
	};
	
	var chart = new google.visualization.LineChart(document.getElementById('theChart'));
	chart.draw(data, options);
});
