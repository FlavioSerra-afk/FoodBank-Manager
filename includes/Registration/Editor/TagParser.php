<?php
/**
 * Registration form template tag parser.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration\Editor;

use function __;
use function array_intersect_key;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_values;
use function explode;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_split;
use function sprintf;
use function strlen;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function substr;
use function trim;
use function ucfirst;
use function uniqid;
use function array_fill_keys;

/**
 * Parses CF7-style tags into normalized field definitions.
 */
final class TagParser {
        private const SUPPORTED_TAGS = array(
                'text',
                'email',
                'tel',
                'date',
                'number',
                'textarea',
                'radio',
                'checkbox',
                'select',
                'file',
                'submit',
        );

        private const BOOLEAN_KEYWORDS = array(
                'multiple',
                'use_label_element',
        );

        private const ATTRIBUTE_KEYWORDS = array(
                'placeholder',
                'autocomplete',
        );

        private const RANGE_ATTRIBUTES = array(
                'min',
                'max',
                'step',
        );

        /**
         * Parse the provided template into fragments and field definitions.
         *
         * @param string $template Template markup.
         *
         * @return array{fragments:array<int,array<string,mixed>>,fields:array<string,array<string,mixed>>,warnings:array<int,string>}
         */
        public function parse( string $template ): array {
                $fragments = array();
                $fields    = array();
                $warnings  = array();

                if ( '' === $template ) {
                        return array(
                                'fragments' => array(
                                        array(
                                                'type'    => 'html',
                                                'content' => '',
                                        ),
                                ),
                                'fields'   => array(),
                                'warnings' => array(),
                        );
                }

                $pattern = '/\[([^\[\]]+)\]/';

                $matches = array();
                preg_match_all( $pattern, $template, $matches, PREG_OFFSET_CAPTURE );

                $offset = 0;

                foreach ( $matches[0] as $index => $full_match ) {
                        if ( ! is_array( $full_match ) ) {
                                continue;
                        }

                        $raw   = $full_match[0];
                        $start = (int) $full_match[1];
                        $length = strlen( $raw );

                        if ( $start > $offset ) {
                                $fragments[] = array(
                                        'type'    => 'html',
                                        'content' => substr( $template, $offset, $start - $offset ),
                                );
                        }

                        $offset = $start + $length;

                        $tag_content = $matches[1][ $index ][0] ?? '';
                        $tag         = $this->parse_tag( $tag_content, $raw );

                        if ( null === $tag ) {
                                $warnings[] = sprintf(
                                        /* translators: %s: unsupported tag markup. */
                                        __( 'Unsupported tag detected: %s', 'foodbank-manager' ),
                                        $raw
                                );

                                $fragments[] = array(
                                        'type'    => 'html',
                                        'content' => '',
                                );

                                continue;
                        }

                        if ( '' !== $tag['warning'] ) {
                                $warnings[] = $tag['warning'];
                        }

                        $fragments[] = array(
                                'type'  => 'field',
                                'field' => $tag['field'],
                        );

                        $name = $tag['field']['name'];

                        if ( ! array_key_exists( $name, $fields ) ) {
                                $fields[ $name ] = $tag['field'];
                        }
                }

                if ( $offset < strlen( $template ) ) {
                        $fragments[] = array(
                                'type'    => 'html',
                                'content' => substr( $template, $offset ),
                        );
                }

                if ( empty( $fragments ) ) {
                        $fragments[] = array(
                                'type'    => 'html',
                                'content' => $template,
                        );
                }

                return array(
                        'fragments' => $fragments,
                        'fields'    => $fields,
                        'warnings'  => $warnings,
                );
        }

