

<script type="text/javascript" src="http://maps.google.com/maps/api/js?libraries=geometry&amp;sensor=false"></script>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
        
        
<script type="text/javascript">
//<![CDATA[
//******************************************************************************************************
//*****  Written by Paul Wroe
//*****  pwroe@atterbury.com
//******************************************************************************************************

//////////////////// variable initializations - global //////////////////////////////
var point_place_object = new Object();			//not used
point_place_object.points_array = new Array();	//not used

var my_geocoder;
var initial_center_of_map = new google.maps.LatLng(45.49168276048248, -122.80861973762512);
var initial_zoom_level = 14;
var points_array = new Array();
var marker_array = new Array();
var click_polygon = new google.maps.Polygon;
var click_line = new google.maps.Polyline;
var new_marker = new google.maps.Marker;
var calculated_acres;
var calculated_length;
var my_map;

var map_options = {								//map options

	scaleControl: true,
	zoom: initial_zoom_level,						// higher number is closer to the ground
	center: initial_center_of_map,
	mapTypeId: google.maps.MapTypeId.ROADMAP
	//style: google.maps.NavigationControlStyle.ZOOM_PAN
};
var autocomplete;
function initialize(){
	
	autocomplete = new google.maps.places.Autocomplete(
                  /** @type {HTMLInputElement} */(document.getElementById('autocomplete')),
                  { types: ['geocode'] });
              google.maps.event.addListener(autocomplete, 'place_changed', function() {
              });
			  
	my_geocoder = new google.maps.Geocoder();
	my_map = new google.maps.Map(document.getElementById("map_canvas"),map_options);//this needs to be global
	google.maps.event.addListener(my_map, 'click', function(mouse_was_clicked) {on_click_function(mouse_was_clicked.latLng);} );//listener does not fire on polygons
	google.maps.event.addListener(my_map, 'mousemove', function(mouse_location) {mouse_was_moved(mouse_location.latLng);} );//listener does not fire on polygons
	load_points();// load points if comming back from upload
	
	
}// end initialize

function on_click_function(location_passed){
	click_polygon.setMap(null);								//	clears click_polygon from map
	click_line.setMap(null);								//	clears click_line from map
	points_array.push(location_passed);						//	push clicked lat lng into points array
	if (document.getElementById("polygon_mode").checked){	//	create (or recreate) the click_polygon object
		create_polygon();
		click_polygon.setMap(my_map);
	}
	if (document.getElementById("line_mode").checked){		//	create or recreate the click_line
		create_line();
		click_line.setMap(my_map);
	}
	clear_markers();
	rewrite_markers();
	calculate_dimensions_and_display();
}// end on_click_function

function create_polygon(){
	click_polygon = new google.maps.Polygon({
		paths: points_array,
		strokeColor: "#FF0000",
		strokeOpacity: 0.8,
		strokeWeight: 2,
		fillColor: "#FF0000",
		fillOpacity: 0.35
	});
}

function create_line(){
	click_line = new google.maps.Polyline({
    path: points_array,
    strokeColor: "#0000ff",
    strokeOpacity: 1.0,
    strokeWeight: 2
  });
}

function rewrite_markers(){
	for(var m= 0; m < points_array.length; m++) {							//loop through point array, making markers
		new_marker = new google.maps.Marker({
			position: points_array[(m)],
			draggable:true,
			map: my_map,												
			// can add arbitrary properties to marker here
			index_number: m,
			title: "index "+m
		});
		
		marker_array.push(new_marker);
		google.maps.event.addListener(new_marker, 'click', function (){ marker_was_clicked (this) } ); // here is a convoluted one to increase scope!!!
		google.maps.event.addListener(new_marker, 'dragend', function (){ marker_was_dragged (this) } );
	}//end for
}// end rewrite_markers

function clear_markers(){
	for(var m= 0; m < marker_array.length; m++) {					
		marker_array[m].setMap(null);
	}
	marker_array = [];
}

function marker_was_clicked (marker_object) {
	points_array.splice(marker_object.index_number,1);
	if (document.getElementById("polygon_mode").checked){
		click_polygon.setMap(null);	//clears polygon
		click_polygon.setMap(my_map);	//writes polygon
	}
	if (document.getElementById("line_mode").checked){
		click_line.setMap(null);
		click_line.setMap(my_map);
	}
	clear_markers();
	rewrite_markers();
	calculate_dimensions_and_display();
}// end marker_was_clicked

