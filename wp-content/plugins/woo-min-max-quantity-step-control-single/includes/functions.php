<?php 

/**
 * Only for developer
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
if( ! function_exists('dd') ){
    /**
     * Dump and die function
     * 
     * @param mixed ...$vals
     * @return void
     */
    function dd(...$vals) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $backtrace['file'] ?? 'Unknown file';
        $line = $backtrace['line'] ?? 'Unknown line';
        echo '<div style="background: #e1e1e1;border-left: 3px solid #888;padding: 15px;margin: 15px 0;font-family: monospace;border-radius: 6px;overflow-x: auto;">';
        echo '<div style="margin-bottom: 10px;color: #3F51B5;">';
        echo "🛠️ <strong>File:</strong> <span style='color:#8d8d8d;'>$file</span> on line <span style='color:#4b4b4b;'>$line</span>";
        echo '</div>';
        foreach ($vals as $val) {
            ob_start();
            var_dump($val);
            $output = ob_get_clean();
            // HTML entities
            echo '<pre style="color: #777777;background: #ffffff9c;">' . htmlspecialchars($output) . '</pre>';
        }
        echo '</div>';
    }
}

/**
 * Getting term list new/agin generate based on
 * wpml
 * 
 * asole wpml er madhome category/taxonomy asle ter number alada hoy
 * sei jonno sei onusare ID ta ber korar jonno wpml_object_id filter ta use korechi
 *
 * @param Array $terms_data
 * @return Array
 */
function wcmmq_tems_based_wpml( $terms_data ){

    $temp_term = array();
    foreach( $terms_data as $key=>$e_temp ){
        
        foreach( $e_temp as $k=>$val ){
            unset($e_temp[$k]);
            $id = apply_filters( 'wpml_object_id', $k, $key, TRUE);
            $e_temp[$id] =$val;
        }
        $temp_term[$key] = $e_temp;
    }

    return $temp_term;
}

/**
 * Single dimension array of tems 
 * to wpml supported term ids
 * convertion
 * 
 * I have made it for Admin part actually for first time,
 * but it can be use in front-end later, thats why
 * I have make this function to frontEnd functions.php file
 *
 * @param array $term_ids Array of terms ids
 * @param array $taxonomy_name tame of taxonomy key such: product_cat,product_tag etc
 * @return array
 */
function wcmmq_term_ids_wpml( $term_ids, $taxonomy_name ){
    if( ! is_array( $term_ids ) ) return $term_ids;
    $term_temp_ids = array();
    foreach( $term_ids as $k=>$val ){
        
        $id = apply_filters( 'wpml_object_id', $k, $taxonomy_name, TRUE);
        $term_temp_ids[$id] =$val;
    }
    return $term_temp_ids;
}

function wcmmq_get_term_data_wpml(){
    $terms_data = WC_MMQ::getOption( 'terms' );
    $terms_data = is_array( $terms_data ) ? $terms_data : array();
    return wcmmq_tems_based_wpml( $terms_data );
}

function wcmmq_get_message( $keyword, $prefix = WC_MMQ_PREFIX ){
    $f_keyword = $prefix . $keyword; //'msg_min_limit'


    $lang = apply_filters( 'wpml_current_language', NULL );
    $default_lang = apply_filters('wpml_default_language', NULL );

    
    if( $lang !== $default_lang ){
        $f_keyword .= '_' . $lang;
    }
                      
    return WC_MMQ::getOption( $f_keyword );
}