        /**
         * Parse an individual tag into a field definition.
         *
         * @param string $tag_content Inner tag markup without brackets.
         * @param string $raw         Raw tag including brackets.
         *
         * @return array{field:array<string,mixed>,warning:string}|null
         */
        private function parse_tag( string $tag_content, string $raw ): ?array {
                $tag_content = trim( $tag_content );

                if ( '' === $tag_content ) {
                        return null;
                }

                if ( ! preg_match( '/^(?<type>[a-z]+)(?<required>\*)?(?:\s+(?<params>.*))?$/i', $tag_content, $parts ) ) {
                        return null;
                }

                $type = strtolower( (string) $parts['type'] );

                if ( ! in_array( $type, self::SUPPORTED_TAGS, true ) ) {
                        return null;
                }

                $required_marker = isset( $parts['required'] ) && '*' === $parts['required'];
                $params          = isset( $parts['params'] ) ? trim( (string) $parts['params'] ) : '';
                $tokens          = $this->tokenize( $params );

                $definition = array(
                        'type'              => $type,
                        'name'              => '',
                        'required'          => $required_marker,
                        'id'                => '',
                        'classes'           => array(),
                        'placeholder'       => '',
                        'autocomplete'      => '',
                        'range'             => array(),
                        'multiple'          => false,
                        'use_label_element' => false,
                        'label'             => '',
                        'options'           => array(),
                        'raw'               => $raw,
                );

                $warning = '';

                foreach ( $tokens as $index => $token ) {
                        if ( '' === $token ) {
                                continue;
                        }

                        // First positional token becomes the field name when not quoted/keyword.
                        if ( '' === $definition['name'] && $this->is_candidate_name( $token ) ) {
                                $definition['name'] = $this->normalize_name( $token );
                                continue;
                        }

                        if ( $this->is_boolean_keyword( $token ) ) {
                                $definition[ $token ] = true;
                                continue;
                        }

                        if ( $this->is_range_attribute( $token ) ) {
                                $this->assign_range_attribute( $definition, $token );
                                continue;
                        }

                        if ( $this->is_attribute_keyword( $token ) ) {
                                $next = $tokens[ $index + 1 ] ?? '';

                                if ( '' !== $next && ! $this->is_keyword( $next ) ) {
                                        $definition[ $token ] = $this->strip_quotes( $next );
                                }

                                continue;
                        }

                        if ( str_starts_with( $token, 'id:' ) ) {
                                $definition['id'] = $this->normalize_name( substr( $token, 3 ) );
                                continue;
                        }

                        if ( str_starts_with( $token, 'class:' ) ) {
                                $definition['classes'][] = $this->normalize_class( substr( $token, 6 ) );
                                continue;
                        }

                        if ( $this->is_quoted( $token ) ) {
                                if ( 'submit' === $type && '' === $definition['label'] ) {
                                        $definition['label'] = $this->strip_quotes( $token );
                                } else {
                                        $definition['options'][] = $this->parse_option( $token );
                                }

                                continue;
                        }

                        // Remaining tokens for grouped fields count as options.
                        if ( in_array( $type, array( 'radio', 'checkbox', 'select' ), true ) ) {
                                $definition['options'][] = $this->parse_option( $token );
                                continue;
                        }

                        if ( '' === $definition['label'] ) {
                                $definition['label'] = $this->strip_quotes( $token );
                                continue;
                        }

                        $warning = sprintf(
                                /* translators: %s: unexpected token string. */
                                __( 'Unrecognised token "%s" ignored.', 'foodbank-manager' ),
                                $token
                        );
                }

                if ( '' === $definition['name'] && 'submit' !== $type ) {
                        $definition['name'] = $this->generate_fallback_name( $type );
                }

                if ( '' === $definition['label'] ) {
                        $definition['label'] = $this->default_label( $definition['name'], $definition['type'] );
                }

                if ( empty( $definition['classes'] ) ) {
                        $definition['classes'] = array();
                }

                if ( 'select' !== $type && ! empty( $definition['range'] ) ) {
                        $definition['range'] = array_intersect_key(
                                $definition['range'],
                                array_fill_keys( array( 'min', 'max', 'step' ), true )
                        );
                }

                return array(
                        'field'   => $definition,
                        'warning' => $warning,
                );
        }

        /**
         * Tokenize a tag parameter string.
         *
         * @param string $params Raw parameter string.
         *
         * @return array<int,string>
         */
        private function tokenize( string $params ): array {
                if ( '' === $params ) {
                        return array();
                }

                $tokens = preg_split( '/\s+(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/', trim( $params ) ) ?: array();

                return array_values( array_map( 'trim', $tokens ) );
        }

        /**
         * Determine whether the provided token may represent a field name.
         *
         * @param string $token Candidate token.
         */
        private function is_candidate_name( string $token ): bool {
                if ( '' === $token ) {
                        return false;
                }

                if ( $this->is_keyword( $token ) ) {
                        return false;
                }

                if ( $this->is_quoted( $token ) ) {
                        return false;
                }

                if ( str_contains( $token, ':' ) ) {
                        return false;
                }

                return true;
        }

        /**
         * Normalize field names to safe tokens.
         *
         * @param string $token Raw token.
         */
        private function normalize_name( string $token ): string {
                $token = strtolower( trim( $token ) );
                $token = preg_replace( '/[^a-z0-9_\-]+/', '-', $token ) ?? '';

                if ( '' === $token ) {
                        return 'fbm-field-' . uniqid( '', false );
                }

                return $token;
        }

        /**
         * Normalize class names.
         *
         * @param string $token Raw class token.
         */
        private function normalize_class( string $token ): string {
                $token = strtolower( trim( $token ) );
                $token = preg_replace( '/[^a-z0-9_\-]+/', '-', $token ) ?? '';

                return $token;
        }

