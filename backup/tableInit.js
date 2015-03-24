			$(document).ready(function () {
    			
		    	// Tab Inhalte als eigene html in die Tabelle laden:
		    	
		    	//
		    	// Tab: Kurse
		    	//
    			$.get("../../report/moodleanalyst/html/tabs/kurse.html", function( inhalt ) {
    				$( "#kurse" ).html(inhalt);
				});
		    
		    
    			//
		    	// Tab: Nutzer
		    	//
    			$.get("../../report/moodleanalyst/html/tabs/nutzer.html", function( inhalt ) {
    				$( "#nutzer" ).html(inhalt);
				});
    		
		    
    			//
    			// Tab: Dateien
    			//
    			$.get("../../report/moodleanalyst/html/tabs/dateien.html", function ( inhalt ) {
    				$( "#dateien" ).html(inhalt);
    			});
    			
	    	    
        		//
    			// Tab: Kommunikation
    			//
	    		$.get("../../report/moodleanalyst/html/tabs/kommunikation.html", function ( inhalt ) {
	    			$( "#kommunikation" ).html(inhalt);
	    		});
    		
        		
    			//
				// Tab: Tests
				//
				$.get("../../report/moodleanalyst/html/tabs/tests.html", function ( inhalt ) {
					$( "#tests" ).html(inhalt);
				});
    			
    			
    			//
				// Tab: Kooperation
				//
				$.get("../../report/moodleanalyst/html/tabs/kooperation.html", function ( inhalt ) {
					$( "#koop" ).html(inhalt);
				});
    			
    			
    			//
				// Tab: Rückmeldungen
				//
				$.get("../../report/moodleanalyst/html/tabs/rueckmeldungen.html", function ( inhalt ) {
					$( "#rueckmeldungen" ).html(inhalt);
				});
    			
    			
    			//
				// Tab: Lehrorganisation
				//
				$.get("../../report/moodleanalyst/html/tabs/lehrorganisation.html", function ( inhalt ) {
					$( "#lehrorganisation" ).html(inhalt);
				});
		    	
    			
		    	//
		    	// Bei Klick auf eine Zeile öffnet sich automatisch der erste Tab ("Kurse"), außer, wenn im Tab "Nutzer/innen" auf eine Zeile geklickt wird.
		    	// In dem Fall bleibt das Tab aktiv.
		    	// Die Tab ID des Nutzer Tabs muss angepasst werden, falls weitere Tabs dazu kommen (Momentan ist die Tab ID: 1)
		    	//
        		jQuery(document.body).on('click', '.jtable-data-row', function() {
        			var curTab = $(".ui-tabs-active");
        			var curTabIndex = curTab.index();
        			//alert(curTabIndex);
        			if (curTabIndex == 1) {
        				$("#tabs").tabs({active: 1});
        			}
        			else {
        				$("#tabs").tabs({active: 0});
        			}
    			});
			});