function marker_was_dragged(marker_object){
	points_array.splice(marker_object.index_number,1);// removes point
	points_array.splice(marker_object.index_number,0,marker_object.getPosition());// adds updated point
	if (document.getElementById("polygon_mode").checked){
		click_polygon.setMap(null);	//clears polygon
		click_polygon.setMap(my_map);	//writes polygon
	}
	if (document.getElementById("line_mode").checked){
		click_line.setMap(null);
		click_line.setMap(my_map);
	}
	clear_markers();
	rewrite_markers();
	calculate_dimensions_and_display();
}

function calculate_dimensions_and_display(){
	calculated_acres = (google.maps.geometry.spherical.computeArea(points_array)/(1000*1000)*247.105381);
	calculated_acres = calculated_acres.toFixed(2);
	calculated_length = (google.maps.geometry.spherical.computeLength(points_array))/1000*.621371192;
	calculated_length = calculated_length.toFixed(2);
	if (document.getElementById("polygon_mode").checked){
		document.getElementById("area").innerHTML = calculated_acres+" acres";
	}
	if (document.getElementById("line_mode").checked){
		document.getElementById("area").innerHTML = calculated_length+" miles";
	}
}

function mode_was_changed(){
	click_polygon.setMap(null);								//	clears click_polygon from map
	click_line.setMap(null);								//	clears click_line from map
	if (document.getElementById("polygon_mode").checked){	//	create (or recreate) the click_polygon object
		create_polygon();
		click_polygon.setMap(my_map);
	}
	
	if (document.getElementById("line_mode").checked){		//	create or recreate the click_line
		create_line();
		click_line.setMap(my_map);
	}
	calculate_dimensions_and_display();
}

function clear_map(){//removes markers, removes polygon, removes line, sets points array to null
	clear_markers();
	click_polygon.setMap(null);
	click_line.setMap(null);
	points_array = [];
	calculate_dimensions_and_display();
}

function mouse_was_moved(mouse_move_location){
	
	var mouse_latitude = mouse_move_location.lat();
	mouse_latitude = mouse_latitude.toFixed(3);
	var mouse_longitude = mouse_move_location.lng();
	mouse_longitude = mouse_longitude.toFixed(3);
	
	document.getElementById("mouse_location").innerHTML = mouse_latitude+" "+mouse_longitude;

}

function find_address(){
	var my_address = document.getElementById("autocomplete").value;
    my_geocoder.geocode( { 'address': my_address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK){
			my_map.setCenter(results[0].geometry.location);
			points_array.push(results[0].geometry.location);		//	push returned lat lng into points array
			click_polygon.setMap(null);								//	clears click_polygon from map
			click_line.setMap(null);								//	clears click_line from map
			if (document.getElementById("polygon_mode").checked){	//	create (or recreate) the click_polygon object
				create_polygon();
				click_polygon.setMap(my_map);
			}
			if (document.getElementById("line_mode").checked){		//	create or recreate the click_line
				create_line();
				click_line.setMap(my_map);
			}
			clear_markers();
			rewrite_markers();
			calculate_dimensions_and_display();
		}
		else{
			alert("Geocode was not successful for the following reason: " + status);
		}
	});
}

function enter_key_as_submit(event){// note, must pass event, not 'this' to this function
	if (event.keyCode == 13){
		find_address()
	}
}

function help_window(){
	document.getElementById("help_div").style.display = "block";
}

function close_help_window(){
	document.getElementById("help_div").style.display = "none";
}
function load_points(){
	
	clear_map();
	
		
	if (points_array.length>0){

		if (document.getElementById("polygon_mode").checked){	//	create (or recreate) the click_polygon object
			create_polygon();
			click_polygon.setMap(my_map);
		}
		if (document.getElementById("line_mode").checked){		//	create or recreate the click_line
			create_line();
			click_line.setMap(my_map);
		}
		clear_markers();
		rewrite_markers();
		calculate_dimensions_and_display();
		bounding_box();

	}	

}

function bounding_box(){

	if (points_array.length > 0){
		var bounds = new google.maps.LatLngBounds();
		for (var i = 0; i < (points_array.length); i++) {
			bounds.extend(points_array[i]);
		}
		my_map.fitBounds(bounds);// zooms to box
	}
}

function submit_points_array(){

	var point_string = "";
	for (var i = 0; i < (points_array.length); i++) {	// comma between point pairs, but not after the last one
		point_string = point_string+points_array[i];
		
		if (points_array.length > (i+1) ){
			point_string = point_string+",";
		}
	}

	document.getElementById("point_string_variable").value = point_string;
	document.getElementById("download_file").method = "post";
	document.getElementById("download_file").action = "google_map_includes/generate_kmz.php";
	document.getElementById("download_file").submit();

}





