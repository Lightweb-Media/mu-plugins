<?php 
switch ( wp_get_environment_type() ) {
    case 'development':
     #   do_nothing();
        break;
      
    case 'staging':
        do_staging_thing();
        break;
      
    case 'production':
    default:
        do_production_thing();
        break;
}

function do_staging_thing(){
    if (get_option('blog_public') === '1'){
        update_option('blog_public', '0');
        add_filter( 'robots_txt', 'wpdocs_name_block_crawling', 999 );
// Activate "Disable browsers index"
add_filter( 'pre_option_blog_public', '__return_zero', 999 );
}

}

function do_production_thing(){
    if (get_option('blog_public') === '0'){
        update_option('blog_public', '1');  
    }
}

/* We filter robots.txt to evade index environments that are not in production */
function wpdocs_name_block_crawling( $output ) {
    $output = '# Crawling block for not-in-production' . PHP_EOL;
    $output .= 'User-agent: *' . PHP_EOL;
    $output .= 'Disallow: /';
    return $output;
 }





function locate_wp_config() {
    static $path;

    if ( null === $path ) {
        $path = false;

        if ( getenv( 'WP_CONFIG_PATH' ) && file_exists( getenv( 'WP_CONFIG_PATH' ) ) ) {
            $path = getenv( 'WP_CONFIG_PATH' );
        } elseif ( file_exists( ABSPATH . 'wp-config.php' ) ) {
            $path = ABSPATH . 'wp-config.php';
        } elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
            $path = dirname( ABSPATH ) . '/wp-config.php';
        }

        if ( $path ) {
            $path = realpath( $path );
        }
    }

    return $path;
}


function set_get_environment_type($file,$env){
    $file_content = file_get_contents($file);
    // replace the data
    $suchmuster = "/define\('WP_ENVIRONMENT_TYPE','([^\s]+)'\);/";
    $replace = "define('WP_ENVIRONMENT_TYPE','".$env."');";
    preg_match($suchmuster,$file_content, $match );
    if (count($match) > 2 && $match[1] != $env){
        preg_replace($suchmuster, $replace, $file_content);
    } else {
        $file_content .= "define('WP_ENVIRONMENT_TYPE','".$env."');";
    }
    file_put_contents($file,$file_content );
    

}

$domain = $_SERVER['SERVER_NAME'];
if (str_ends_with($domain, 'dev-wp.de')) {
    $file = locate_wp_config();
    $env = 'staging';
    set_get_environment_type($file, $env);    
}