        /**
         * Determine whether the token represents a boolean keyword.
         *
         * @param string $token Candidate token.
         */
        private function is_boolean_keyword( string $token ): bool {
                return in_array( strtolower( $token ), self::BOOLEAN_KEYWORDS, true );
        }

        /**
         * Determine whether the token is an attribute keyword requiring a value.
         *
         * @param string $token Candidate token.
         */
        private function is_attribute_keyword( string $token ): bool {
                return in_array( strtolower( $token ), self::ATTRIBUTE_KEYWORDS, true );
        }

        /**
         * Determine whether the token corresponds to a range attribute.
         *
         * @param string $token Candidate token.
         */
        private function is_range_attribute( string $token ): bool {
                foreach ( self::RANGE_ATTRIBUTES as $attribute ) {
                        if ( str_starts_with( $token, $attribute . ':' ) ) {
                                return true;
                        }
                }

                return false;
        }

        /**
         * Assign a range attribute to the definition.
         *
         * @param array<string,mixed> $definition Field definition.
         * @param string              $token      Raw range token.
         */
        private function assign_range_attribute( array &$definition, string $token ): void {
                foreach ( self::RANGE_ATTRIBUTES as $attribute ) {
                        if ( str_starts_with( $token, $attribute . ':' ) ) {
                                $value = substr( $token, strlen( $attribute ) + 1 );

                                if ( '' === $value ) {
                                        return;
                                }

                                $definition['range'][ $attribute ] = $value;
                        }
                }
        }

        /**
         * Determine whether the provided token is quoted.
         *
         * @param string $token Candidate token.
         */
        private function is_quoted( string $token ): bool {
                return strlen( $token ) >= 2 && ( ( '"' === $token[0] && '"' === $token[ strlen( $token ) - 1 ] ) || ( "'" === $token[0] && "'" === $token[ strlen( $token ) - 1 ] ) );
        }

        /**
         * Strip surrounding quotes from a token.
         *
         * @param string $token Quoted token.
         */
        private function strip_quotes( string $token ): string {
                if ( ! $this->is_quoted( $token ) ) {
                        return $token;
                }

                return substr( $token, 1, -1 );
        }

        /**
         * Parse an option token into value and label pairs.
         *
         * @param string $token Raw token.
         *
         * @return array{value:string,label:string}
         */
        private function parse_option( string $token ): array {
                $value = $this->strip_quotes( $token );

                if ( str_contains( $value, '|' ) ) {
                        $segments = explode( '|', $value, 2 );

                        $option_value = $this->sanitize_option_value( $segments[0] );
                        $label        = trim( $segments[1] );

                        return array(
                                'value' => $option_value,
                                'label' => '' !== $label ? $label : $option_value,
                        );
                }

                $option_value = $this->sanitize_option_value( $value );

                return array(
                        'value' => $option_value,
                        'label' => $value,
                );
        }

        /**
         * Generate a fallback field name when none supplied.
         *
         * @param string $type Field type.
         */
        private function generate_fallback_name( string $type ): string {
                return 'fbm-' . $type . '-' . uniqid( '', false );
        }

        /**
         * Compute a default label when not supplied.
         *
         * @param string $name Field name.
         * @param string $type Field type.
         */
        private function default_label( string $name, string $type ): string {
                if ( 'submit' === $type ) {
                        return __( 'Submit', 'foodbank-manager' );
                }

                $name = preg_replace( '/[-_]+/', ' ', $name ) ?? $name;
                $name = trim( $name );

                if ( '' === $name ) {
                        return __( 'Field', 'foodbank-manager' );
                }

                return ucfirst( $name );
        }

        /**
         * Determine whether a token is a reserved keyword.
         *
         * @param string $token Candidate token.
         */
        private function is_keyword( string $token ): bool {
                $token = strtolower( $token );

                if ( in_array( $token, self::BOOLEAN_KEYWORDS, true ) ) {
                        return true;
                }

                if ( in_array( $token, self::ATTRIBUTE_KEYWORDS, true ) ) {
                        return true;
                }

                foreach ( self::RANGE_ATTRIBUTES as $attribute ) {
                        if ( str_starts_with( $token, $attribute . ':' ) ) {
                                return true;
                        }
                }

                if ( str_starts_with( $token, 'id:' ) || str_starts_with( $token, 'class:' ) ) {
                        return true;
                }

                return false;
        }

        /**
         * Normalize option values.
         *
         * @param string $value Raw option value.
         */
        private function sanitize_option_value( string $value ): string {
                $value = strtolower( trim( $value ) );
                $value = preg_replace( '/[^a-z0-9_\-]+/', '-', $value ) ?? '';

                if ( '' !== $value ) {
                        return $value;
                }

                return (string) uniqid( 'option-', false );
        }
}