//]]>
</script>
<style type="text/css">
#help_div{
	position:fixed;
	display:none;
	left:300px;
	z-index:100;
	background-color:#ffffff;
	width:400px;
	height:300px;
	border-style:solid;
	border-width:1px;
	padding:5px;
	background-color:#b0c4de;
	}


#map_canvas {
	height:670px;
	width:940px;
	position:relative;
	}

#output_table, #test_div{
	position:relative;
	background-color:#b0c4de;
	width:940px;
		}

#autocomplete{
	width:400px;
	}
#map_canvas, #output_table, #test_div, #containing_table{
	position:relative;
	left:0%;
	margin:auto;
	}
#check_box{
	float:right;
	cursor:pointer;
	border-style:solid;
	border-width:1px;
	}

</style>
</head>
<body onload = "initialize()">

<table id="containing_table" >
	
	<tr><!-- middle row -->
		<td class = "middle_left"></td>
		<td class = "middle_middle" id = "middle_cell" >
		<!-- middle content here -->
			<h3>Area and Distance Calculator with Google Maps</h3>
			<div id="help_div">
				<div id="check_box" onclick="close_help_window()" >X</div>
				<p>Clicking on the map places a marker.</p>
				<p>Dragging a marker recalculates the area or distance.</p>
				<p>Entering an address at the bottom of the map zooms to the location and places a marker.</p>
				<p>Clicking on a marker removes the marker.</p>
				<p>You can change from area mode to distance mode by clicking on the radio button.</p>
				<p>The clear map button removes all markers.</p>
			</div>
			<table id = "output_table">
				<tr>
					<td><input type="button" value="Clear Map" onclick = "clear_map();"/>
					</td>
					<td><input type="radio" name="draw_mode" id = "polygon_mode" onclick = "mode_was_changed();" checked = "checked"/> Area Mode
					</td>
					<td><input type="radio" name="draw_mode" id = "line_mode" onclick = "mode_was_changed();" /> Distance Mode
					</td>
					<td>Area/Distance<span id="area">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
					</td>
					<td>Mouse Location<span id="mouse_location">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
					</td>
					<td><input type="button" value="Help" onclick = "help_window();"/>
					</td>
				</tr>
			</table>

			<table id="map_container">
				<tr>

					<td>
						<div id = "map_canvas" ><!-- looks like everything in here gets overwritten --></div>
					</td>

				</tr>
			</table><!-- end map_container -->
			<div id = "test_div">
				<input type="button" value="Go To Address" title = "Enter either an address or lat lng coordinates." onclick = "find_address();" />
				<input type="text" name="autocomplete" id = "autocomplete" onkeydown="enter_key_as_submit(event)" />
			</div>
			

				
			<br />
			<form action="server_side_file_handler.php" method="post" enctype="multipart/form-data">
				<label for="file">Upload an <a href = "gps_unit.php" target = "_blank">ezTour</a> formatted .kmz file or a .kmz file that was generated by this website.<br />Browse to file:&nbsp;&nbsp;</label>
				
				<input type="file" name="file" id="file" />&nbsp;&nbsp;
				Upload file:&nbsp;&nbsp;
				<input type="submit" name="submit" value="Submit File" title="It can take a minute or two to load the data...."/>
			</form>
			<hr />
			<form id = "download_file">
				<input type="button" value="Download your polygon" title = "Save your polygon" onclick = "submit_points_array();"/>
				<input type="hidden" id="point_string_variable" name="point_string_variable" />
				&nbsp;&nbsp;&nbsp;&nbsp;You can save your polygon in .kmz format here.  The file format also works automatically with Google Earth.
			</form>
			<hr />
			<input type="button" value="Reload Points" title = "Reloads uploaded points if present." onclick = "load_points();"/>
			<input type="button" value="Hide Markers" title = "Temporarily hides markers." onclick = "clear_markers();"/>
			<input type="button" value="Show Markers" title = "Shows Markers everywhere there is a vertex." onclick = "rewrite_markers();"/>
			<input type="button" value="Zoom and Center" title = "Zooms to polygon and centers map" onclick = "bounding_box();"/>
		</td>
		<td class = "middle_right"></td>
	</tr>
	<tr><!-- bottom row -->
		<td class = "lower_left"></td>
		<td class = "lower_middle"></td>
		<td class = "lower_right"></td>
	</tr>
</table>



