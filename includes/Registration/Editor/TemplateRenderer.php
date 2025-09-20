<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Registration template renderer.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration\Editor;

use function esc_attr;
use function esc_html;
use function esc_html__;
use function array_merge;
use function in_array;
use function is_array;
use function is_string;
use function implode;
use function preg_replace;
use function trim;
use function wp_kses;

/**
 * Renders sanitized templates into HTML form markup.
 */
final class TemplateRenderer {
		/**
		 * Tag parser dependency.
		 *
		 * @var TagParser
		 */
	private TagParser $parser;

		/**
		 * Constructor.
		 *
		 * @param TagParser|null $parser Optional parser dependency for testing.
		 */
	public function __construct( ?TagParser $parser = null ) {
			$this->parser = $parser ?? new TagParser();
	}

		/**
		 * Sanitize template markup using a strict allow-list.
		 *
		 * @param string $template Template markup.
		 */
	public static function sanitize_template( string $template ): string {
			return wp_kses( $template, self::allowed_html() );
	}

		/**
		 * Render template markup into HTML and field definitions.
		 *
		 * @param string                          $template Template markup.
		 * @param array<string,mixed>             $values   Field values keyed by name.
		 * @param array<string,array<int,string>> $errors   Field errors keyed by name.
		 *
		 * @return array{html:string,fields:array<string,array<string,mixed>>,warnings:array<int,string>}
		 */
	public function render( string $template, array $values = array(), array $errors = array() ): array {
			$sanitized = self::sanitize_template( $template );
			$parsed    = $this->parser->parse( $sanitized );

			$html = '';

		foreach ( $parsed['fragments'] as $fragment ) {
			if ( ! is_array( $fragment ) ) {
				continue;
			}

			if ( 'html' === ( $fragment['type'] ?? '' ) ) {
					$html .= (string) ( $fragment['content'] ?? '' );
					continue;
			}

			if ( 'field' !== ( $fragment['type'] ?? '' ) ) {
					continue;
			}

				$field = $fragment['field'] ?? array();
			if ( ! is_array( $field ) ) {
					continue;
			}

				$name         = isset( $field['name'] ) ? (string) $field['name'] : '';
				$field_markup = $this->render_field(
					$field,
					$values[ $name ] ?? null,
					$errors[ $name ] ?? array()
				);

				$html .= $this->wrap_field( $field, $field_markup );
		}

			return array(
				'html'     => $html,
				'fields'   => $parsed['fields'],
				'warnings' => $parsed['warnings'],
			);
	}

		/**
		 * Allowed HTML structure for stored templates.
		 *
		 * @return array<string,array<string,bool>>
		 */
	public static function allowed_html(): array {
			$common_attributes = array(
				'class'  => true,
				'id'     => true,
				'role'   => true,
				'title'  => true,
				'aria-*' => true,
				'data-*' => true,
			);

			return array(
				'div'      => $common_attributes,
				'section'  => $common_attributes,
				'article'  => $common_attributes,
				'p'        => $common_attributes,
				'span'     => $common_attributes,
				'strong'   => $common_attributes,
				'em'       => $common_attributes,
				'ul'       => $common_attributes,
				'ol'       => $common_attributes,
				'li'       => $common_attributes,
				'fieldset' => $common_attributes,
				'legend'   => $common_attributes,
				'label'    => array_merge(
					$common_attributes,
					array(
						'for' => true,
					)
				),
				'a'        => array_merge(
					$common_attributes,
					array(
						'href'   => true,
						'target' => true,
						'rel'    => true,
					)
				),
				'h1'       => $common_attributes,
				'h2'       => $common_attributes,
				'h3'       => $common_attributes,
				'h4'       => $common_attributes,
				'h5'       => $common_attributes,
				'h6'       => $common_attributes,
				'small'    => $common_attributes,
				'sup'      => $common_attributes,
				'sub'      => $common_attributes,
				'br'       => array(),
				'hr'       => $common_attributes,
				'dl'       => $common_attributes,
				'dt'       => $common_attributes,
				'dd'       => $common_attributes,
			);
	}

