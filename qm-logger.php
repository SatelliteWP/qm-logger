<?php
/**
 * Plugin Name:  QM Logger
 * Description:  Logs for Query Monitor
 * Version:      0.1
 * Plugin URI:   https://github.com/SatelliteWP/qm-logger
 * Author:       SatelliteWP
 * Author URI:   https://www.satellitewp.com/
 * Text Domain:  qml
 * Domain Path:  /languages/
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'qm/output/after', function() {
    global $wp;

    $html_data = [];
    $slow_queries = [];
    
    $db_queries = QM_Collectors::get( 'db_queries' );
    $overview = QM_Collectors::get( 'overview' );

    $html_data['request'] = home_url( add_query_arg( $_GET, $wp->request ) );

    if ( $db_queries ) {
        $db_queries_data = $db_queries->get_data();

        $html_data['db_total_time'] = number_format( $db_queries_data['total_time'], 4, '.', '' );
        $html_data['db_requests'] = $db_queries_data['total_qs'];

        if ( ! empty( $db_queries_data['expensive'] ) ) {
            foreach( $db_queries_data['expensive'] as $row ) {
                $slow_queries[] = array(
                    'request' => home_url( add_query_arg( $_GET, $wp->request ) ),
                    'sql' => $row['sql'],
                    #'caller' => $row['caller'],
                    #'caller_name' => $row['caller_name'],
                    'ltime' => $row['ltime'],
                );
            }
        }
    }

    if ( $overview ) {
        $overview_data = $overview->get_data();
        $html_data['html_time'] = number_format( $overview_data['time_taken'], 4, '.', '' );

        if ( ! empty( $overview_data['memory'] ) ) {
            // MB
			$html_data['memory'] = number_format( $overview_data['memory'] / 1024 / 1024, 4, '.', '' );
		}
    }

    $html_data    = apply_filters( 'qml/output/html/data', $html_data );
    $slow_queries = apply_filters( 'qml/output/sql/queries', $slow_queries );
    $html_file    = apply_filters( 'qml/output/html/filename', WP_CONTENT_DIR . '/qm-logger-html.csv' );
    $sql_file     = apply_filters( 'qml/output/sql/filename', WP_CONTENT_DIR . '/qm-logger-sql.csv' );

    qml_write_to_file( $html_file, $html_data );

    foreach( $slow_queries as $query ) {
        qml_write_to_file( $sql_file, $query );
    }

}, 1000 );


/**
 * Create a CSV file (if needed) and write CSV data to it
 * 
 * @param string $filename Filename
 * @param array  $data     Data to write
 * 
 */
function qml_write_to_file( $filename, $data ) {
    $headers = null;
    $file_exists = file_exists( $filename );

    $fh = fopen( $filename, 'a' );
    if ( $fh === false ) {
        trigger_error( __( 'QML: Could not create log file - ', 'qml' ) . $filename );
    }

    $headers = [];
    if ( ! $file_exists ) {
        $headers = array_keys( $data );
        fputcsv( $fh, $headers );
    }
    else {
        $header = trim( fgets( fopen( $filename, 'r' ) ) );
        $headers = explode( ',', $header );
    }

    $csv_data = [];
    foreach( $headers as $h ) {
        $csv_data[$h] = $data[$h] ?? null;
    }

    if ( ! empty( $csv_data ) ) {
        fputcsv( $fh, $csv_data );
    }

    fclose( $fh );
}