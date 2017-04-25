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
function drawChart(labelsX, donnees)
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


var element = document.getElementById('clickme');

element.addEventListener('click', function() {
	getDonnees(); // fonction php... TODO
	alert("click");
});



/**
	sera lancée au chargement de la page
	plage de dates par défaut à déterminer
*/
/*function init_graph()
{
	<?php
		include graphiques.php;
	?>

	getDonnees();
	var labels = new Array("1","2","3","4","5","6","7");
	var donnees0 = new Array(65, 59, 80, 81, 56, 55, 40);
	var donnees1 = new Array(2, 29, 5, 5, 2, 3, 10);
	var donnees = new Array(donnees0, donnees1);
	drawChart(labels, donnees);
}*/