				/**
				 * Wrap rendered markup with a container for client-side hooks.
				 *
				 * @param array<string,mixed> $definition Field definition.
				 * @param string              $markup     Rendered markup.
				 */
	private function wrap_field( array $definition, string $markup ): string {
					$name = isset( $definition['name'] ) ? (string) $definition['name'] : '';
					$type = isset( $definition['type'] ) ? (string) $definition['type'] : 'text';

		if ( '' === $name ) {
						return $markup;
		}

										$required_flag = ! empty( $definition['required'] ) ? '1' : '0';
										$id            = 'fbm-field-' . $name;

										return '<div class="fbm-field" id="' . esc_attr( $id ) . '" data-fbm-field="' . esc_attr( $name ) . '" data-fbm-field-type="' . esc_attr( $type ) . '" data-fbm-field-required="' . esc_attr( $required_flag ) . '">' . $markup . '</div>';
	}

				/**
				 * Render an individual field definition.
				 *
				 * @param array<string,mixed>            $field   Field definition.
				 * @param mixed                          $value   Submitted value.
				 * @param array<int,string>|string|mixed $errors  Field errors.
				 */
	private function render_field( array $field, $value, $errors ): string { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh -- Rendering covers many field types.
			$type         = isset( $field['type'] ) ? (string) $field['type'] : 'text';
			$name         = isset( $field['name'] ) ? (string) $field['name'] : '';
			$label        = isset( $field['label'] ) ? (string) $field['label'] : '';
			$id           = isset( $field['id'] ) ? (string) $field['id'] : '';
			$classes      = isset( $field['classes'] ) && is_array( $field['classes'] ) ? $field['classes'] : array();
			$required     = ! empty( $field['required'] );
			$multiple     = ! empty( $field['multiple'] );
			$wrap_label   = ! empty( $field['use_label_element'] );
			$placeholder  = isset( $field['placeholder'] ) ? (string) $field['placeholder'] : '';
			$autocomplete = isset( $field['autocomplete'] ) ? (string) $field['autocomplete'] : '';
			$range        = isset( $field['range'] ) && is_array( $field['range'] ) ? $field['range'] : array();
			$options      = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();

			$input_classes = array_merge( array( 'fbm-field-control', 'fbm-field-control--' . $type ), $classes );

		if ( '' === $id ) {
				$id = $this->generate_id_from_name( $name );
		}

			$error_messages = array();
		if ( is_array( $errors ) ) {
			foreach ( $errors as $error ) {
				if ( is_string( $error ) && '' !== $error ) {
					$error_messages[] = $error;
				}
			}
		} elseif ( is_string( $errors ) && '' !== $errors ) {
				$error_messages[] = $errors;
		}

			$error_html = '';
		if ( ! empty( $error_messages ) ) {
				$error_html = '<span class="fbm-field-error" role="alert">' . esc_html( implode( ' ', $error_messages ) ) . '</span>';
		}

		switch ( $type ) {
			case 'textarea':
				return $this->render_textarea( $name, $id, $label, $value, $input_classes, $required, $wrap_label, $placeholder, $autocomplete ) . $error_html;
			case 'radio':
				return $this->render_choice_group( 'radio', $name, $id, $label, $options, $value, $input_classes, $required ) . $error_html;
			case 'checkbox':
				return $this->render_checkbox_group( $name, $id, $label, $options, $value, $input_classes, $required ) . $error_html;
			case 'select':
				return $this->render_select( $name, $id, $label, $options, $value, $input_classes, $required, $multiple, $wrap_label ) . $error_html;
			case 'file':
				return $this->render_file_input( $name, $id, $label, $input_classes, $required, $wrap_label, $autocomplete ) . $error_html;
			case 'submit':
				return $this->render_submit( $name, $label, $input_classes );
			case 'date':
			case 'number':
			case 'email':
			case 'tel':
			case 'text':
			default:
				return $this->render_input( $type, $name, $id, $label, $value, $input_classes, $required, $wrap_label, $placeholder, $autocomplete, $range ) . $error_html;
		}
	}

