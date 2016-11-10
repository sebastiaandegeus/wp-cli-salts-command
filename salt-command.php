<?php

/**
 * Manage salts.
 *
 * @author Sebastiaan de Geus
 */
class Salts_Command extends WP_CLI_Command {

  /**
   * Generates salts to STDOUT or to a file
   *
   * @when before_wp_load
   *
   * @synopsis [--file=<foo>, --format=<php,yaml>]
   *
   */
  function generate( $args, $assoc_args ) {
    $api  = 'https://api.wordpress.org/secret-key/1.1/salt/';
    $data = file_get_contents( $api );

    if ( isset( $assoc_args['format'] ) ) {
      $format = $assoc_args['format'];
      switch ($format) {
        case 'yaml':
          $data = str_replace("define('AUTH_KEY',", "auth_key:", $data);
          $data = str_replace("define('SECURE_AUTH_KEY',","secure_auth_key:", $data);
          $data = str_replace("define('LOGGED_IN_KEY',","logged_in_key:", $data);
          $data = str_replace("define('NONCE_KEY',","nonce_key:", $data);
          $data = str_replace("define('SECURE_AUTH_SALT',","secure_auth_salt:", $data);
          $data = str_replace("define('LOGGED_IN_SALT',","logged_in_salt:", $data);
          $data = str_replace("define('NONCE_SALT',","nonce_salt:", $data);
          $data = str_replace("');","\"", $data);
          $data = str_replace("'","\"", $data);
          break;
      }
    }

    if ( isset( $assoc_args['file'] ) ) {
      $file   = $assoc_args['file'];
      $output = '<?php' . PHP_EOL . PHP_EOL . $data . PHP_EOL;

      if ( ! is_writable( $file ) )
        WP_CLI::error('File is not writable or path is not correct: ' . $file );

      if ( ! file_put_contents( $file, $output ) )
        WP_CLI::error('could not write salts to: ' . $file );

      WP_CLI::success('Added salts to: ' . $file );
      return;
    }

    fwrite( STDOUT, $data );
  }
}

WP_CLI::add_command( 'salts', 'Salts_Command' );
