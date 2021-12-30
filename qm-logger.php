<?php
/**
 * Plugin Name: QM Logger
 * Description: Logs for Query Monitor
 * Version: 0.1
 */

add_action( 'qm/output/after', function() {
    global $wp;

    $data = [];
    
    $db_queries = QM_Collectors::get( 'db_queries' );
    $overview = QM_Collectors::get( 'overview' );

    $data['request'] = home_url( add_query_arg( $_GET, $wp->request ) );

    if ( $db_queries ) {
        $db_queries_data = $db_queries->get_data();

        $data['db_total_time'] = number_format( $db_queries_data['total_time'], 4, '.', '' );
        $data['db_requests'] = $db_queries_data['total_qs'];
    }

    if ( $overview ) {
        $overview_data = $overview->get_data();
        $data['html_time'] = number_format( $overview_data['time_taken'], 4, '.', '' );

        if ( ! empty( $overview_data['memory'] ) ) {
            // MB
			$data['memory'] = number_format( $overview_data['memory'] / 1024 / 1024, 4, '.', '' );
		}
    }

    $data = apply_filters( 'qml/output/data', $data );
    $output_file = apply_filters( 'qml/output/html/filename', WP_CONTENT_DIR . '/qm-logger-html.csv' );

    $headers = null;
    $file_exists = file_exists( $output_file );

    $fh = fopen( $output_file, 'a' );
    if ( $fh === false ) {
        trigger_error( __( 'QML: Could not create log file - ', 'qml' ) . $output_file );
    }
    
    $headers = [];
    if ( ! $file_exists ) {
        $headers = array_keys( $data );
        fputcsv( $fh, $headers );
    }
    else {
        $header = trim( fgets( fopen( $output_file, 'r' ) ) );
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

 }, 1000 );