		/**
		 * Render a generic input control.
		 *
		 * @param string              $type         Input type attribute.
		 * @param string              $name         Field name.
		 * @param string              $id           Field identifier.
		 * @param string              $label        Field label.
		 * @param mixed               $value        Submitted value.
		 * @param array<int,string>   $classes      CSS classes.
		 * @param bool                $required     Whether the field is required.
		 * @param bool                $wrap_label   Wrap control within label.
		 * @param string              $placeholder  Placeholder text.
		 * @param string              $autocomplete Autocomplete hint.
		 * @param array<string,mixed> $range        Range attributes (min/max/step).
		 */
	private function render_input( string $type, string $name, string $id, string $label, $value, array $classes, bool $required, bool $wrap_label, string $placeholder, string $autocomplete, array $range ): string {
			$value = is_string( $value ) ? $value : '';

			$attributes  = $this->build_common_attributes( $name, $id, $classes, $required );
			$attributes .= ' type="' . esc_attr( $type ) . '"';

		if ( '' !== $value ) {
				$attributes .= ' value="' . esc_attr( $value ) . '"';
		}

		if ( '' !== $placeholder ) {
				$attributes .= ' placeholder="' . esc_attr( $placeholder ) . '"';
		}

		if ( '' !== $autocomplete ) {
				$attributes .= ' autocomplete="' . esc_attr( $autocomplete ) . '"';
		}

		foreach ( array( 'min', 'max', 'step' ) as $attribute ) {
			if ( isset( $range[ $attribute ] ) && '' !== $range[ $attribute ] ) {
					$attributes .= ' ' . $attribute . '="' . esc_attr( (string) $range[ $attribute ] ) . '"';
			}
		}

			$input = '<input' . $attributes . ' />';

		if ( $wrap_label ) {
				return '<label class="fbm-field-label">' . esc_html( $label ) . ' ' . $input . '</label>';
		}

			$label_html = '<label class="fbm-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';

			return $label_html . $input;
	}

		/**
		 * Render a textarea control.
		 *
		 * @param string            $name         Field name.
		 * @param string            $id           Field identifier.
		 * @param string            $label        Field label.
		 * @param mixed             $value        Submitted value.
		 * @param array<int,string> $classes      CSS classes.
		 * @param bool              $required     Required state.
		 * @param bool              $wrap_label   Wrap textarea in label.
		 * @param string            $placeholder  Placeholder text.
		 * @param string            $autocomplete Autocomplete attribute.
		 */
	private function render_textarea( string $name, string $id, string $label, $value, array $classes, bool $required, bool $wrap_label, string $placeholder, string $autocomplete ): string {
			$value = is_string( $value ) ? $value : '';

			$attributes = $this->build_common_attributes( $name, $id, $classes, $required );

		if ( '' !== $placeholder ) {
				$attributes .= ' placeholder="' . esc_attr( $placeholder ) . '"';
		}

		if ( '' !== $autocomplete ) {
				$attributes .= ' autocomplete="' . esc_attr( $autocomplete ) . '"';
		}

			$textarea = '<textarea' . $attributes . '>' . esc_html( $value ) . '</textarea>';

		if ( $wrap_label ) {
				return '<label class="fbm-field-label">' . esc_html( $label ) . ' ' . $textarea . '</label>';
		}

			$label_html = '<label class="fbm-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';

			return $label_html . $textarea;
	}

