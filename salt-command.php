<?php

/**
 * Manage salts.
 *
 * @author Sebastiaan de Geus
 */
class Salts_Command extends WP_CLI_Command {

  /**
   * Generates salts to STDOUT or to a file.
   *
   * ## OPTIONS
   *
   * [--file=<file>]
   * : The name of the file to write to. Default outputs to STDOUT.
   *
   * [--format=<format>]
   * : Can be php or env. Defaults to php.
   *
   * @when before_wp_load
   * @synopsis [--file=<file>] [--format=<format>]
   *
   */
  function generate( $args, $assoc_args ) {
    $defaults = array(
      'format' => 'php',
    );
    $assoc_args = array_merge( $defaults, $assoc_args );

    $api    = 'https://api.wordpress.org/secret-key/1.1/salt/';
    $data   = file_get_contents( $api );
    $output = self::_format_data( $data, $assoc_args['format'] );

    if ( isset( $assoc_args['file'] ) ) {
      $file = $assoc_args['file'];

      if ( ! is_writable( $file ) )
        WP_CLI::error( 'File is not writable or path is not correct: ' . $file );

      if ( ! file_put_contents( $file, $output ) )
        WP_CLI::error( 'Could not write salts to: ' . $file );

      WP_CLI::success( 'Added salts to: ' . $file );
      return;
    }

    fwrite( STDOUT, $output );
  }

  private static function _format_data( $data, $format ) {
    switch ( $format ) {
      case 'env':
        $pattern   = "/define\('([A-Z_]+)',\s*'(.+)'\);/";
        $formatted = "\n\n" . preg_replace($pattern, "$1='$2'", $data) . "\n";
        break;

      case 'php':
      default:
        $formatted = '<?php' . PHP_EOL . PHP_EOL . $data . PHP_EOL;
        break;
    }

    return $formatted;
  }
}

WP_CLI::add_command( 'salts', 'Salts_Command' );
