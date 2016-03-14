<?php
/**
 * Select field class.
 */
class Video_Central_Metaboxes_Select_Field extends Video_Central_Metaboxes_Field
{
	/**
	 * Enqueue scripts and styles
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'video-central-metaboxes-select', Video_Central_Metaboxes_CSS_URL . 'select.css', array(), Video_Central_Metaboxes_VER );
		wp_enqueue_script( 'video-central-metaboxes-select', Video_Central_Metaboxes_JS_URL . 'select.js', array(), Video_Central_Metaboxes_VER, true );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$attributes  = call_user_func( array( Video_Central_Metabox::get_class_name( $field ), 'get_attributes' ), $field, $meta );
		$html = sprintf(
			'<select %s>',
			self::render_attributes( $attributes )
		);

		$html .= $field['placeholder'] ? "<option value=''>{$field['placeholder']}</option>" : '<option></option>';

		$html .= self::options_html( $field, $meta );

		$html .= '</select>';

		$html .= self::get_select_all_html( $field['multiple'] );

		return $html;
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'size' => $field['multiple'] ? 5 : 0,
		) );

		$field['field_name'] .= ! $field['clone'] && $field['multiple'] ? '[]' : '';
		return $field;
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, array(
			'multiple' => $field['multiple'],
			'size'     => $field['size'],
		) );

		return $attributes;
	}

	/**
	 * Creates html for options
	 *
	 * @param array $field
	 * @param mixed $meta
	 *
	 * @return array
	 */
	static function options_html( $field, $meta )
	{
		$html = '';

		$option = '<option value="%s"%s>%s</option>';

		foreach ( $field['options'] as $value => $label )
		{
			$html .= sprintf(
				$option,
				$value,
				selected( in_array( (string) $value, (array) $meta, true ), true, false ),
				$label
			);
		}

		return $html;
	}

	/**
	 * Output the field value
	 * Display unordered list of option labels, not option values
	 *
	 * @param  array    $field   Field parameters
	 * @param  array    $args    Additional arguments. Not used for these fields.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string Link(s) to post
	 */
	static function the_value( $field, $args = array(), $post_id = null )
	{
		$value = self::get_value( $field, $args, $post_id );
		if ( ! $value )
			return '';

		$function = array( Video_Central_Metabox::get_class_name( $field ), 'get_option_label' );

		if ( $field['clone'] )
		{
			$output = '<ul>';
			if ( $field['multiple'] )
			{
				foreach ( $value as $subvalue )
				{
					$output .= '<li>';
					array_walk_recursive( $subvalue, $function, $field );
					$output .= '<ul><li>' . implode( '</li><li>', $subvalue ) . '</li></ul>';
					$output .= '</li>';
				}
			}
			else
			{
				array_walk_recursive( $value, $function, $field );
				$output = '<li>' . implode( '</li><li>', $value ) . '</li>';
			}
			$output .= '</ul>';
		}
		else
		{
			if ( $field['multiple'] )
			{
				array_walk_recursive( $value, $function, $field );
				$output = '<ul><li>' . implode( '</li><li>', $value ) . '</li></ul>';
			}
			else
			{
				call_user_func_array( $function, array( &$value, 0, $field ) );
				$output = $value;
			}
		}

		return $output;
	}

	/**
	 * Get option label to display in the frontend
	 *
	 * @param int   $value Option value
	 * @param int   $index Array index
	 * @param array $field Field parameter
	 *
	 * @return string
	 */
	static function get_option_label( &$value, $index, $field )
	{
		$value = $field['options'][$value];
	}

	/**
	 * Get html for select all|none for multiple select
	 *
	 * @param $multiple
	 *
	 * @return string
	 */
	static function get_select_all_html( $multiple )
	{
		if ( $multiple === true )
		{
			return '<div class="video-central-metaboxes-select-all-none">
					' . __( 'Select', 'meta-box' ) . ': <a data-type="all" href="#">' . __( 'All', 'meta-box' ) . '</a> | <a data-type="none" href="#">' . __( 'None', 'meta-box' ) . '</a>
				</div>';
		}
		return '';
	}
}
