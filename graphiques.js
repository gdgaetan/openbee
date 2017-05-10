/**
	dessiner les graphiques dans le canvas myChart
	labelsX = graduation abscisse
	donnees = array de 2 arrays (un par courbe)
	
	normalement on a :
	- en abscisse = dates
	- courbes =
		- abeilles entrantes
		- abeilles sortantes
		- différence entrées/sorties
		(on peut ajouter : pollen, faux bourdon -> décommenter les lignes correspondantes, adapter les couleurs, )
*/
function drawChartOld(labelsX, donnees)
{
	var ctx = document.getElementById("myChart").getContext("2d");
	
	var chartAbeille = new Chart(ctx, {
		type: 'line',
		
		data: {
			labels: labelsX,
			datasets: [{
				label: 'Abeilles entrantes',
				data: donnees[0],
				fill: false,
				backgroundColor: "rgba(0,255,0,0.1)",
				borderColor: "rgba(0,255,0,1)",
				borderWidth: 2,
				tension: 0
			},{
				label: 'Abeilles sortantes',
				data: donnees[1],
				fill: false,
				backgroundColor: "rgba(255,0,0,0.1)",
				borderColor: "rgba(255,0,0,1)",
				borderWidth: 2,
				tension: 0
			},{/*
				label: 'Abeilles "Pollen" entrantes',
				data: donnees[2],
				fill: false,
				backgroundColor: "rgba(255,0,0,1)",
				borderColor: "rgba(255,0,0,1)",
				borderWidth: 2,
				tension: 0
			},{
				label: 'Abeilles "Pollen" sortantes',
				data: donnees[3],
				fill: false,
				backgroundColor: "rgba(255,0,0,1)",
				borderColor: "rgba(255,0,0,1)",
				borderWidth: 2,
				tension: 0
			},{
				label: 'Abeilles "Faux bourdon" entrantes',
				data: donnees[4],
				fill: false,
				backgroundColor: "rgba(255,0,0,1)",
				borderColor: "rgba(255,0,0,1)",
				borderWidth: 2,
				tension: 0
			},{
				label: 'Abeilles "Faux bourdon" sortantes',
				data: donnees[5],
				fill: false,
				backgroundColor: "rgba(255,0,0,1)",
				borderColor: "rgba(255,0,0,1)",
				borderWidth: 2,
				tension: 0
			},{*/
				label: 'Différence entrées/sorties',
				data: donnees[2], // modifier le '2' avec l'ajout de nouvelles séries de données
				fill: true,
				backgroundColor: "rgba(0,0,255,0.1)",
				borderColor: "rgba(0,0,255,1)",
				borderWidth: 2,
				tension: 0
			}]
		},
		
		options: {
			responsive: true,
			maintainAspectRatio: false,
			/*scales: {
				yAxes: [{
					ticks: {
						beginAtZero:true,
						//min: 0,
						//max: 100
					}
				}]
			}*/
		}
	});
	
}

// export -> http://a----.github.io/highcharts-export-clientside/#installation
// https://www.highcharts.com/plugin-registry/single/37/highcharts-export-clientside
// https://www.highcharts.com/docs/export-module/client-side-export
function drawChart(labelsX, donnees) {
alert("drawChart");
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
			name: 'Abeilles entrantes',
			data: donnees[0],
			color: '#86FE3A'
		}, {
			name: 'Abeilles sortantes',
			data: donnees[1],
			color: '#F1261A'
		}, {
			name: 'Différence entrées-sorties',
			data: donnees[2],
			color: '#394E71'
		}]
	});
}
