<?php
/**
 * URL parameter trait.
 *
 * Provides reusable utilities for reading, sanitizing, and generating links
 * with URL query parameters during a WordPress request.
 *
 * Classes using this trait must define a URL_PARAMETERS class constant that
 * maps internal parameter keys to their corresponding URL query parameter
 * names.
 */

namespace DealerluxUtils\Traits;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Provides reusable URL query parameter utilities.
 */
trait Url_Parameter {

	/**
	 * URL query parameters supported by the class.
	 *
	 * The class using this trait must define this constant.
	 *
	 * @var array<string, string>
	 */
	// private const URL_PARAMETERS = array();

	/**
	 * Get a sanitized URL query parameter.
	 *
	 * @param string $key Parameter key defined in URL_PARAMETERS.
	 * @return string Sanitized parameter value, or an empty string.
	 */
	private function get_url_parameter( $key ) {
		if (
			! defined( 'self::URL_PARAMETERS' ) ||
			! isset( self::URL_PARAMETERS[ $key ] )
		) {
			return '';
		}
        
		$parameter = self::URL_PARAMETERS[ $key ];
        
		if ( ! isset( $_GET[ $parameter ] ) ) {
            return '';
        }

		return sanitize_text_field(
			wp_unslash( $_GET[ $parameter ] )
		);
	}

	/**
	 * Get the current page URL.
	 *
	 * @return string Current page URL.
	 */
	private function get_current_page_url() {
		$request_uri = isset( $_SERVER['REQUEST_URI'] )
			? wp_unslash( $_SERVER['REQUEST_URI'] )
			: '/';

		return home_url( $request_uri );
	}

	/**
	 * Add or replace a query parameter on the current page URL.
	 *
	 * @param string $key   Parameter key defined in URL_PARAMETERS.
	 * @param string $value Parameter value.
	 * @return string URL containing the query parameter.
	 */
	private function build_url_with_parameter( $key, $value ) {
		if (
			! defined( 'self::URL_PARAMETERS' ) ||
			! isset( self::URL_PARAMETERS[ $key ] )
		) {
			return '';
		}

		return add_query_arg(
			array(
				self::URL_PARAMETERS[ $key ] => $value,
			),
			$this->get_current_page_url()
		);
	}

    /**
     * Build an anchor link using a configured URL parameter.
     *
     * @param array<string, mixed> $args {
     *     Link configuration.
     *
     *     @type string $key                 Parameter key defined in URL_PARAMETERS.
     *     @type string $value               Parameter value.
     *     @type string $label               Optional. Link label. Defaults to the value.
     *     @type string $before_html         Optional. Allowed HTML before the link label.
     *     @type string $after_html          Optional. Allowed HTML after the link label.
     *     @type array  $allowed_html        Optional. Complete allowed HTML configuration.
     *     @type array  $add_allowed_html    Optional. Tags and attributes to add.
     *     @type array  $remove_allowed_html Optional. Tags and attributes to remove.
     *     @type array  $attributes          Optional. Additional anchor attributes.
     * }
     *
     * @return string Escaped anchor element, or an empty string.
     */
    private function build_parameter_link( $args ) {
        $default_allowed_html = array(
            'span' => array(
                'class'       => true,
                'id'          => true,
                'title'       => true,
                'role'        => true,
                'aria-label'  => true,
                'aria-hidden' => true,
                'data-*'      => true,
            ),
            'i' => array(
                'class'       => true,
                'title'       => true,
                'role'        => true,
                'aria-label'  => true,
                'aria-hidden' => true,
                'data-*'      => true,
            ),
            'strong' => array(
                'class' => true,
            ),
            'em' => array(
                'class' => true,
            ),
            'small' => array(
                'class' => true,
            ),
            'br' => array(),
        );

        $args = wp_parse_args(
            $args,
            array(
                'key'                 => '',
                'value'               => '',
                'label'               => '',
                'before_html'         => '',
                'after_html'          => '',
                'allowed_html'        => $default_allowed_html,
                'add_allowed_html'    => array(),
                'remove_allowed_html' => array(),
                'attributes'          => array(),
            )
        );

        if (
            '' === $args['key'] ||
            '' === $args['value']
        ) {
            return '';
        }

        $url = $this->build_url_with_parameter(
            $args['key'],
            $args['value']
        );

        if ( '' === $url ) {
            return '';
        }

        $label = '' !== $args['label']
            ? $args['label']
            : $args['value'];

        $html_attributes = sprintf(
            ' href="%s"',
            esc_url( $url )
        );

        if (
            ! empty( $args['attributes'] ) &&
            is_array( $args['attributes'] )
        ) {
            foreach ( $args['attributes'] as $attribute => $attribute_value ) {
                if (
                    '' === $attribute_value ||
                    null === $attribute_value ||
                    false === $attribute_value
                ) {
                    continue;
                }

                if ( true === $attribute_value ) {
                    $html_attributes .= sprintf(
                        ' %s',
                        esc_attr( $attribute )
                    );

                    continue;
                }

                $html_attributes .= sprintf(
                    ' %1$s="%2$s"',
                    esc_attr( $attribute ),
                    esc_attr( $attribute_value )
                );
            }
        }

        $allowed_html = is_array( $args['allowed_html'] )
            ? $args['allowed_html']
            : $default_allowed_html;

        /*
        * Add new allowed tags or attributes.
        */
        if (
            ! empty( $args['add_allowed_html'] ) &&
            is_array( $args['add_allowed_html'] )
        ) {
            foreach ( $args['add_allowed_html'] as $tag => $tag_attributes ) {
                if ( ! is_array( $tag_attributes ) ) {
                    continue;
                }

                if ( ! isset( $allowed_html[ $tag ] ) ) {
                    $allowed_html[ $tag ] = array();
                }

                $allowed_html[ $tag ] = array_merge(
                    $allowed_html[ $tag ],
                    $tag_attributes
                );
            }
        }

        /*
        * Remove allowed tags or individual attributes.
        *
        * Use true to remove an entire tag:
        *
        * 'span' => true
        *
        * Use an array to remove specific attributes:
        *
        * 'span' => array(
        *     'id',
        *     'title',
        * )
        */
        if (
            ! empty( $args['remove_allowed_html'] ) &&
            is_array( $args['remove_allowed_html'] )
        ) {
            foreach ( $args['remove_allowed_html'] as $tag => $tag_attributes ) {
                if ( true === $tag_attributes ) {
                    unset( $allowed_html[ $tag ] );
                    continue;
                }

                if (
                    ! isset( $allowed_html[ $tag ] ) ||
                    ! is_array( $tag_attributes )
                ) {
                    continue;
                }

                foreach ( $tag_attributes as $attribute ) {
                    if ( ! is_string( $attribute ) ) {
                        continue;
                    }

                    unset( $allowed_html[ $tag ][ $attribute ] );
                }
            }
        }

        $before_html = '';

        if ( '' !== $args['before_html'] ) {
            $before_html = wp_kses(
                $args['before_html'],
                $allowed_html
            );
        }

        $after_html = '';

        if ( '' !== $args['after_html'] ) {
            $after_html = wp_kses(
                $args['after_html'],
                $allowed_html
            );
        }

        return sprintf(
            '<a%1$s>%2$s%3$s%4$s</a>',
            $html_attributes,
            $before_html,
            esc_html( $label ),
            $after_html
        );
    }
}