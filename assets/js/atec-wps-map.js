function atec_wps_map(countries)
{
	var arr = eval("["+countries+"]");
	google.charts.load('current', {'packages':['geochart'],'mapsApiKey': 'AIzaSyC0jiKuLRcT7zxXkQbSWSvGWMQZurZ4NdA'});
	google.charts.setOnLoadCallback(drawRegionsMap);
	function drawRegionsMap() {
		var gData = new google.visualization.DataTable();
		gData.addColumn('string', 'Country');
		gData.addColumn('number', 'Visitors');
		arr.forEach((a) => { gData.addRows([[a[0], a[1]]]); });
		const chart = new google.visualization.GeoChart(document.getElementById('regions_div'));
		chart.draw(gData, {'region':'world'});
	}
}