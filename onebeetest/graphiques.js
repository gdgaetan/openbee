/**
	dessiner les graphiques dans le la div myChart
	labelsX = graduation abscisse
	donnees = array de 2 arrays (un par courbe)
	
	normalement on a :
	- en abscisse = dates
	- courbes =
		- abeilles entrantes
		- abeilles sortantes
		- différence entrées/sorties
		(on peut ajouter : pollen, faux bourdon -> même principe que pour toutes les séries, penser à rajouter les données dans le tableau en paramètres )
*/
function drawChart(labelsX, donnees) {
	var theChart = Highcharts.chart('myChart', {
		chart: {
			zoomType: 'x'
		},
		title: {
			text: 'Entrées-sorties des abeilles'
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
		xAxis: {
			categories: labelsX
		},
		yAxis: {
			title: {
				text: 'Nombre d\'abeilles'
			}
		},
		series: [{
			type: 'spline',
			name: 'Abeilles entrantes',
			data: donnees[0],
			color: '#B7EB34'
		}, {
			type: 'spline',
			name: 'Abeilles sortantes',
			data: donnees[1],
			color: '#EC2655'
		}, {
			type: 'areaspline',
			name: 'Différence entrées-sorties',
			data: donnees[2],
			color: '#4AC5EB',
			fillColor: 'rgba(74, 197, 235, .2)'
		}]
	});
}
