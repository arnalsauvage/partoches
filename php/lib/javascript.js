function confirmeSuppr(nouvelleAdresse,message)
{
if (confirm(message))
	window.location.href = nouvelleAdresse;
}

function changeImage(nouvelleImage)
{
document.getElementById("imageChoisie").src = nouvelleImage;
/*document.getElementById("image").width = "240";*/
}

var requete = null;

function creerRequete()
{
    requete = null;
    try
    	{
    	requete = new XMLHttpRequest();
    	}
    catch (essaiMicrosoft)
    	{
        	try 
    			{
        			requete = new ActiveXObject("Msxml2.XMLHTTP");
        		}
        	catch (autremicrosoft)
    			{
        			try
    					{
    	    			requete = newActiveXObject("Microsoft.XMLHTTP");
        				}
        			catch (echec)
    					{
    	    			requete = null;
    	    			}
        		}
       	}
    if (requete==null)
    	alert("Impossible de créer l'objet requête!");		
}

function actualisePage()
{
	/*alert("requete.readyState : "+requete.readyState);    */
		if (requete.readyState == 4)
		{
			/*alert("requete.responseText = "+requete.responseText);*/
			/* alert("Ready state = 4 !"); */
			document.getElementById("v-"+requete.responseText).src = "vignettes/"+requete.responseText;
			/*alert("requete.responseText = "+requete.responseText);*/
			/*alert("Ready state = 4 !");*/
		}
}

function demandeVignette(nom)
{
	creerRequete();
	var url = "http://medina.arnaud.free.fr/pages/include/mavignette.php";
	requete.open("POST", url, true);
	requete.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	var data = "image="+nom+"&largeur_max=240&hauteur_max=200&source=../../images/&destination=../../vignettes/";
	requete.onreadystatechange = actualisePage;
	requete.send(data);
/*	alert ("Requete créée!");*/
}

function recalculeVignette(nom)
{
	creerRequete();
	var url = "http://medina.arnaud.free.fr/pages/include/mavignette.php";
	requete.open("POST", url, true);
	requete.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	var data = "image="+nom+"&largeur_max=240&hauteur_max=200&source=../../images/&destination=../../vignettes/";
	data = data +  "&force=oui";
  requete.onreadystatechange = actualisePage;
	requete.send(data);
/*	alert ("Requete créée!");*/
}

function changeListeImage(formulaire)
{
  var l1    = formulaire.elements["listeImage"];
/*  var index = l1.selectedIndex;
  l1.options[index].value */
	var image = l1.options[l1.selectedIndex].value;
	document.getElementById("vignette").src = "../vignettes/"+image;	
}

/* fonction appelée par le composant formulaire image de formulaire.php */

var xhr_object = null; 
function miseAjourListeImages ()
{
   if(window.XMLHttpRequest) // Firefox 
      xhr_object = new XMLHttpRequest(); 
   else if(window.ActiveXObject) // Internet Explorer 
      xhr_object = new ActiveXObject("Microsoft.XMLHTTP"); 
   else { // XMLHttpRequest non supporté par le navigateur 
      alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
      return; 
      }
/*  if (xhr_object!=null)
    alert ("Objet XHr créé .");*/
  xhr_object.open("GET", "http://medina.arnaud.free.fr/pages/include/majListeImages.php", true); 
  xhr_object.onreadystatechange = function()  {
/*    alert("XHR.readyState = " + xhr_object.readyState); */ 
    if (xhr_object.readyState == 4)
    {
       alert("Nouvelle liste reçue du serveur.") 
      eval(xhr_object.responseText);
    } 
  }
  xhr_object.send(null);    
/*  alert("Sortie fonction MiseAjourListeImage"); */     
} 