<?php

namespace olope;
include 'class.pixelate.php';

class PixelateRender {

  public $dark_mode = false;
  private $pixelate;
  private $display_map;
  private $symbol_map;

  public function __construct( $file = '', $scale = 6, $grid_size = 20, $brightness = 1) {
    if ( $scale < 2 || $scale > 6 ) {
      echo "Error: invalid scale. Scale must a value from 2 to 6";
    }
    $symbol_maps = [
      2 => ['.', ' '],
      3 => ['o', '.', ' '],
      4 => ['0', 'o', '.', ' '],
      5 => ['0', 'O', 'o', '.', ' '],
      6 => ['0', 'O', 'o', ',', '.', ' '],
    ];
    $this->symbol_map = $symbol_maps[ $scale ];
    $this->pixelate = new Pixelate( $file );
    $this->pixelate->pixelate( $scale, $grid_size, $brightness );
    $this->display_map = $this->pixelate->get_map();
  }

  public function sketch( $dark_mode = false, $echo = true ) {
    $s_map = ( $dark_mode ) ? array_reverse( $this->symbol_map ) : $this->symbol_map;
    $sketch = '';
    foreach ($this->display_map as $row ) {
      foreach ( $row as $cell ) {
        $sketch .= "{$s_map[$cell]} ";
      }
      $sketch .= "\n";
    }
    if ( $echo ) {
      echo $sketch;
    }
    return $sketch;
  }

  public function brick_instructions( $echo = true ) {
    $inst = '';
    foreach ($this->display_map as $row ) {
      $row_inst = '';
      $prev_cell = false;
      $prev_inst_count = 0;
      foreach ( $row as $cell ) {
        if ( false === $prev_cell || $prev_cell === $cell ) {
          $prev_inst_count++;
        }else{
          $row_inst .= $prev_inst_count . 'B' . $prev_cell . ' ';
          $prev_inst_count = 1;
        }
        $prev_cell = $cell;
      }
      $row_inst .= $prev_inst_count . 'B' . $prev_cell . "\n";
      $inst .= $row_inst;
    }
    if ( $echo ) {
      echo $inst;
    }
  }

  public function get_dimensions() {
    return [ $this->get_x_dimension(), $this->get_y_dimension() ];
  }

  public function get_x_dimension() {
    return count($this->display_map[0]);
  }

  public function get_y_dimension() {
    return count($this->display_map);
  }

}
