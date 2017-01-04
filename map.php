<?php
$xt=3600;
$yt=3600;
set_time_limit(0);

$loc=strtoupper($_GET['locator']);

if (file_exists(getcwd().'/cache/'.$loc.'.png')==true) {
	$raw=file_get_contents(getcwd().'/cache/'.$loc.'.png');
	header('Content-type: image/png');
	echo $raw;
	exit();
}

$hll = loc_to_latlon($loc);
//$hll=array(44.04702,5.77914);
//print_r($hll);

/*
$r=bearing($hll[0],$hll[1],35.465,137.264);
print_r($r);exit();
*/


if (isset($_GET['debug'])==false) header('Content-type: image/png');
$png_image = imagecreatetruecolor($xt+800, $yt+800);
$blanc = imagecolorallocate($png_image,  255,255,255); 
$glace = imagecolorallocate($png_image,  210,210,210); 
$noir = imagecolorallocate($png_image,  0,0,0); 
$gris = imagecolorallocate($png_image,  10,10,10); 
$rouge = imagecolorallocate($png_image,  255,0,0); 
$sea = imagecolorallocate($png_image,  0,27,160); 
$land = imagecolorallocate($png_image, 76, 155, 77); 
$lake = imagecolorallocate($png_image,  0,0,255); 
imagefill($png_image,0,0,$glace);
//imagesetpixel($png_image, $xt/2, $yt/2, $rouge);
// imageline($png_image, 0, 0, 150, 150, $black);

  
// Register autoloader
require_once('ShapeFileAutoloader.php');
\ShapeFile\ShapeFileAutoloader::register();

// Import classes
use \ShapeFile\ShapeFile;
use \ShapeFile\ShapeFileException;

$maxdist=0;

$oldcolor=$blanc;

shape('ne_110m_glaciated_areas/ne_110m_glaciated_areas.shp',$lake,true);
shape('ne_110m_lakes/ne_110m_lakes.shp',$lake,true,5);
shape('ne_110m_rivers_lake_centerlines/ne_110m_rivers_lake_centerlines.shp',$land,true,5);
shape('ne_110m_coastline/ne_110m_coastline.shp',$sea,true);
shape('ne_110m_graticules_20/ne_110m_graticules_20.shp',$gris,false,5);


function shape($file,$color,$fill=true,$thick=0) {
	global $png_image,$xt,$yt,$hll,$x,$y,$xt,$yt;
	imagesetthickness($png_image, $thick);
	$ShapeFile = new ShapeFile($file);
	while ($record = $ShapeFile->getRecord(ShapeFile::GEOMETRY_BOTH)) {
//if (isset($_GET['debug'])==true)      print_r($record['shp']);
	    // DBF Data
//if (isset($_GET['debug'])==true)        print_r($record['dbf']);
		foreach ($record as $p2) {
//if (isset($_GET['debug'])==true) print_r($p2);		

			$x=0;
			$y=0;
			$oldx=0;
			$oldy=0;
			foreach ($p2['parts'] as $r) {
				foreach ($r['rings'] as $n) {
//if (isset($_GET['debug'])==true) print_r($n);			
					$x=0;
					$y=0;
					$oldx=0;
					$oldy=0;
					foreach ($n['points'] as $p) {
						dessine($p,$color);
	  				}
				}
				foreach ($r['points'] as $p) {
					dessine($p,$color);
					}
			}
		}
	}
	if ($fill!==false) imagefilltoborder($png_image,0,0,$oldcolor,$color);
	$oldcolor=$color;
}

function dessine($p) {
	global $png_image,$xt,$yt,$hll,$maxdist,$x,$y,$xt,$yt;
	
	//if (isset($_GET['debug'])==true) print_r($p );
							$oldx=$x;
							$oldy=$y;
							$oldpy=$p['y'];
							$oldpx=$p['x'];
							$r=lbearing_dist($hll,array($p['y'],$p['x']));
			//if (isset($_GET['debug'])==true)  print_r($r);
							$sina=sin(deg2rad($r['deg']));
							$cosa=cos(deg2rad($r['deg']));
							if ($r['km']>$maxdist) $maxdist=$r['km'];
							$x=round(($sina*($r['km']/36000)*$xt)+($xt/2),0);
							$y=round(-($cosa*($r['km']/36000)*$xt)+($xt/2),0);
							if (($oldx<>0) && ($oldy<>0) ) {
	if (isset($_GET['debug'])==true)  echo $x."/".$y." ".$oldx.'/'.$oldy."\n\n";
								imageline($png_image,$x+400,$y+400,$oldx+400,$oldy+400,$color);
							}
	//imagesetpixel($png_image, $x+400, $y+400, $noir);
}

//if (isset($_GET['debug'])==true) echo "maxdist=".$maxdist;


$divise=5;

//imageellipse($png_image, $xt/2+100, $yt/2+100, ($maxdist/18000*$xt), ($maxdist/18000*$xt), $noir);
//imageellipse($png_image, $xt/2+100, $yt/2+100,$xt, $xt, $noir);	
//if (isset($_GET['debug'])==false) imagepng($png_image);

$png_image_f=imagescale($png_image,$xt/$divise+800/$divise,$yt/$divise+800/$divise);

imagedestroy($png_image);


