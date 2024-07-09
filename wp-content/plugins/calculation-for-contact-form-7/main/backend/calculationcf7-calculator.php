<?php
/**
** A base module for the following types of tags:
** 	[calculator]  # calculator
**/


/* Tag generator */
add_action( 'wpcf7_admin_init', 'CALCULATIONCF7_add_calculator_tag_generator_cf7_form', 18, 0 );
function CALCULATIONCF7_add_calculator_tag_generator_cf7_form() {
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 'calculator', __( 'calculator', 'contact-form-7' ),
		'CALCULATIONCF7_calculator_tag_generator_content' );
}


/* Tag generator inner content */
function CALCULATIONCF7_calculator_tag_generator_content( $contact_form, $args = '' ) {
	$wpcf7_contact_form = WPCF7_ContactForm::get_current();
	$contact_form_tags = $wpcf7_contact_form->scan_form_tags();
	$calculator_args = wp_parse_args( $args, array() );
	$calculator_type = 'calculator';
	?>
	<div class="control-box">
		<fieldset>
			<table class="form-table">
				<tbody>

					<tr>
						<th>
							<label for="<?php echo esc_attr( $calculator_args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $calculator_args['content'] . '-name' ); ?>" />
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo esc_attr( $calculator_args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $calculator_args['content'] . '-id' ); ?>" />
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo esc_attr( $calculator_args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $calculator_args['content'] . '-class' ); ?>" />
						</td>
					</tr>
					
					<tr>
						<th>
							<label for="<?php echo esc_attr( $calculator_args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Formulas', 'contact-form-7' ) ); ?>
							</label>
						</th>
						<td>
								<?php 
								   $calculationcf7_tag = array();
									foreach ($contact_form_tags as $contact_form_tag) {
										if ( $contact_form_tag['type'] == 'number' || $contact_form_tag['type'] == 'number*' || $contact_form_tag['type'] == 'radio' || $contact_form_tag['type'] == 'select' || $contact_form_tag['type'] == 'select*' || $contact_form_tag['type'] == 'text*' || $contact_form_tag['type'] == 'text' || $contact_form_tag['type'] == 'checkbox' || $contact_form_tag['type'] == 'checkbox*' || $contact_form_tag['type'] == 'rangeslider' || $contact_form_tag['type'] == 'rangeslider*' || $contact_form_tag['type'] == 'calculator'){
											$calculationcf7_tag[] = $contact_form_tag['name'];
										}
									} 
								?>
							<p><span><strong><u>Field Name</u></strong></span><br>	
							<?php echo esc_attr(implode(' , ', $calculationcf7_tag)); ?></p>
							<textarea rows="3" class="large-text code" name="values" id="<?php echo esc_attr( $calculator_args['content'] . '-values' ); ?>"></textarea> <br>
							<?php _e( 'Ex: sqrt(number-12) % number-13', 'contact-form-7' ); ?> <br>
							<?php _e( 'Ex: radio-108 + checkbox-345 + ( number-667 + number-24 ) / 2', 'contact-form-7' ); ?> <br>
							<?php _e( 'Ex: checkbox-77 ** number-24', 'contact-form-7' ); ?>
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo esc_attr( $calculator_args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Prefix', 'contact-form-7' ) ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="calculationcf7_Prefix" class="Prefixvalue oneline option" id="<?php echo esc_attr( $calculator_args['content'] . '-Prefix' ); ?>" disabled/>
							<label>Required Pro Version for add Prefix. <a href="https://www.plugin999.com/plugin/calculation-for-contact-form-7/" target="_blank">Click Here To Get Pro Version</a></label>

						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo esc_attr( $calculator_args['content'] . '-Precision' ); ?>"><?php echo esc_html( __( 'Result Precision (digits after decimal point)', 'contact-form-7' ) ); ?>
							</label>
						</th>
						<td>
							<input type="number" name="calculationcf7_Precision" class="Precision oneline option" id="<?php echo esc_attr( $calculator_args['content'] . '-Precision' ); ?>"  min="0"/>
						</td>
					</tr>

				</tbody>
			</table>
		</fieldset>
	</div>

	<div class="insert-box">
		<input type="text" name="<?php echo esc_attr($calculator_type); ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

		<div class="submitbox">
		<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
		</div>

		<br class="clear" />

		<p class="description mail-tag"><label for="<?php echo esc_attr( $calculator_args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $calculator_args['content'] . '-mailtag' ); ?>" /></label></p>
	</div>
	<?php
}


/* TAg calculator */
add_action( 'wpcf7_init', 'CALCULATIONCF7_add_calculator_tag_cf7_form', 10, 0 );
function CALCULATIONCF7_add_calculator_tag_cf7_form() {
	wpcf7_add_form_tag( array( 'calculator', 'calculator*' ),
		'CALCULATIONCF7_calculator_tag_handler_in_cf7_form', array( 'name-attr' => true) );
}


/* tag Handler */
function CALCULATIONCF7_calculator_tag_handler_in_cf7_form( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$calculator_atts = array();
	
	$calculator_validation_error = wpcf7_get_validation_error( $tag->name );
	$calculator_class = wpcf7_form_controls_class( $tag->type );
	$calculator_class .= ' wpcf7-validates-as-calculator';
	$calculator_atts['id'] = $tag->get_id_option();
	$calculator_atts['class'] = $tag->get_class_option( $calculator_class );

	$calculator_atts['readonly'] = 'readonly';
	
	if ( $tag->has_option( 'readonly' ) ) {
		$calculator_atts['readonly'] = 'readonly';
	}

	$calculator_value = (string) reset( $tag->values );

	$calculator_value = $tag->get_default_option( $calculator_value );
	$calculator_value = wpcf7_get_hangover( $tag->name, $calculator_value );
	
	
	$calculator_atts['type'] = 'text';

	$calculator_atts['name'] = $tag->name;

	$calculator_atts['class'] .= " calculationcf7-total";

	$calculator_atts['value'] = 0;

	if(!empty($tag->get_option( 'calculationcf7_Prefix' )[0])){
		$calculator_atts['prefix'] = "";
	}

	if(!empty($tag->get_option( 'calculationcf7_Precision' )[0])){
		$calculator_atts['precision'] = $tag->get_option( 'calculationcf7_Precision' )[0];
	}

	$calculator_atts = wpcf7_format_atts( $calculator_atts );

	
	$calculator_html = sprintf(
	'<span class="wpcf7-form-control-wrap %1$s"><input %2$s %4$s />%3$s</span>',
	sanitize_html_class( $tag->name ), $calculator_atts, $calculator_validation_error, 'data-formulas="'.$calculator_value.'"' );
	return $calculator_html;
}

