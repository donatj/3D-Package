<?php
/**
 * This script draws and outputs a Cube.
 *
 * The GD library is used to draw the cube and output it as a
 * PNG image.
 *
 * Developed by Leonel Machava <leonelmachava@gmail.com>
 * http://codentronix.com
 *
 * This code is released under the "MIT License" available at
 * http://www.opensource.org/licenses/mit-license.php
 */
 
/* Represents points in 3D space. */
class Point3D {
  public $x;
  public $y;
  public $z;
 
  public function __construct($x,$y,$z) {
	 $this->x = $x;
	 $this->y = $y;
	 $this->z = $z;
  }
 
  public function rotateX($angle) {
	 $rad = $angle * M_PI / 180;
	 $cosa = cos($rad);
	 $sina = sin($rad);
	 $y = $this->y * $cosa - $this->z * $sina;
	 $z = $this->y * $sina + $this->z * $cosa;
	 return new Point3D($this->x, $y, $z);
  }
 
  public function rotateY($angle) {
	 $rad = $angle * M_PI / 180;
	 $cosa = cos($rad);
	 $sina = sin($rad);
	 $z = $this->z * $cosa - $this->x * $sina;
	 $x = $this->z * $sina + $this->x * $cosa;
	 return new Point3D($x, $this->y, $z);
  }
 
  public function rotateZ($angle) {
	 $rad = $angle * M_PI / 180;
	 $cosa = cos($rad);
	 $sina = sin($rad);
	 $x = $this->x * $cosa - $this->y * $sina;
	 $y = $this->x * $sina + $this->y * $cosa;
	 return new Point3D($x, $y, $this->z);
  }
 
  public function project($width,$height,$fov,$viewerDistance) {
	 $factor = (float)($fov) / ($viewerDistance + $this->z);
	 $x = $this->x * $factor + $width / 2;
	 $y = -$this->y * $factor + $height / 2;
	 return new Point3D($x,$y,$this->z);
  }
}

$img_width  = 400;
$img_height = 400;

$width = 5;
$height = 2;
$depth = 4;

$hWidth = $width / 2;
$hHeight = $height / 2;
$hDepth = $depth / 2;
/* Define the 8 vertices of the cube. */
$vertices = array(
  new Point3D(-$hWidth,	$hHeight,	-$hDepth),
  new Point3D($hWidth,	$hHeight,	-$hDepth),
  new Point3D($hWidth,	-$hHeight,	-$hDepth),
  new Point3D(-$hWidth,	-$hHeight,	-$hDepth),
  new Point3D(-$hWidth,	$hHeight,	$hDepth),
  new Point3D($hWidth,	$hHeight,	$hDepth),
  new Point3D($hWidth,	-$hHeight,	$hDepth),
  new Point3D(-$hWidth,	-$hHeight,	$hDepth)
);
 
/* Define the vertices that compose each of the 6 faces. These numbers are
	indices to the vertex list defined above. */
$faces = array(array(0,1,2,3),array(1,5,6,2),array(5,4,7,6),array(4,0,3,7),array(0,4,5,1),array(3,2,6,7));
 
/* Define colors for each face. */
$colors = array(array(240,240,240),array(200,200,200),array(220,220,220),array(180,180,180),array(160,160,160),array(255,255,255));
 
/* Create the image. */
$im = imagecreatetruecolor($img_width, $img_height);
$white = imagecolorallocatealpha($im,255,255,255,0);
imagefill($im, 1,1,$white);
imageantialias($im, true);
 
$im_colors = array();
 
foreach( $colors as $color ) {
  $im_colors[] = imagecolorallocate($im,$color[0],$color[1],$color[2]);
}
 
/* Assign random values for the angles that describe the cube orientation. */
$angleX = -35;
$angleZ = 15;
$angleY = -30;
 
/* It will store transformed vertices. */
$t = array();
 
/* Transform all the vertices. */
foreach( $vertices as $v ) {
  $t[] = $v->rotateX($angleX)->rotateY($angleY)->rotateZ($angleZ)->project($img_width,$img_height,256,6);
}
 
/* When drawing the cube we must be careful to draw only the faces that are visible.
	We do that by using the Painter's Algorithm, which consists in drawing the faces
	from back to front.
	Note that other algorithms do exist, and are classified as HIDDEN SURFACE REMOVAL
	ALGORITHMS. */
 
/* It will store the average Z value of each face. */
$avgZ = array();
 
/* Calculate the average Z value of each face. */
foreach( $faces as $index=>$f ) {
  $avgZ["$index"] = ($t[$f[0]]->z + $t[$f[1]]->z + $t[$f[2]]->z + $t[$f[3]]->z) / 4.0;
}
 
/* Sort the array in descending order. */
arsort($avgZ);
 
/* Draw the faces from back to front. */
foreach( $avgZ as $index=>$z ) {
  $f = $faces[$index];
  $points = array(
	 $t[$f[0]]->x,$t[$f[0]]->y,
	 $t[$f[1]]->x,$t[$f[1]]->y,
	 $t[$f[2]]->x,$t[$f[2]]->y,
	 $t[$f[3]]->x,$t[$f[3]]->y
  );
  imagefilledpolygon($im,$points,4,$im_colors[$index]);
}
 
/* Tell the browser/client we are outputing a PNG image. */
header("Content-Type: image/png");
 
/* Output the image. */
imagepng($im);