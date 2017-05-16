/**
	dessiner les graphiques dans la div myChart (dans le fichier graphiques.php)
	divID = id de la balise où sera placé le graphique, dans le fichier graphe.php
	labelsX = graduation abscisse
	donnees = array de 2 arrays (un par courbe)
	
	normalement on a :
	- en abscisse = dates
	- courbes =
		- abeilles entrantes
		- abeilles sortantes
		- différence entrées/sorties
		(on peut ajouter : pollen, faux bourdon -> même principe que pour toutes les séries, penser à rajouter les données dans le tableau en paramètres, dans le fichier graphiques.php)
*/
function drawChart(divID, labelsX, donnees) {
	var theChart = Highcharts.chart(divID, {
		chart: {
			zoomType: 'x' // zoom seulement sur l'axe x
		},
		title: {
			text: 'Entrées-sorties des abeilles'
		},
		legend: {
			// noms des courbes à droite
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
		xAxis: {
			categories: labelsX // tableau des dates sur la graduation de x
		},
		yAxis: {
			title: {
				text: 'Nombre d\'abeilles' // légende en y
			}
		},
		/* suivre ces exemples pour rajouter des nouvelles courbes (pollen, etc.) */
		series: [{
			type: 'spline', // pour des lignes courbées
			name: 'Abeilles entrantes',
			data: donnees[0], // tableau récupéré dans graphiques.php
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
			fillColor: 'rgba(74, 197, 235, .2)' // remplir l'intérieur de la courbe, pour bien voir si on est en négatif ou positif
		}]
	});
}