//imageellipse($png_image_f, $xt/$divise/2+(400/$divise), $yt/$divise/2+(400/$divise), ($maxdist/18000*$xt/$divise), ($maxdist/18000*$xt/$divise, $noir);
//imageellipse($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)), $xt/$divise, $yt/$divise, $noir);
/*
imagesetthickness($png_image_f, 5);

for ($i = 1; $i < 20; $i++) {
	imageellipse($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)), $maxdist/18000*$xt/$divise+$i+4, $maxdist/18000*$yt/$divise+$i+4, $blanc);
}
for ($i = 1; $i < 4; $i++) {
	imageellipse($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)), $maxdist/18000*$xt/$divise+$i, $maxdist/18000*$yt/$divise+$i, $noir);
}

for ($i = 1; $i < 4; $i++) {
	imageellipse($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)), $maxdist/18000*$xt/$divise+$i+15, $maxdist/18000*$yt/$divise+$i+15, $noir);
}

for ($i = 1; $i < 10; $i++) {
	imageellipse($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)), $i,$i, $rouge);
}

*/



imagesetthickness($png_image_f, 6);
imagearc($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)), $maxdist/18000*$xt/$divise+6, $maxdist/18000*$yt/$divise+6,0,360, $blanc);

imagesetthickness($png_image_f, 3);
imagearc($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)), $maxdist/18000*$xt/$divise, $maxdist/18000*$yt/$divise, 0,360, $gris);

imagearc($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)), $maxdist/18000*$xt/$divise+15, $maxdist/18000*$yt/$divise+15, 0,360,$gris);
imagefilltoborder($png_image_f,0,0,$gris,$blanc);



for ($i = 0; $i < 360; $i+=15) {
	$x1=round((sin(deg2rad($i))*$maxdist/18000*($xt)/$divise/2)+($xt/$divise/2+(400/$divise)),0);
	$x2=round((sin(deg2rad($i))*$maxdist/18000*($xt+50)/$divise/2)+($xt/$divise/2+(400/$divise)),0);
	$y1=round((cos(deg2rad($i))*$maxdist/18000*($yt)/$divise/2)+($xt/$divise/2+(400/$divise)),0);
	$y2=round((cos(deg2rad($i))*$maxdist/18000*($yt+50)/$divise/2)+($xt/$divise/2+(400/$divise)),0);
//	if (isset($_GET['debug'])==true) echo $x1.','.$y1.'  /  '.$x2.','.$y2."\n";
	imageline($png_image_f,$x1,$y1,$x2,$y2,$gris);
	
	
	imagearc($png_image_f, ($xt/$divise/2+(400/$divise)), ($yt/$divise/2+(400/$divise)),5 ,5,0,360, $rouge);
}
$font = getcwd(). '/open24display.ttf';
//echo $font;exit();
for ($i = 0; $i < 360; $i+=15) {
	$angle=(360-$i);
	$v=$i;

	if (strlen($v)==2) $v='0'.$i;
	if (strlen($v)==1) $v='000';
//if (isset($_GET['debug'])==true) echo $v."\n";
	$x1=round((cos(deg2rad($i-92))*$maxdist/18000*($xt+200)/$divise/2)+($xt/$divise/2+(400/$divise)),0);
	$y1=round((sin(deg2rad($i-92))*$maxdist/18000*($yt+200)/$divise/2)+($xt/$divise/2+(500/$divise)),0);
	imagettftext($png_image_f,16,$angle,$x1,$y1-20,$noir,$font,$v);
}

imagettftext($png_image_f,16,0,790,880,$noir,$font,'fg8oj.com');

if (isset($_GET['debug'])==false) imagepng($png_image_f);
if (isset($_GET['debug'])==false) imagepng($png_image_f,getcwd().'/cache/'.$loc.'.png');

imagedestroy($png_image_f);

function loc_to_latlon ($loc) {
	/* lat */
	$l[0] = 
	(ord(substr($loc, 1, 1))-65) * 10 - 90 +
	(ord(substr($loc, 3, 1))-48) +
	(ord(substr($loc, 5, 1))-65) / 24 + 1/48;
	//$l[0] = deg_to_rad($l[0]);
	/* lon */
	$l[1] = 
	(ord(substr($loc, 0, 1))-65) * 20 - 180 +
	(ord(substr($loc, 2, 1))-48) * 2 +
	(ord(substr($loc, 4, 1))-65) / 12 + 1/24;
	//$l[1] = deg_to_rad($l[1]);

	return $l;
}

function lbearing_dist($l1, $l2) {

	$co = cos($l1[1] - $l2[1]) * cos($l1[0]) * cos($l2[0]) +
			sin($l1[0]) * sin($l2[0]);
	$ca = atan2(sqrt(1 - $co*$co), $co);
	$az = atan2(sin($l2[1] - $l1[1]) * cos($l1[0]) * cos($l2[0]),
				sin($l2[0]) - sin($l1[0]) * cos($ca));

	if ($az < 0) {
		$az += 2 * M_PI;
	}

//	$ret[km] = round(6371*$ca);
	$ret['km'] =round(distance($l1[0],$l1[1],$l2[0],$l2[1],'K'),0) ;
//	$ret[deg] = round(rad_to_deg($az));
	$ret['deg'] = bearing($l1[0],$l1[1],$l2[0],$l2[1]) ;
	
	return $ret;
}

function rad_to_deg ($rad) {
	return (($rad/M_PI) * 180);
}

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}
function bearing($lat1, $lon1, $lat2, $lon2) {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        $lonDelta = $lon2 - $lon1;
        $y = sin($lonDelta) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lonDelta);
        $brng = atan2($y, $x);
        $brng = $brng * (180 / pi());
        
        if ( $brng < 0 ) { $brng += 360; }
        
        return $brng;
    }
    
?>