# OPixelate
PHP tools for pixelating images for art purposes

Example:

  include( 'class.pixelateRender.php' );

  $p = new olope\PixelateRender( 'somefile.png', 5, 18, 1 );  //5 level scale, 18 pixel chunking, brightness of 1

  $p->sketch(); //calculate and display a preview sketch
  $p->brick_instructions(); //calculate and display bricking instructions
  print_r($p->get_dimensions());  //get pixelated grid dimensions
