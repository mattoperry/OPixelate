<?php

namespace olope;

class Pixelate {

  public $supported_file_types;
  private $scale;
  private $grid_size;
  private $file_type;
  private $image;
  private $width;
  private $height;
  private $map;
  private $grid_map;

  public function __construct( $file = '') {
    if ( !self::gd_check() ) {
      throw new Exception( 'gd not supported' );
    }

    $this->$supported_file_types = [ 'image/jpg', 'image/png' ];  //@todo setting

    if ( $file ) {
      $this->process_file( $file );
    }
  }

  private function gd_check() {
    return ( function_exists( 'gd_info' ) );
  }

  private function is_file_supported_type( $file ) {
    if ( !$this->file_type ) {
      $this->file_type = mime_content_type( $file );
    }
    return in_array( $this->file_type, $this->$supported_file_types );
  }

  private function generate_image_object( $file ) {
    if ( !$this->file_type ) {
      $this->file_type = mime_content_type( $file );
    }
    switch ( $this->file_type ) {
      case 'image/png':
        $this->image = imagecreatefrompng( $file );
        break;
      case 'image/jpg':
        $this->image = imagecreatefromjpg( $file );
        break;
      default:  //@todo exception here?
        break;
    }
    /// can we just do $this->image = imagecreatefromstring( file_get_contents( $file ) );
  }

  private function generate_grid_map( ) {
    if ( !$this->scale ) {
      return false;
    }
    $grid_map = [];
    for ( $i = 0; $i < $this->scale; $i++ ) {
      $grid_map[$i] = floor( ( ($i + 1) /$this->scale ) * 255 );
    }
    $this->grid_map = $grid_map;
  }

  public function process_file( $file ) {

    if ( !file_exists( $file ) ) {
      throw new \Exception( 'file not found' );
    }

    if ( !$this->is_file_supported_type( $file ) ) {
      throw new \Exception( 'unsupported file type, supported types are: ' . implode( ' ', $this->$supported_file_types ) );
    }

    $this->generate_image_object( $file );
    // this library only works with grayscale images, so make sure we have one
    imagefilter($this->image, IMG_FILTER_GRAYSCALE);

    $this->width = imagesx( $this->image );
    $this->height = imagesy( $this->image );
  }

  public function pixelate( $scale = 5, $grid_size = 20, $brightness = 1 ) {

    $this->scale = $scale;
    $this->grid_size = $grid_size;
    $this->brightness = $brightness;
    $this->generate_grid_map();

    $this->map = [];

    $x = 0;
    $y = 0;

    while ( ( $y + $this->grid_size ) < $this->height ) {
      //process a row
      while ( ( $x + $this->grid_size ) < $this->width ) {
        $this->process_chunk( $x, $y );
        $x += $this->grid_size;
      }
      $x = 0;
      $y += $this->grid_size;
    }
  }

  public function get_map() {
    return $this->map;
  }

  private function process_chunk( $x, $y ) {
    $total_gray_value = 0;
    $num_pixels = ($this->grid_size) ** 2;
    for( $i = $y; $i < $y + $this->grid_size; $i++ )  {
      for ( $j = $x; $j < $x + $this->grid_size; $j++ ) {
        $total_gray_value += $this->get_gray_level_for_pixel( $j, $i );
      }
    }
    $chunk_gray_raw = $total_gray_value/$num_pixels;
    foreach ( $this->grid_map as $level => $value) {
      if ( $chunk_gray_raw <= $value ) {
          $this->map[ (int) $y/$this->grid_size ][ (int) $x/$this->grid_size ] = $level;
          return;
      }
    }
  }

  private function get_gray_level_for_pixel( $x, $y ) {
    $grayindex = imagecolorat( $this->image, $x, $y );
    return floor( $this->brightness * array_shift( imagecolorsforindex( $this->image, $grayindex ) ) );
  }

}
