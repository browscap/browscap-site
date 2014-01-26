google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(function () {
	
	// Downloads per day
	var downloadsPerDayData = google.visualization.arrayToDataTable(downloadsPerDay); 
	var options = {
        'width': 960,
        'height': 300,
        'legend': { 'position': 'bottom' },
        'backgroundColor': '#f8f8ff',
        'colors': ['#3b5998']
	};
	var downloadsPerDayChart = new google.visualization.LineChart(document.getElementById('downloadsPerDay'));
	downloadsPerDayChart.draw(downloadsPerDayData, options);
	
	// Downloads per month
	var downloadsPerMonthData = google.visualization.arrayToDataTable(downloadsPerMonth); 
	var options = {
        'width': 960,
        'height': 300,
        'legend': { 'position': 'bottom' },
        'backgroundColor': '#f8f8ff',
        'colors': ['#3b5998'],
        'orientation': 'horizontal'
	};
	var downloadsPerMonthChart = new google.visualization.BarChart(document.getElementById('downloadsPerMonth'));
	downloadsPerMonthChart.draw(downloadsPerMonthData, options);
});
