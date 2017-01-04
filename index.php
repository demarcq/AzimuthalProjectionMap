<html> 
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /> 
<title>FG8OJ, Bertrand Demarcq, Guadeloupe Island Amateur Radio</title> 
<meta name="description" content="Radioamateur Guadeloupe FG8OJ" /> 
</head>
<body>
<center>
<h1>Azimuthal Projection Map</h1>
Create your own azimuthal projection map based on your geographical position.
<p>


<form  name="myform2" method="GET" action="/map/map.php">
Maidenhead Locator System (<a href="https://fr.wikipedia.org/wiki/Maidenhead_Locator_System" target="_blank">*</a>) : <input type="text" name="locator" value="FK96IG" />
<input type="submit" name="" value="Create Map" />
<p>
If you don't know you locator, enter your geographical coordinates in format :<br/>
Latitude : <input type="text" size ="2" name="latd"  />
    <input type="text" size ="2" name="latm"  />
    <input type="text" size ="4" name="lats"  />
<select name="latb" style="background-color: #ffdddd">
  <option value="Blank"></option>
  <option value="N">N</option>
  <option value="S">S</option>
</select><br/>
Longitude : <input type="text" size ="2" name="lond"  />
    <input type="text" size ="2" name="lonm"  />
    <input type="text" size ="4" name="lons"  />
<select name="lonb" style="background-color: #ffdddd">
  <option value="Blank"></option>
  <option value="W">W</option>
  <option value="E">E</option>
</select><br/>
<input type="button" onclick="val_ll()" value="Calculate" />
<input type="hidden" name="locc" value="6" />





<input  type="hidden" name="latd2"  />
<input type="hidden" name="latm2"  />
<input type="hidden" name="latb2"  />
<input type="hidden"  name="lond2"  />
<input type="hidden"  name="lonm2"  />
<input type="hidden"  name="lonb2"  />
<input   type="hidden"   name="ngr"  />
<input type="hidden" name="grid" value="" />
<input  type="hidden"   name="eastings" >
<input  type="hidden"   name="northings" >

</form>
</p>
<img src="/map/map.php?locator=FM18lv&s" height="600" width="600" />
</center>




<script>
var deg2rad = Math.PI / 180;
var rad2deg = 180.0 / Math.PI;
var pi = Math.PI;
var locres = 0; 

function val_ll()
{
  var latd = document.myform2.latd.value;
  var latm = document.myform2.latm.value;
  var lats = document.myform2.lats.value;
  var latb = document.myform2.latb.selectedIndex;
  var lond = document.myform2.lond.value;
  var lonm = document.myform2.lonm.value;
  var lons = document.myform2.lons.value;
  var lonb = document.myform2.lonb.selectedIndex;
  //alert(lonb);

  // cope with blank fields
  if (latd == "")
  {
    alert("Latitude degrees must be entered");
    return;
  }

if (latb == -1)  //Safari bug, -1 after reset.
  latb = 0;
if (lonb == -1)
  lonb = 0;

  if (latm == "")
    latm = "0";  document.myform2.latm.value = latm;
  if (lats == "")
    lats = "0";  document.myform2.lats.value = lats;
  if (latb == 0)
    latb = "1";  document.myform2.latb.selectedIndex = latb;  // default N
  if (lond == "")
    lond = "0";  document.myform2.lond.value = lond;
  if (lonm == "")
    lonm = "0";  document.myform2.lonm.value = lonm;
  if (lons == "")
    lons = "0";  document.myform2.lons.value = lons;
  if (lonb == 0)
    lonb = 1;  document.myform2.lonb.selectedIndex = lonb;  // default W

//document.myform2.latb2.selectedIndex = latb;
//document.myform2.lonb2.selectedIndex = lonb;

//alert(lonb);

  //validate
  if (abs(Number(latd)) >= 90)
  {
    alert("Degrees wrong");
    return;
  }
  if (Number(latm) >= 60)
  {
    alert("Minutes wrong");
    return;
  }
  if (Number(lats) >= 60)
  {
    alert("Seconds wrong");
    return;
  }
  if (abs(Number(lond)) >= 180)
  {
    alert("Degrees wrong");
    return;
  }
  if (Number(lonm) >= 60)
  {
    alert("Minutes wrong");
    return;
  }
  if (Number(lons) >= 60)
  {
    alert("Seconds wrong");
    return;
  }

  var lat = Number(latd);
  lat = lat + Number(latm) / 60;
  lat = lat + Number(lats) / 3600;
  if (latb == 2)  // S
    lat = lat * -1;
  var lon = Number(lond);
  lon = lon + Number(lonm) / 60;
  lon = lon + Number(lons) / 3600;
  if (lonb == 1)  // W
    lon = lon * -1;

  document.myform2.latd2.value = latd;  // fill in dmh
  document.myform2.lond2.value = lond;
  latm2 = Number(latm) + Number(lats)/60;
  latm2 = floor(latm2 * 1000)/1000;
  lonm2 = Number(lonm) + Number(lons)/60;
  lonm2 = floor(lonm2 * 1000)/1000;
  document.myform2.latm2.value = latm2;
  document.myform2.lonm2.value = lonm2;
  
  document.myform2.ngr.value = "";
  document.myform2.grid.selectedIndex = 0;
  document.myform2.northings.value = "";
  document.myform2.eastings.value = "";
  document.myform2.locator.value = "";
  document.myform2.latb.selectedIndex = latb;
  document.myform2.lonb.selectedIndex = lonb;
  if (locres == -1)   // Safari bug
    locres = 0;
    
  var loc = calcloc(lon, lat);  //wgs84

  var locy = loc.substring(0, 6 + 2 * locres);
  document.myform2.locator.value = locy;	
  var grid = choose_where(lat, lon);
  var phip = lat * deg2rad;  // deg to rad
  var lambdap = lon * deg2rad;
  if (grid == "")
    return;
  var geo = convert_to_local(grid, phip, lambdap);
  phip = geo.latitude;
  lambdap = geo.longitude;
  //alert(lonb);
  ll2ne(phip, lambdap, grid);
}