		/**
		 * Render a select element.
		 *
		 * @param string            $name       Field name.
		 * @param string            $id         Field identifier.
		 * @param string            $label      Field label.
		 * @param array<int,array>  $options    Field options.
		 * @param mixed             $value      Submitted value(s).
		 * @param array<int,string> $classes    CSS classes.
		 * @param bool              $required   Required state.
		 * @param bool              $multiple   Multiple selection.
		 * @param bool              $wrap_label Wrap select within label.
		 */
	private function render_select( string $name, string $id, string $label, array $options, $value, array $classes, bool $required, bool $multiple, bool $wrap_label ): string {
			$selected_values = array();

		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				if ( is_string( $item ) && '' !== $item ) {
						$selected_values[] = $item;
				}
			}
		} elseif ( is_string( $value ) && '' !== $value ) {
				$selected_values[] = $value;
		}

			$attributes = $this->build_common_attributes( $name . ( $multiple ? '[]' : '' ), $id, $classes, $required );

		if ( $multiple ) {
				$attributes .= ' multiple="multiple"';
		}

			$options_html = '';
		foreach ( $options as $index => $option ) {
			if ( ! is_array( $option ) ) {
					continue;
			}

				$option_value = isset( $option['value'] ) ? (string) $option['value'] : (string) $index;
				$option_label = isset( $option['label'] ) ? (string) $option['label'] : $option_value;
				$selected     = in_array( $option_value, $selected_values, true ) ? ' selected="selected"' : '';

				$options_html .= '<option value="' . esc_attr( $option_value ) . '"' . $selected . '>' . esc_html( $option_label ) . '</option>';
		}

			$select = '<select' . $attributes . '>' . $options_html . '</select>';

		if ( $wrap_label ) {
				return '<label class="fbm-field-label">' . esc_html( $label ) . ' ' . $select . '</label>';
		}

			$label_html = '<label class="fbm-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';

			return $label_html . $select;
	}

		/**
		 * Render a set of radio buttons.
		 *
		 * @param string            $type       radio|checkbox.
		 * @param string            $name       Field name.
		 * @param string            $id         Base identifier.
		 * @param string            $label      Legend label.
		 * @param array<int,array>  $options    Options array.
		 * @param mixed             $value      Submitted value(s).
		 * @param array<int,string> $classes    CSS classes.
		 * @param bool              $required   Required state.
		 */
	private function render_choice_group( string $type, string $name, string $id, string $label, array $options, $value, array $classes, bool $required ): string {
			$selected_values = array();

		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				if ( is_string( $item ) ) {
						$selected_values[] = $item;
				}
			}
		} elseif ( is_string( $value ) ) {
				$selected_values[] = $value;
		}

			$controls = '';
		foreach ( $options as $index => $option ) {
			if ( ! is_array( $option ) ) {
					continue;
			}

				$option_value = isset( $option['value'] ) ? (string) $option['value'] : (string) $index;
				$option_label = isset( $option['label'] ) ? (string) $option['label'] : $option_value;
				$control_id   = $id . '-' . (string) $index;

				$attributes  = $this->build_common_attributes( $name, $control_id, $classes, $required && 0 === $index );
				$attributes .= ' type="' . esc_attr( $type ) . '" value="' . esc_attr( $option_value ) . '"';

			if ( in_array( $option_value, $selected_values, true ) ) {
					$attributes .= ' checked="checked"';
			}

				$controls .= '<label class="fbm-field-option"><input' . $attributes . ' /> ' . esc_html( $option_label ) . '</label>';
		}

			return '<fieldset class="fbm-fieldset"><legend class="fbm-field-legend">' . esc_html( $label ) . '</legend>' . $controls . '</fieldset>';
	}

		/**
		 * Render checkbox group with support for single option.
		 *
		 * @param string            $name     Field name.
		 * @param string            $id       Base identifier.
		 * @param string            $label    Group label.
		 * @param array<int,array>  $options  Options array.
		 * @param mixed             $value    Submitted value(s).
		 * @param array<int,string> $classes  CSS classes.
		 * @param bool              $required Required state.
		 */
	private function render_checkbox_group( string $name, string $id, string $label, array $options, $value, array $classes, bool $required ): string {
		if ( empty( $options ) ) {
				$options = array(
					array(
						'value' => '1',
						'label' => $label,
					),
				);
		}

			$selected_values = array();

		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				if ( is_string( $item ) ) {
					$selected_values[] = $item;
				}
			}
		} elseif ( is_string( $value ) && '' !== $value ) {
				$selected_values[] = $value;
		}

		if ( 1 === count( $options ) ) {
				$option       = $options[0];
				$option_value = isset( $option['value'] ) ? (string) $option['value'] : '1';
				$option_label = isset( $option['label'] ) ? (string) $option['label'] : $label;
				$control_id   = $id . '-single';

				$attributes  = $this->build_common_attributes( $name, $control_id, $classes, $required );
				$attributes .= ' type="checkbox" value="' . esc_attr( $option_value ) . '"';

			if ( in_array( $option_value, $selected_values, true ) ) {
					$attributes .= ' checked="checked"';
			}

				return '<label class="fbm-field-option"><input' . $attributes . ' /> ' . esc_html( $option_label ) . '</label>';
		}

			$controls = '';
		foreach ( $options as $index => $option ) {
			if ( ! is_array( $option ) ) {
					continue;
			}

				$option_value = isset( $option['value'] ) ? (string) $option['value'] : (string) $index;
				$option_label = isset( $option['label'] ) ? (string) $option['label'] : $option_value;
				$control_id   = $id . '-' . (string) $index;

				$attributes  = $this->build_common_attributes( $name . '[]', $control_id, $classes, $required && 0 === $index );
				$attributes .= ' type="checkbox" value="' . esc_attr( $option_value ) . '"';

			if ( in_array( $option_value, $selected_values, true ) ) {
					$attributes .= ' checked="checked"';
			}

				$controls .= '<label class="fbm-field-option"><input' . $attributes . ' /> ' . esc_html( $option_label ) . '</label>';
		}

			return '<fieldset class="fbm-fieldset"><legend class="fbm-field-legend">' . esc_html( $label ) . '</legend>' . $controls . '</fieldset>';
	}

		/**
		 * Render a file input control.
		 *
		 * @param string            $name         Field name.
		 * @param string            $id           Field identifier.
		 * @param string            $label        Field label.
		 * @param array<int,string> $classes      CSS classes.
		 * @param bool              $required     Required flag.
		 * @param bool              $wrap_label   Wrap control in label.
		 * @param string            $autocomplete Autocomplete attribute.
		 */
	private function render_file_input( string $name, string $id, string $label, array $classes, bool $required, bool $wrap_label, string $autocomplete ): string {
			$attributes  = $this->build_common_attributes( $name, $id, $classes, $required );
			$attributes .= ' type="file"';

		if ( '' !== $autocomplete ) {
				$attributes .= ' autocomplete="' . esc_attr( $autocomplete ) . '"';
		}

			$input = '<input' . $attributes . ' />';

		if ( $wrap_label ) {
				return '<label class="fbm-field-label">' . esc_html( $label ) . ' ' . $input . '</label>';
		}

			$label_html = '<label class="fbm-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';

			return $label_html . $input;
	}

		/**
		 * Render a submit button.
		 *
		 * @param string            $name    Field name.
		 * @param string            $label   Button label.
		 * @param array<int,string> $classes CSS classes.
		 */
	private function render_submit( string $name, string $label, array $classes ): string {
		if ( '' === $label ) {
				$label = esc_html__( 'Submit', 'foodbank-manager' );
		}

			$class_attr = ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
			$name_attr  = '' !== $name ? ' name="' . esc_attr( $name ) . '"' : '';

			return '<button type="submit"' . $class_attr . $name_attr . '>' . esc_html( $label ) . '</button>';
	}

		/**
		 * Build common HTML attributes for controls.
		 *
		 * @param string            $name     Field name.
		 * @param string            $id       Identifier.
		 * @param array<int,string> $classes  CSS classes.
		 * @param bool              $required Required flag.
		 */
	private function build_common_attributes( string $name, string $id, array $classes, bool $required ): string {
			$attributes = ' name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"';

		if ( $required ) {
				$attributes .= ' required aria-required="true"';
		}

			return $attributes;
	}

		/**
		 * Generate a stable identifier from a field name.
		 *
		 * @param string $name Field name.
		 */
	private function generate_id_from_name( string $name ): string {
			$base = trim( $name );

		if ( '' === $base ) {
				$base = 'fbm-field';
		}

			$base = preg_replace( '/[^a-z0-9_\-]+/i', '-', $base ) ?? 'fbm-field';

			return $base . '-input';
	}
}

// phpcs:enable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
