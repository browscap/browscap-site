google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(function () {
	var data = google.visualization.arrayToDataTable(chartData); 
	
	var options = {
        'width': 960,
        'height': 300,
        'legend': { 'position': 'bottom' },
        'backgroundColor': '#f8f8ff',
        'colors': ['#3b5998']
	};
	
	var chart = new google.visualization.LineChart(document.getElementById('theChart'));
	chart.draw(data, options);
});