function numonly(ev)
{
  var key;
  var keychar;
  if (window.event)
    key = window.event.keycode;
  else if (ev)
    key = ev.which;
  else return true;
  keychar = String.fromCharCode(key);
  if ((key==null) || (key==0) || (key==8) || (key==9) || (key==13) || (key==27))
    return true;
  else if (((".0123456789").indexOf(keychar) > -1))
    return true;
  else
    return false;
}


function mod(y, x)
{
if (y >= 0)
  return y - x * floor(y / x);
else
  return y + x * (floor(-y / x) + 1.0);
}

function atan2(y, x)
{
	return Math.atan2(y, x);
}

function sqrt(x)
{
	return Math.sqrt(x);
}

function tan(x)
{
	return Math.tan(x);
}

function sin(x)
{
	return Math.sin(x);
}

function cos(x)
{
	return Math.cos(x);
}

function acos(x)
{
	return Math.acos(x);
}

function floor(x)
{
	return Math.floor(x);
}

function round(x)
{
	return Math.round(x);
}

function ceil(x)
{
	return Math.ceil(x)
}

function ln(x)
{
	return Math.log(x);
}

function abs(x)
{
	return Math.abs(x);
}

function pow(x, y)
{
	return Math.pow(x, y);
}

function atan(x)
{
	return Math.atan(x);
}

function chr(x)
{
return String.fromCharCode(x);
}

function round(x)
{
	return Math.round(x);
}

function calcloc(e, n)
// calculate IARU locator from lat, lon
// includes correction to hopefully avoid js fp maths error.
// input is in degrees.
{
	e = e + 180;
	ee = e;
	n = n + 90;
	nn = n;
	var locator = "";
	e = e / 20 + 0.0000001;
	n = n / 10 + 0.0000001;
	locator = locator + chr(65 + e) + chr(65 + n);
	e = e - floor(e);
	n = n - floor(n);
	e = e * 10;
	n = n * 10;
	locator = locator + chr(48 + e) + chr(48 + n);
	e = e - floor(e);
	n = n - floor(n);
	e = e * 24;
	n = n * 24;
	locator = locator + chr(65 + e) + chr(65 + n);
	e = e - floor(e);
	n = n - floor(n);
	e = e * 10;
	n = n * 10;
	locator = locator + chr(48 + e) + chr(48 + n);
	e = e - floor(e);
	n = n - floor(n);
	e = e * 24;
	n = n * 24;
	locator = locator + chr(65 + e) + chr(65 + n);
	return locator;
}
function choose_where(lat, lon)
{
var where = "";
if ((lat >= 49) && (lat <= 50) && (lon >= -3) && (lon <= -2))
  where = "Channel Islands"
else if ((lon <= -6) && (lon > -11) && (lat > 51) && (lat <= 54))
  where = "Irish";
else if ((lon < -5.333) && (lon >- 11) && (lat >= 54) && (lat <= 55))
  where = "Irish";
else if ((lon < -5.9) && (lon >= -9) && (lat >= 55) && (lat < 55.5))
  where = "Irish";
else if ((lon < 1.8) && (lon > -9) && (lat > 49.8166) && (lat < 61))
  where = "British";
return where;
}
</script>
</body>
</html>
