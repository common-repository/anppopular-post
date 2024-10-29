<?php
/*
Plugin Name: anppopular-post
Plugin URI: http://www.anpstudio.com/2012/09/anppopular-post-nuevo-plugin-para-mostrar-los-post-mas-comentados-de-una-forma-muy-grafica/ 
Description: Widget to display a list of the most commented posts. The posts are displayed on a color scale of colors. 
Author: antocara
Version: 1.0.6
Author URI: http://www.anpstudio.com
*/
/*
Copyright 2012  Antonio Carabantes(Email : antocara@gmail.com)

This program is free software: you can redistribute it and/or modify

it under the terms of the GNU General Public License as published by

the Free Software Foundation, either version 3 of the License, or

(at your option) any later version.

This program is distributed in the hope that it will be useful,

but WITHOUT ANY WARRANTY; without even the implied warranty of

MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

GNU General Public License for more details.

You should have received a copy of the GNU General Public License

along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
/*
Count views based in plugin WP-post-view of Towards Technology http://answer2me.com/
*/
?>
<?php
//Activationes varias
register_activation_hook(__FILE__, wpp_init());
register_uninstall_hook(__FILE__, wpp_destroy());
add_action('admin_head', 'post_view_style');
add_action('manage_posts_custom_column', 'show_post_row_views', 10, 2);
add_filter('manage_posts_columns', 'show_post_header_views');



/**
 * Cuando se activa el plugin se crea una tabla
 *
 * @global  $wpdb
 */
function wpp_init() {
    global $wpdb;
    $table = $wpdb->prefix . "postview";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $sql = "CREATE TABLE " . $table .
                " ( UNIQUE KEY id (post_id), post_id int(10) NOT NULL,
             view int(10),
            view_datetime datetime NOT NULL default '0000-00-00 00:00:00'
            )";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

/**
 * Se borra el plugin, se borra la tabla
 * @global $wpdb $wpdb
 */
