//------------------------------------------------------------------//
// Load the Visualization API and the controls package.
//------------------------------------------------------------------//
google.load('visualization', '1.1', {'packages': ['controls']});

//------------------------------------------------------------------//
// Set a callback to run when the Google Visualization API is loaded.
//------------------------------------------------------------------//
//google.setOnLoadCallback('dateien', fields, '/Dateien');


function tabelle(divID, fields, url) {
    //console.log('/report/moodleanalyst/rest/router.php' + url);

    //------------------------------------------------------------------//
    // Loading image
    //------------------------------------------------------------------//
    //var loader = "<img src='/report/moodleanalyst/pix/loader.gif' style='display: block; margin-left: auto; margin-right: auto; margin-top: 50px;'></src>";
    $( "#table_div_" + divID ).html(loader);
    
    //------------------------------------------------------------------//
    // ajax call
    //------------------------------------------------------------------//
    $.ajax({
        fields : fields,
        url: '/report/moodleanalyst/rest/router.php' + url,
        //type: 'POST',
        dataType: 'json',
        //data: postData,
    }).done( function (result) {
        var data = new google.visualization.DataTable();
        
        //------------------------------------------------------------------//
        //Spalten, die auf jeden Fall vorkommen sollen
        //------------------------------------------------------------------//
        data.addColumn('number', 'ID');
        data.addColumn('string', 'Semester');
        data.addColumn('string', 'Fachbereich');
        data.addColumn('string', 'Name');
        data.addColumn('number', 'Teilnehmer');
        $.each(fields, function(spalte, eigenschaften) {
            data.addColumn(eigenschaften.typ, eigenschaften.bezeichnung);
        });
        data.addColumn('datetime', 'Erstellt');
        data.addColumn('datetime', 'Geändert');
        
        result = result.Records;
        var array = new Array();
        
        $.each(result, function(id, felder) {
            //console.log(felder);
            var subarray = new Array();
            subarray.push(parseInt(felder.course));
            subarray.push(felder.semester);
            subarray.push(felder.fb);
            subarray.push(felder.fullname);
            subarray.push(parseInt(felder.participants));
            $.each(fields, function(spalte, eigenschaften) {
                if(eigenschaften.typ == "number") {
        	    subarray.push(parseInt(felder[spalte]));
        	}
        	else {
        	    subarray.push(felder[spalte]);
        	}
            //array.push(felder[spalte]);
            //data.addColumn('boolean', eigenschaften.bezeichnung);
            });
            subarray.push(new Date(felder.timecreated*1000));
            subarray.push(new Date(felder.timemodified*1000));
            
            array.push(subarray);
            //data.addRow(array);
            //data.addRow('boolean', eigenschaften.bezeichnung);
        });
        
        data.addRows(array);
                            
        //------------------------------------------------------------------//
        // Create a dashboard.
        //------------------------------------------------------------------//
        var dashboard = new google.visualization.Dashboard(document.getElementById(divID));
        
        //------------------------------------------------------------------//
        // Create a category picker to filter by Semester.
        //------------------------------------------------------------------//
        var categoryPickerSemester = new google.visualization.ControlWrapper({
            'controlType': 'CategoryFilter',
            'containerId': 'semester_filter_div_' + divID,
            'options': {
                filterColumnLabel: 'Semester',
                ui: {
                    caption: 'Nach Semester filtern',
                    label: '',
                    allowTyping: false
                }
            }
        });
                 
        //------------------------------------------------------------------//
        // Create a category picker to filter by Fachbereich.
        //------------------------------------------------------------------//
        var categoryPickerFB = new google.visualization.ControlWrapper({
            'controlType': 'CategoryFilter',
            'containerId': 'fb_filter_div_' + divID,
            'options': {
                filterColumnLabel: 'Fachbereich',
                ui: {
                    caption: 'Nach Fachbereich filtern',
                    label: '',
                    allowTyping: false
                }
            }
        });
        
        //------------------------------------------------------------------//
        // Create a table to display.
        //------------------------------------------------------------------//
        //var temp = document.getElementById(divID);
        var table = new google.visualization.ChartWrapper({
            chartType: 'Table',
            containerId: 'table_div_' + divID,
            options: {
                showRowNumber: false,
                page: 'enable',
                pageSize: 25,
                allowHtml: true,
                sortColumn: 2,
                sortAscending: true
            }
        });
                            
        //------------------------------------------------------------------//
        // Establish dependencies.
        //------------------------------------------------------------------//
        dashboard.bind([categoryPickerSemester, categoryPickerFB], [table]);

        //------------------------------------------------------------------//
        // Draw the dashboard.
        //------------------------------------------------------------------//
        dashboard.draw(data);
	
        //------------------------------------------------------------------//
	// Select Handler. Call the table's getSelection() method
        //------------------------------------------------------------------//
	function selectHandler() {
            var selection = table.getChart().getSelection();
            for (var i = 0; i < selection.length; i++) {
                $.get("/report/moodleanalyst/html/detailed_course_view.html", function (inhalt) {
                    //$("#getCourse").html(loader);
                    var selectedCourseId = table.getDataTable().getFormattedValue(selection[0].row, 0);
                    //console.log(selectedCourseId);

                    inhalt = inhalt.replace(/#ID#/g, selectedCourseId);
                    //console.log(inhalt);
                    $("#getCourse").html(inhalt);
                    $("#tabs").tabs({active: 0});
                });
            }
	};
        
        //------------------------------------------------------------------//
        // Setup listener for the selectHandler (when a table row is selected)
        //------------------------------------------------------------------//
	google.visualization.events.addListener(table, 'select', selectHandler);
    });      			
};