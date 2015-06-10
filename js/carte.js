/* carte */

carte = {
	//objet de gestion de la carte
	laCarte:{},
	affiche:0,
	toggle:function() {
		if (this.affiche == 0) {
			this.afficheCarte() ;
		}
		else {
			this.effaceCarte() ;
		}
		return this.affiche ;
	},
	
	afficheCarte : function() {
		this.affiche = 1 ;
		$('carteToggle').innerHTML = "Cacher la carte" ;
		var marqueurs = [] ;
		var minlat = 0 ;
		var minlon = 0 ;
		var maxlat = 0 ;
		var maxlon = 0 ;
		var bounds = new google.maps.LatLngBounds();
		var chaine = ""
		for (var i = 0 ; i < lesEvenements.donnees.length ; i++) {
			var e = lesEvenements.donnees[i] ;
			if (e.latitude != 0 && e.longitude != 0) {
				if (minlat == 0) minlat = e.latitude ;
				if (maxlat == 0) maxlat = e.latitude ;
				if (minlon == 0) minlon = e.longitude ;
				if (maxlon == 0) maxlon = e.longitude ;				
				var p = new google.maps.LatLng(e.latitude, e.longitude) ;
				chaine = "" ;
				if (e.anfin != 0 && e.anfin != e.andebut) {
					chaine = e.andebut + "-" + e.anfin ;
				}
				else {
					chaine = e.andebut ;
				}
				chaine += " : " + e.description ;
				marqueurs.push([p, chaine]) ;
				if (e.latitude < minlat) minlat = e.latitude ;
				if (e.latitude > maxlat) maxlat = e.latitude ;
				if (e.longitude < minlon) minlon = e.longitude ;
				if (e.longitude > minlon) maxlon = e.longitude ;
				bounds.extend(p) ;
			}
		}
		minlat = Math.ceil(minlat) ;
		minlon = Math.ceil(minlon) ;
		maxlat = Math.floor(maxlat) ;
		maxlon = Math.floor(maxlon) ;
		var center = new google.maps.LatLng((maxlat + minlat)/2, (minlon + maxlon)/2) ;
		if (marqueurs.length > 0) {
			$('carte').style.display = "block" ;
			var mapOptions = {
				center: center,
				zoom:6,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			} ;	
			carte.laCarte = new google.maps.Map(document.getElementById('carte'), mapOptions) ;
			var n = 0 ;
			var markers = [] ;
			var couleursmarqueurs = ["FE7569", "677E52", "A7A37E", "046380", "B9121B", "BD8D46", "B09F91", "C44C51", "A2B5BF", "B1221C"] ;
			var ncm = 0 ;
			for (var i = 0 ; i < marqueurs.length ; i++) {
				var m = marqueurs[i] ;
				var icong = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + couleursmarqueurs[ncm], new google.maps.Size(21, 34), new google.maps.Point(0,0), new google.maps.Point(10, 34));
				/*markers[n] = new google.maps.Marker({
     				position: m[0],
				    map: carte.laCarte,
					icon:icong,
     				title:m[1]});*/
				var icong = new google.maps.InfoWindow(
					{'content':m[1], 
					"position":m[0]}) ;
				icong.open(carte.laCarte);
     			n++ ;
				ncm++ ;
				if (ncm >= couleursmarqueurs.length) ncm = 0 ;
     		}
     		if (marqueurs.length > 0) carte.laCarte.fitBounds(bounds);
		}
	},
	effaceCarte : function() {
		this.affiche = 0 ;
		$('carteToggle').innerHTML = "Montrer la carte" ;
		this.laCarte = {} ;
		$('carte').style.display = "" ;
	}
} ;