function wpp_destroy() {
    global $wpdb;
    $table = $wpdb->prefix . "postview";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $sql = "DROP TABLE " . $table;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

/**
 * Estilos panel de adminsitración
 */
function post_view_style() {

    echo
    '<style type="text/css">
	.column-views {
		width: 60px;
		text-align: right;
	}
	</style>';
}

/**
 * Se crea una columna en el listado de post del panel de administración
 *
 * @param array $columns
 * @return <type> 
 */
function show_post_header_views($columns) {
    $columns['views'] = __('Views');
    return $columns;
}

/**
 *
 * Se muestran el número de visitas en cada post en el panel de administración
 *
 * @param <type> $column_name
 * @param <type> $post_id
 * @return <type> 
 */
function show_post_row_views($column_name, $post_id) {
    if ($column_name != 'views')
        return;
    echo wp_get_post_views($post_id);
}

 /**
  *
  * Función que muestra el número de visitas a cada post
  *
  */
  
if (!function_exists('echo_post_views')) {

    /**
     * Echo, print or display the views of the post.
     * @param <type> $post_id
     */
    function echo_post_views($post_id) {
        if (wp_update_post_views($post_id) == 1) {
            $views = wp_get_post_views($post_id);
             number_format_i18n($views);
        } else {
            0;
        }
    }

}

/**
 * Returns 1 if successfully updated post views.
 *
 * @global $wpdb $wpdb
 * @param <type> $views
 * @param <type> $post_id
 * @return <type> 
 */
function wp_insert_post_views($views, $post_id) {
    global $wpdb;
    $table = $wpdb->prefix . "postview";

    $result = $wpdb->query("INSERT INTO $table VALUES($post_id,$views,NOW())");
    return ($result);
}

/**
 * Returns 1 if successfully updated post views.
 *
 * @global <type> $wpdb
 * @param <type> $post_id
 * @return <type>
 */
function wp_update_post_views($post_id) {
    global $wpdb;
    $table = $wpdb->prefix . "postview";

    $views = wp_get_post_views($post_id) + 1;
        
    
    
    if ($wpdb->query("SELECT view FROM $table WHERE post_id = '$post_id'") != 1)
        wp_insert_post_views($views, $post_id);
    $result = $wpdb->query("UPDATE $table SET view=$views, view_datetime=NOW()  WHERE post_id = '$post_id'");
    return ($result);
}

/**
 * Get the post views amount.
 * @global $wpdb $wpdb
 * @param <type> $post_id
 * @return <type> 
 */
function wp_get_post_views($post_id) {
    global $wpdb;
    $table = $wpdb->prefix . "postview";
    $result = $wpdb->get_results("SELECT view FROM $table WHERE post_id = '$post_id'", ARRAY_A);
    if (!is_array($result) || empty($result)) {
        return "0";
    } else {
        return $result[0]['view'];
    }
}
/**
 *
 * añado las funciones dentro del single.php
 *
 */
function anp_post_vistos($content) {
	if(is_singular()){
	 $content.=echo_post_views(get_the_ID());
	 return $content;
	}else{
	
	return $content;
	}
		 
}
add_action('the_content', anp_post_vistos);
/*
 *
 * registramos jscolor
 *
 */
function anp_regis_jscolor() {
	
	wp_register_script('anp_jscolor', plugins_url('/js/jscolor/jscolor.js', __FILE__),"", "", true);
	wp_enqueue_script('anp_jscolor', 1);
	
	wp_register_script('anp_script', plugins_url('/js/script.js', __FILE__));
	wp_enqueue_script('anp_script');
} 
add_action('admin_print_scripts-widgets.php', 'anp_regis_jscolor');



/*
 *
 * Activamos multilenguaje
 *
 */ 
function traduce_anp_popular_post(){

	load_plugin_textdomain('anp_text_popular_post', false, basename( dirname( __FILE__ ) ) . '/lang' );
}
add_action('init', 'traduce_anp_popular_post');

/**
 * 
 * Creamos un widget
 *
 **/		
class anp_popular_post_widget extends WP_Widget {
		

		public function __construct() {
			parent::__construct(
		 		'anp_post_popular_post', // Base ID
				'anp Popular Post', // Name
				array( 'description' => __( 'Displays a list of most commented post', 'anp_text_popular_post' ), ) // Args
			);
		}
	/**
	 * Front-end  widget.
	 *
	 */
		public function widget( $args, $instance ) {
			extract( $args );
			
			$title = apply_filters( 'widget_title', $instance['title'] );
			$anp_num_post= $instance['anp_num_post'];
			$anp_colores_post = $instance['anp_colores_post'];
			$anp_autor = $instance['anp_autor'];
			$anp_color_back_num = $instance['anp_color_back_num'];
			$anp_color_lett = $instance['anp_color_lett'];
			$anp_color_text_num = $instance['anp_color_text_num'];
			$anp_comment_view = $instance['anp_comment_view'];
			$anp_num_com_time = $instance['anp_num_com_time'];
			
			
			update_option('anp_color_back_num', $anp_color_back_num);
			update_option('anp_color_lett', $anp_color_lett);
			update_option('anp_color_text_num', $anp_color_text_num);
			
			
			
			global $post, $wpdb, $count;
	
			echo $before_widget;
			if ( ! empty( $title ) )
						echo $before_title . $title . $after_title;
			?>
				<div class="num_post_list">
					<?php 
						/**
						 *
						 * hacemos el query según la opción elegida
						 * en la opción de historial de comentarios
						 *
						 */
						 switch ($anp_num_com_time){
						 
						  	case 1:
						      $result = $wpdb->get_results("
						      SELECT comment_count,ID,post_title 
						      FROM $wpdb->posts 
						      ORDER BY comment_count DESC LIMIT 0 , $anp_num_post");
						      
						     break; 
						     
						    case 2:
						   
							   $result = $wpdb->get_results("
							   SELECT comment_post_ID, COUNT( comment_post_ID ) AS 'comment_count' , ID, post_title 
							   FROM $wpdb->posts, $wpdb->comments
							   WHERE comment_approved =1 
							   AND $wpdb->comments.comment_post_ID = $wpdb->posts.ID
							   AND comment_date > DATE_SUB( NOW( ) , INTERVAL 1 DAY )
							   GROUP BY comment_post_ID
							   ORDER BY comment_date ASC LIMIT 0 , $anp_num_post");
						    break;  
						    
						    case 3:
						    
						    	   $result = $wpdb->get_results("
						    	   SELECT comment_post_ID, COUNT( comment_post_ID ) AS 'comment_count' , ID, post_title 
						    	   FROM $wpdb->posts, $wpdb->comments
						    	   WHERE comment_approved =1 
						    	   AND $wpdb->comments.comment_post_ID = $wpdb->posts.ID
						    	   AND comment_date > DATE_SUB( NOW( ) , INTERVAL 1 WEEK )
						    	   GROUP BY comment_post_ID
						    	   ORDER BY comment_date ASC LIMIT 0 , $anp_num_post");
						     break; 
						     
						     case 4:
						     
						     	   $result = $wpdb->get_results("
						     	   SELECT comment_post_ID, COUNT( comment_post_ID ) AS 'comment_count' , ID, post_title 
						     	   FROM $wpdb->posts, $wpdb->comments
						     	   WHERE comment_approved =1 
						     	   AND $wpdb->comments.comment_post_ID = $wpdb->posts.ID
						     	   AND comment_date > DATE_SUB( NOW( ) , INTERVAL 15 DAY )
						     	   GROUP BY comment_post_ID
						     	   ORDER BY comment_date ASC LIMIT 0 , $anp_num_post");
						      break; 
						     
						     case 5:
						     
						     	   $result = $wpdb->get_results("
						     	   SELECT comment_post_ID, COUNT( comment_post_ID ) AS 'comment_count' , ID, post_title 
						     	   FROM $wpdb->posts, $wpdb->comments
						     	   WHERE comment_approved =1 
						     	   AND $wpdb->comments.comment_post_ID = $wpdb->posts.ID
						     	   AND comment_date > DATE_SUB( NOW( ) , INTERVAL 30 DAY )
						     	   GROUP BY comment_post_ID
						     	   ORDER BY comment_date ASC LIMIT 0 , $anp_num_post");
						      break;
						 } 
						/** 
						 * 
						 * extraemos el número mayor dentro del array
						 * de los números de comentarios
						 *
						 */
						foreach ($result as $post) {
							$anp_mayor = $post->comment_count;
							break;
						}   
						
						$post_nu_clas=1; //añadimos valores a la clase css de cada post
						
												
												
						/**	
						 *
						 * recorremos el query y mostramos todos los resultados
						 * según se eliga por comentarios o por visitas
						 *
						 */								
					if ($anp_comment_view == 1){	// si es 1 mostramos los post más comentados
						foreach ($result as $post) {
							setup_postdata($post);
							$postid = $post->ID;
							$title = $post->post_title;
							$commentcount = $post->comment_count;
							
							
									if ($commentcount != 0) { ?>
									<div class="post <?php echo 'a'.$post_nu_clas;?>" style="width: <?php 
																							//hacemos proporcional la longitud de la barra colores
																							$anp_barra_pro_comm=45+(55/($anp_mayor/$commentcount));
																							echo $anp_barra_pro_comm;
			
																									?>%!important;"><?php $post_nu_clas++;?>
									<div class="num_com"><?php echo $commentcount; ?></div>
									<div class="tex_anp"><a href="<?php echo get_permalink($postid); ?>"><?php echo $title; ?></a></div></div>
									 
									 <?php } 
							}
						}else{	//mostramos los post por visitas
						
								switch ($anp_num_com_time){
									
									case 1:
									 $table = $wpdb->prefix . "postview";
									 $result = $wpdb->get_results("
									 SELECT view_datetime, view, post_ID, ID, post_title 
									 FROM $table, $wpdb->posts 
									 WHERE $table.post_ID = $wpdb->posts.ID
									 
									 ORDER BY view DESC LIMIT 0 , $anp_num_post");
									 
									
									break;	
									
									case 2:	
									 				 
									 $table = $wpdb->prefix . "postview";
									 $result = $wpdb->get_results("
									 SELECT view_datetime, view, post_ID, ID, post_title,  
									 FROM $table, $wpdb->posts 
									 WHERE $table.post_ID = $wpdb->posts.ID
									 AND view_datetime > DATE_SUB( NOW( ) , INTERVAL 1 DAY )
									 ORDER BY view DESC LIMIT 0 , $anp_num_post");
									break;
									
									case 3:					 
									 $table = $wpdb->prefix . "postview";
									 $result = $wpdb->get_results("
									 SELECT view_datetime, view, post_ID, ID, post_title 
									 FROM $table, $wpdb->posts 
									 WHERE $table.post_ID = $wpdb->posts.ID
									 AND view_datetime > DATE_SUB( NOW( ) , INTERVAL 7 DAY )
									 ORDER BY view DESC LIMIT 0 , $anp_num_post");
									break;
									
									case 3:					 
									 $table = $wpdb->prefix . "postview";
									 $result = $wpdb->get_results("
									 SELECT view_datetime, view, post_ID, ID, post_title 
									 FROM $table, $wpdb->posts 
									 WHERE $table.post_ID = $wpdb->posts.ID
									 AND view_datetime > DATE_SUB( NOW( ) , INTERVAL 15 DAY )
									 ORDER BY view DESC LIMIT 0 , $anp_num_post");
									break;
									
									case 3:					 
									 $table = $wpdb->prefix . "postview";
									 $result = $wpdb->get_results("
									 SELECT view_datetime, view, post_ID, ID, post_title 
									 FROM $table, $wpdb->posts 
									 WHERE $table.post_ID = $wpdb->posts.ID
									 AND view_datetime > DATE_SUB( NOW( ) , INTERVAL 30 DAY )
									 ORDER BY view DESC LIMIT 0 , $anp_num_post");
									break;
								 }
								 
								 /** 
								  * 
								  * extraemos el número mayor dentro del array
								  * de los números de comentarios
								  *
								  */
								 foreach ($result as $post) {
								 	$anp_mayor = $post->view;
								 	break;
								 }   
								
								foreach ($result as $post) {
									setup_postdata($post);
									$postid = $post->ID;
									$title = $post->post_title;
									$commentcount = $post->view; //{ 
								
									
								if ($commentcount != 0) { ?>
								
								<div class="post <?php echo 'a'.$post_nu_clas;?>" style="width: <?php 
																						//hacemos proporcional la longitud de la barra colores
																						$anp_barra_pro_comm=45+(55/($anp_mayor/$commentcount));
																						echo $anp_barra_pro_comm;
																								?>%!important;">
								<div class="num_com"><?php  echo $post_nu_clas; ?></div>
								<div class="tex_anp"><a href="<?php echo get_permalink($postid); ?>"><?php the_title(); ?></a></div></div><?php $post_nu_clas++;?>
								
								
							<?php }
							}
								
								
						}?>
							
					
					
					<div id="anp_credit"> 
						<?php if($anp_autor == true)
							_e('Developed by <a href="http://www.anpstudio.com" rel="nofollow">anpstudio.com</a>', 'anp_text_popular_post');?>
					</div>
					
				</div>
				<?php 
				
			echo $after_widget;
			
				
				/******
				 * cargo hojas d estilo en función de la elección
				 ******/
				 
				switch ($anp_colores_post){
				
				 	case 1:
				 
					 // añado al head hoja de estilos para shortcode  
				     wp_register_style( 'anp_estilos', plugins_url('css/anp_popular_post.css', __FILE__ ));  
				     wp_enqueue_style( 'anp_estilos' ); 
				     
				    break;  
				    case 2:
				    
				    	 // añado al head hoja de estilos para shortcode  
				        wp_register_style( 'anp_estilos_azul', plugins_url('css/anp_popular_post_azul.css', __FILE__ ));   
				        wp_enqueue_style( 'anp_estilos_azul' ); 
				        
				    break; 
				    case 3:
				    
				    	 // añado al head hoja de estilos para shortcode  
				        wp_register_style( 'anp_estilos_verde', plugins_url('css/anp_popular_post_verde.css', __FILE__ ) );   
				        wp_enqueue_style( 'anp_estilos_verde' ); 
				        
				    break;   
				 	case 4:
				 
				 	 // añado al head hoja de estilos para shortcode  
				     wp_register_style( 'anp_estilos_rojo', plugins_url('css/anp_popular_post_rojo.css', __FILE__ ) );  
				     wp_enqueue_style( 'anp_estilos_rojo' ); 
				     
				    break; 
				
				}
		}
	/**
     * Sanitize widget form values as they are saved.
	 *
	 */
		public function update($new_instance, $old_instance) {                
		    return $new_instance;
		}
	/**
	 * backend widget
	 *
	 */ 
		public function form($instance){
		
					$title = esc_attr($instance['title']);
					$anp_num_post = esc_attr($instance['anp_num_post']);
					$anp_colores_post = esc_attr($instance['anp_colores_post']);
					$anp_autor = $instance['anp_autor'];
					$anp_color_back_num = $instance['anp_color_back_num'];
					$anp_color_lett = $instance['anp_color_lett'];
					$anp_color_text_num = $instance['anp_color_text_num'];
					$anp_comment_view = $instance['anp_comment_view'];
					$anp_num_com_time = $instance['anp_num_com_time'];
					
					
					
					
					?>
					
				    <p>
				        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','anp_text_popular_post'); ?></label>
				        <input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" />
				    </p>
				    <p>
				        <label for="<?php echo $this->get_field_id('anp_comment_view'); ?>"><?php _e('Show Most comment post or Most Views Post:','anp_text_popular_post'); ?></label>
				         <select name="<?php echo $this->get_field_name('anp_comment_view'); ?>" class="widefat" id="anp_opciones">
				         	<?php for ( $i = 1; $i <= 2; $i += 1) { ?>
				         	<option value="<?php echo $i; ?>" <?php if($anp_comment_view == $i){ echo "selected='selected'";} ?>>
				         	
				         		<?php 
				         			switch ($i) {
				         				case 1:
				         				_e('Most comment post', 'anp_text_popular_post');
				         				break;
				         					           				
				         				case 2:
				         				_e('Most Views Post', 'anp_text_popular_post');
				         				break;		           				
				         					           						
				           			};
				         					           		
				         		?>
				        	</option>
				        	<?php } ?>
				         </select>
				    </p>
				    
				   
				    <p>
				    	 <label for="<?php echo $this->get_field_id('anp_num_com_time'); ?>"><?php _e('Time Interval: (Only for Most comment post','anp_text_popular_post'); ?></label>
				    	 <select name="<?php echo $this->get_field_name('anp_num_com_time'); ?>" class="widefat" id="anp_opciones_tiempo">
				    		   <?php for ( $i = 1; $i <= 5; $i += 1) { ?>
				    		    <option value="<?php echo $i; ?>" <?php if($anp_num_com_time == $i){ echo "selected='selected'";} ?>>
				    			   <?php 
				    			        switch ($i) {
				    			            case 1:
				    			            _e('All the time', 'anp_text_popular_post');
				    			            break;
				    			            		
				    			            case 2:
				    			            _e('Last 24 hours', 'anp_text_popular_post');
				    			            break;
				    			            
				    			            case 3:
				    			            _e('Last 7 days', 'anp_text_popular_post');
				    			            break;
				    			            
				    			            case 4:
				    			            _e('Last 15 days', 'anp_text_popular_post');
				    			            break;
				    			            
				    			            case 5:
				    			            _e('Last 30 days', 'anp_text_popular_post');
				    			            break;
				    
				    			          };
				    			            
				    			       ?>
				    		      </option>
				    		       <?php } ?>
				    	</select>
				    </p> 
				    
				   	<p>
				        <label for="<?php echo $this->get_field_id('anp_num_post'); ?>"><?php _e('Number of post to show:','anp_text_popular_post'); ?></label>
				        <select name="<?php echo $this->get_field_name('anp_num_post'); ?>" class="widefat" id="<?php echo $this->get_field_id('anp_num_post'); ?>">
				            <?php for ( $i = 1; $i <= 10; $i += 1) { ?>
				            <option value="<?php echo $i; ?>" <?php if($anp_num_post == $i){ echo "selected='selected'";} ?>><?php echo $i; ?></option>
				            <?php } ?>
				        </select>
				    </p>
				    <p>
				        <label for="<?php echo $this->get_field_id('anp_colores_post'); ?>"><?php _e('Color Palette:','anp_text_popular_post'); ?></label>
				        <select name="<?php echo $this->get_field_name('anp_colores_post'); ?>" class="widefat" id="<?php echo $this->get_field_id('anp_colores_post'); ?>">
				            <?php for ( $i = 1; $i <= 4; $i += 1) { ?>
				            <option value="<?php echo $i; ?>" <?php if($anp_colores_post == $i){ echo "selected='selected'";} ?>>

				           		<?php 
				           			switch ($i) {
				           				case 1:
				           				_e('Normal', 'anp_text_popular_post');
				           				break;
				           				
				           				case 2:
				           				_e('Blue', 'anp_text_popular_post');
				           				break;
				           				
				           				case 3:
				           				_e('Green', 'anp_text_popular_post');
				           				break;
				           				
				           				case 4:
				           				_e('Red', 'anp_text_popular_post');
				           				break;	
				           						
				           			};
				           		
				           		 ?>
				           	</option>
				           
				            <?php } ?>
				        </select>
				    </p>
				    <p><label for="<?php echo $this->get_field_id('anp_autor'); ?>"><?php _e('Credit the author show', 'anp_text_popular_post'); ?></label>
				    	<input id="<?php echo $this->get_field_id('anp_autor') ; ?>" class="checkbox" type="checkbox" value="1" name="<?php echo $this->get_field_name('anp_autor');?>" <?php checked( '1', $anp_autor );?> />
				       
				    </p>
				    
				   
				    <p><label for="<?php echo $this->get_field_id('anp_color_back_num'); ?>"><?php _e('Background color number of comments', 'anp_text_popular_post'); ?></label>
				    <input type="text" name="<?php echo $this->get_field_name('anp_color_back_num'); ?>"  class="color {required:false}" value="<?php echo $anp_color_back_num; ?>" /><div id="color1"></div>
				    </p>
				    
				    <p><label for="<?php echo $this->get_field_id('anp_color_text_num'); ?>"><?php _e('Text color of the number of comments', 'anp_text_popular_post'); ?></label>
				    <input type="text" name="<?php echo $this->get_field_name('anp_color_text_num'); ?>" class="color {required:false}" value="<?php echo $anp_color_text_num; ?>" />
				    </p>
				    
				    <p><label for="<?php echo $this->get_field_id('anp_color_lett'); ?>"><?php _e('Text color of the post', 'anp_text_popular_post'); ?></label>
				    <input type="text" name="<?php echo $this->get_field_name('anp_color_lett'); ?>" class="color {required:false}" value="<?php echo $anp_color_lett; ?>" />
				    </p>
				    <p><?php _e('Do you like this plugin?', 'anp_text_popular_post'); ?> <a title="<?php _e('Rate anp Popular Posts!', 'anp_text_popular_post'); ?>" href="http://wordpress.org/extend/plugins/anppopular-post/#rate-response" target="_blank"><strong><?php _e('Rate it', 'anp_text_popular_post'); ?></strong></a> <?php _e('on the official Plugin Directory!&nbsp;', 'anp_text_popular_post'); ?><a href="http://www.anpstudio.com/themes/" rel="nofollow" target="_blank"><?php _e('and visit our section of themes', 'anp_text_popular_post'); ?></a>
				    </p>
				   
				    
									   
					<?php
				}	
				
			

}
/*
 *
 * registramos el widget
 *
 */

function registro_anp_popular_post()
{
	register_widget('anp_popular_post_widget');
}
add_action('widgets_init', 'registro_anp_popular_post');

/*
 *
 * cambiamos estilos CSS de las opciones disponibles en widget
 *
 */
function anp_head(){
	$opciones=get_option('anp_color_back_num');
	$opcion_tex=get_option('anp_color_lett');
	$opcion_tex_number=get_option('anp_color_text_num');
	$estilos= '<style type="text/css">
					div.num_post_list div.num_com{
						background-color: #'.$opciones.'!important;
						color: #'.$opcion_tex_number.'!important;
					}	
					div.num_post_list div.post a{
						color: #'.$opcion_tex.'!important;
					}
	
				</style>';
					
  
 	echo $estilos;
	
}
add_action('wp_head', 'anp_head');