<?php
/**
 * Created by PhpStorm.
 * User: huypham
 * Date: 5/24/16
 * Time: 12:02 AM
 */
// Choose Page Location
add_filter('huypham_settings_location', function($location){
	return 'options_page'; // theme_page, options_page
});
// Change Page Title
add_filter( 'huypham_settings_page_title', function ( $page_title ) {
	return "Custom Configuration";
} );
// Change Menu Title
add_filter( 'huypham_settings_menu_title', function ( $menu_title ) {
	return 'Custom Configuration';
} );
// Change Page Heading
add_filter( 'huypham_settings_page_heading', function ( $page_heading ) {
	return 'Custom Configuration';
} );
// Add Tab Item
add_filter( 'huypham_setting_heading_tabs', function ( $tabs ) {
	$tabs_more = [
		'homepage' => 'Home Settings',
		'footer'   => 'Footer'
	];

	return array_merge( $tabs, $tabs_more );
} );
// Change name for tab heading General
add_filter( 'huypham_settings_heading_tab_general', function ( $general ) {
	return $general;
} );
// Setting Default Value
add_filter( 'huypham_settings_default_values', function ( $values ) {
	$default_values = [
		'intro'     => __( 'Some intro text for the home page', 'huypham-text' ),
		'tag_class' => false,
		'ga'        => false
	];

	return array_merge( $values, $default_values );
} );
// Add Tab Content
add_action( 'huypham_setting_tab_panel', function ( $settings, $tab ) {
	switch ( $tab ) {
		case 'footer' :
			?>
			<tr>
				<th><label for="ga">Insert tracking code:</label></th>
				<td>
										<textarea id="ga" name="ga" cols="60"
										          rows="5"><?php echo esc_html( stripslashes( $settings["ga"] ) ); ?></textarea><br/>
					<span class="description">Enter your Google Analytics tracking code:</span>
				</td>
			</tr>
			<?php
			break;
		case 'homepage' :
			?>
			<tr>
				<th><label for="intro">Introduction</label></th>
				<td>
										<textarea id="intro" name="intro" cols="60"
										          rows="5"><?php echo esc_html( stripslashes( $settings["intro"] ) ); ?></textarea><br/>
					<span class="description">Enter the introductory text for the home page:</span>
				</td>
			</tr>
			<?php
			break;
	}

}, 10, 2 );

// Add Setting for General
add_action( 'huypham_settings_tab_panel_general', function ( $settings ) {
	?>
	<tr>
		<th><label for="tag_class">Tags with CSS classes:</label></th>
		<td>
			<input id="tag_class" name="tag_class"
			       type="checkbox" <?php if ( $settings["tag_class"] ) {
				echo 'checked="checked"';
			} ?> value="true"/>
			<span class="description">Output each post tag with a specific CSS class using its slug.</span>
		</td>
	</tr>
	<?php
} );

add_filter( 'huypham_settings_save_tab_general', function ( $settings ) {
	$settings['tag_class'] = $_POST['tag_class'];

	return $settings;
} );

add_filter( 'huypham_setting_save_tab', function ( $settings, $tab ) {
	switch ( $tab ) {
		case 'footer' :
			$settings['ga'] = $_POST['ga'];
			break;
		case 'homepage' :
			$settings['intro'] = $_POST['intro'];
			break;
	}
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		if ( $settings['ga'] ) {
			$settings['ga'] = stripslashes( esc_textarea( wp_filter_post_kses( $settings['ga'] ) ) );
		}
		if ( $settings['intro'] ) {
			$settings['intro'] = stripslashes( esc_textarea( wp_filter_post_kses( $settings['intro'] ) ) );
		}
	}

	return $settings;
}, 10, 2 );