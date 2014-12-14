<?php

	$GLOBALS[ 'htmlVoidElements' ] = array(
		'area'    => 1,
		'base'    => 1,
		'br'      => 1,
		'col'     => 1,
		'command' => 1,
		'embed'   => 1,
		'hr'      => 1,
		'img'     => 1,
		'input'   => 1,
		'keygen'  => 1,
		'link'    => 1,
		'meta'    => 1,
		'param'   => 1,
		'source'  => 1,
		'track'   => 1,
		'wbr'     => 1,
	);
	/**
	 * @var array the preferred order of attributes in a tag. This mainly affects the order of the attributes
	 * that are rendered by [[renderTagAttributes()]].
	 */
	$GLOBALS[ 'htmlAttributeOrder' ] = array(
		'type',
		'id',
		'class',
		'name',
		'value',
		'href',
		'src',
		'action',
		'method',
		'selected',
		'checked',
		'readonly',
		'disabled',
		'multiple',
		'size',
		'maxlength',
		'width',
		'height',
		'rows',
		'cols',
		'alt',
		'title',
		'rel',
		'media',
	);


	/**
	 * Encodes special characters into HTML entities.
	 *
	 * @param string  $content       the content to be encoded
	 * @param boolean $double_encode whether to encode HTML entities in `$content`. If false,
	 *                               HTML entities in `$content` will not be further encoded.
	 *
	 * @return string the encoded content
	 * @see arrayHtmlDecode()
	 * @see http://www.php.net/manual/en/function.htmlspecialchars.php
	 */
	function htmlEncode( $content, $double_encode = true ) {
		defined( 'ENT_SUBSTITUTE' ) or define( 'ENT_SUBSTITUTE', 8 );

		return htmlspecialchars( $content, ENT_QUOTES | ENT_SUBSTITUTE, get_option( 'blog_charset', 'utf-8' ), $double_encode );
	}

	/**
	 * Decodes special HTML entities back to the corresponding characters.
	 * This is the opposite of [[encode()]].
	 *
	 * @param string $content the content to be decoded
	 *
	 * @return string the decoded content
	 * @see arrayHtmlEncode()
	 * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
	 */
	function htmlDecode( $content ) {
		return htmlspecialchars_decode( $content, ENT_QUOTES );
	}

	/**
	 * Generates a complete HTML tag.
	 *
	 * @param string $name    the tag name
	 * @param string $content the content to be enclosed between the start and end tags. It will not be HTML-encoded.
	 *                        If this is coming from end users, you should consider [[encode()]] it to prevent XSS
	 *                        attacks.
	 * @param array  $options the HTML tag attributes (HTML options) in terms of name-value pairs.
	 *                        These will be rendered as the attributes of the resulting tag. The values will be
	 *                        HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not
	 *                        be rendered.
	 *
	 * For example when using `['class' => 'my-class', 'target' => '_blank', 'value' => null]` it will result in the
	 * html attributes rendered like this: `class="my-class" target="_blank"`.
	 *
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated HTML tag
	 * @see htmlBeginTag()
	 * @see htmlEndTag()
	 */
	function htmlTag( $name, $content = '', $options = array() ) {
		global $htmlVoidElements;
		$html = "<$name" . htmlRenderTagAttributes( $options ) . '>';

		return isset( $htmlVoidElements[ strtolower( $name ) ] ) ? $html : "$html$content</$name>";
	}

	/**
	 * Generates a start tag.
	 *
	 * @param string $name    the tag name
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated start tag
	 * @see htmlEndTag()
	 * @see htmlTag()
	 */
	function htmlBeginTag( $name, $options = array() ) {
		return "<$name" . htmlRenderTagAttributes( $options ) . '>';
	}

	/**
	 * Generates an end tag.
	 *
	 * @param string $name the tag name
	 *
	 * @return string the generated end tag
	 * @see htmlBeginTag()
	 * @see htmlTag()
	 */
	function htmlEndTag( $name ) {
		return "</$name>";
	}

	/**
	 * Generates a style tag.
	 *
	 * @param string $content the style content
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        If the options does not contain "type", a "type" attribute with value "text/css" will be
	 *                        used. See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated style tag
	 */
	function htmlStyle( $content, $options = array() ) {
		return htmlTag( 'style', $content, $options );
	}

	/**
	 * Generates a script tag.
	 *
	 * @param string $content the script content
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        If the options does not contain "type", a "type" attribute with value "text/javascript"
	 *                        will be rendered. See [[renderTagAttributes()]] for details on how attributes are being
	 *                        rendered.
	 *
	 * @return string the generated script tag
	 */
	function htmlScript( $content, $options = array() ) {
		return htmlTag( 'script', $content, $options );
	}

	/**
	 * Generates a link tag that refers to an external CSS file.
	 *
	 * @param array|string $url     the URL of the external CSS file. This parameter will be processed by
	 *                              [[Url::to()]].
	 * @param array        $options the tag options in terms of name-value pairs. The following option is specially
	 *                              handled:
	 *
	 * - condition: specifies the conditional comments for IE, e.g., `lt IE 9`. When this is specified,
	 *   the generated `script` tag will be enclosed within the conditional comments. This is mainly useful
	 *   for supporting old versions of IE browsers.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting link tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated link tag
	 * @see Url::to()
	 */
	function htmlCssFile( $url, $options = array() ) {
		if ( ! isset( $options[ 'rel' ] ) ) {
			$options[ 'rel' ] = 'stylesheet';
		}
		$options[ 'href' ] = $url;

		if ( isset( $options[ 'condition' ] ) ) {
			$condition = $options[ 'condition' ];
			unset( $options[ 'condition' ] );

			return "<!--[if $condition]>\n" . htmlTag( 'link', '', $options ) . "\n<![endif]-->";
		} else {
			return htmlTag( 'link', '', $options );
		}
	}

	/**
	 * Generates a script tag that refers to an external JavaScript file.
	 *
	 * @param string $url     the URL of the external JavaScript file. This parameter will be processed by
	 *                        [[Url::to()]].
	 * @param array  $options the tag options in terms of name-value pairs. The following option is specially handled:
	 *
	 * - condition: specifies the conditional comments for IE, e.g., `lt IE 9`. When this is specified,
	 *   the generated `script` tag will be enclosed within the conditional comments. This is mainly useful
	 *   for supporting old versions of IE browsers.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting script tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated script tag
	 * @see Url::to()
	 */
	function htmlJsFile( $url, $options = array() ) {
		$options[ 'src' ] = $url;
		if ( isset( $options[ 'condition' ] ) ) {
			$condition = $options[ 'condition' ];
			unset( $options[ 'condition' ] );

			return "<!--[if $condition]>\n" . htmlTag( 'script', '', $options ) . "\n<![endif]-->";
		} else {
			return htmlTag( 'script', '', $options );
		}
	}

	/**
	 * Generates a form start tag.
	 *
	 * @param array|string $action  the form action URL. This parameter will be processed by [[Url::to()]].
	 * @param string       $method  the form submission method, such as "post", "get", "put", "delete"
	 *                              (case-insensitive). Since most browsers only support "post" and "get", if other
	 *                              methods are given, they will be simulated using "post", and a hidden input will be
	 *                              added which contains the actual method type.
	 * @param array        $options the tag options in terms of name-value pairs. These will be rendered as
	 *                              the attributes of the resulting tag. The values will be HTML-encoded using
	 *                              [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *                              See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated form start tag.
	 * @see htmlEndForm()
	 */
	function htmlBeginForm( $action = '', $method = 'post', $options = array() ) {
		$hidden_inputs = array();

		if ( ! strcasecmp( $method, 'get' ) && ( $pos = strpos( $action, '?' ) ) !== false ) {
			// query parameters in the action are ignored for GET method
			// we use hidden fields to add them back
			foreach ( explode( '&', substr( $action, $pos + 1 ) ) as $pair ) {
				if ( ( $pos1 = strpos( $pair, '=' ) ) !== false ) {
					$hidden_inputs[ ] = htmlHiddenInput(
						urldecode( substr( $pair, 0, $pos1 ) ),
						urldecode( substr( $pair, $pos1 + 1 ) )
					);
				} else {
					$hidden_inputs[ ] = htmlHiddenInput( urldecode( $pair ), '' );
				}
			}
			$action = substr( $action, 0, $pos );
		}

		$options[ 'action' ] = $action;
		$options[ 'method' ] = $method;
		$form                = htmlBeginTag( 'form', $options );
		if ( ! empty( $hidden_inputs ) ) {
			$form .= "\n" . implode( "\n", $hidden_inputs );
		}

		return $form;
	}

	/**
	 * Generates a form end tag.
	 *
	 * @return string the generated tag
	 * @see htmlBeginForm()
	 */
	function htmlEndForm() {
		return '</form>';
	}

	/**
	 * Generates a hyperlink tag.
	 *
	 * @param string            $text    link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 *                                   such as an image tag. If this is coming from end users, you should consider
	 *                                   [[encode()]] it to prevent XSS attacks.
	 * @param array|string|null $url     the URL for the hyperlink tag. This parameter will be processed by
	 *                                   [[Url::to()]] and will be used for the "href" attribute of the tag. If this
	 *                                   parameter is null, the "href" attribute will not be generated.
	 * @param array             $options the tag options in terms of name-value pairs. These will be rendered as
	 *                                   the attributes of the resulting tag. The values will be HTML-encoded using
	 *                                   [[encode()]]. If a value is null, the corresponding attribute will not be
	 *                                   rendered. See [[renderTagAttributes()]] for details on how attributes are
	 *                                   being rendered.
	 *
	 * @return string the generated hyperlink
	 * @see Url::to()
	 */
	function a( $text, $url = null, $options = array() ) {
		if ( $url !== null ) {
			$options[ 'href' ] = $url;
		}

		return htmlTag( 'a', $text, $options );
	}

	/**
	 * Generates a mailto hyperlink.
	 *
	 * @param string $text    link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 *                        such as an image tag. If this is coming from end users, you should consider [[encode()]]
	 *                        it to prevent XSS attacks.
	 * @param string $email   email address. If this is null, the first parameter (link body) will be treated
	 *                        as the email address and used.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated mailto link
	 */
	function mailto( $text, $email = null, $options = array() ) {
		$options[ 'href' ] = 'mailto:' . ( $email === null ? $text : $email );

		return htmlTag( 'a', $text, $options );
	}

	/**
	 * Generates an image tag.
	 *
	 * @param array|string $src     the image URL. This parameter will be processed by [[Url::to()]].
	 * @param array        $options the tag options in terms of name-value pairs. These will be rendered as
	 *                              the attributes of the resulting tag. The values will be HTML-encoded using
	 *                              [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *                              See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated image tag
	 */
	function img( $src, $options = array() ) {
		$options[ 'src' ] = $src;
		if ( ! isset( $options[ 'alt' ] ) ) {
			$options[ 'alt' ] = '';
		}

		return htmlTag( 'img', '', $options );
	}

	/**
	 * Generates a label tag.
	 *
	 * @param string $content label text. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 *                        such as an image tag. If this is is coming from end users, you should [[encode()]]
	 *                        it to prevent XSS attacks.
	 * @param string $for     the ID of the HTML element that this label is associated with.
	 *                        If this is null, the "for" attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated label tag
	 */
	function label( $content, $for = null, $options = array() ) {
		$options[ 'for' ] = $for;

		return htmlTag( 'label', $content, $options );
	}

	/**
	 * Generates a button tag.
	 *
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 *                        Therefore you can pass in HTML code such as an image tag. If this is is coming from end
	 *                        users, you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	function htmlButton( $content = 'Button', $options = array() ) {
		return htmlTag( 'button', $content, $options );
	}

	/**
	 * Generates a submit button tag.
	 *
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 *                        Therefore you can pass in HTML code such as an image tag. If this is is coming from end
	 *                        users, you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated submit button tag
	 */
	function htmlSubmitButton( $content = 'Submit', $options = array() ) {
		$options[ 'type' ] = 'submit';

		return htmlButton( $content, $options );
	}

	/**
	 * Generates a reset button tag.
	 *
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 *                        Therefore you can pass in HTML code such as an image tag. If this is is coming from end
	 *                        users, you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated reset button tag
	 */
	function reset_button( $content = 'Reset', $options = array() ) {
		$options[ 'type' ] = 'reset';

		return htmlButton( $content, $options );
	}

	/**
	 * Generates an input type of the given type.
	 *
	 * @param string $type    the type attribute.
	 * @param string $name    the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated input tag
	 */
	function htmlInput( $type, $name = null, $value = null, $options = array() ) {
		$options[ 'type' ]  = $type;
		$options[ 'name' ]  = $name;
		$options[ 'value' ] = $value === null ? null : (string) $value;

		return htmlTag( 'input', '', $options );
	}

	/**
	 * Generates an input button.
	 *
	 * @param string $label   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	function htmlButtonInput( $label = null, $options = array() ) {
		$options[ 'type' ]  = 'button';
		$options[ 'value' ] = $label === null ? __( 'Button', 'phoenix-framework' ) : $label;

		return htmlTag( 'input', '', $options );
	}

	/**
	 * Generates a submit input button.
	 *
	 * @param string $label   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	function htmlSubmitInput( $label = null, $options = array() ) {
		$options[ 'type' ]  = 'submit';
		$options[ 'value' ] = $label === null ? __( 'Submit', 'phoenix-framework' ) : $label;

		return htmlTag( 'input', '', $options );
	}

	/**
	 * Generates a reset input button.
	 *
	 * @param string $label   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the attributes of the button tag. The values will be HTML-encoded using [[encode()]].
	 *                        Attributes whose value is null will be ignored and not put in the tag returned.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	function htmlResetInput( $label = null, $options = array() ) {
		$options[ 'type' ]  = 'reset';
		$options[ 'value' ] = $label === null ? __( 'Reset', 'phoenix-framework' ) : $label;

		return htmlTag( 'input', '', $options );
	}

	/**
	 * Generates a text input field.
	 *
	 * @param string $name    the name attribute.
	 * @param string $value   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	function htmlTextInput( $name, $value = null, $options = array() ) {
		return htmlInput( 'text', $name, $value, $options );
	}

	/**
	 * Generates a hidden input field.
	 *
	 * @param string $name    the name attribute.
	 * @param string $value   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	function htmlHiddenInput( $name, $value = null, $options = array() ) {
		return htmlInput( 'hidden', $name, $value, $options );
	}

	/**
	 * Generates a password input field.
	 *
	 * @param string $name    the name attribute.
	 * @param string $value   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	function password_input( $name, $value = null, $options = array() ) {
		return htmlInput( 'password', $name, $value, $options );
	}

	/**
	 * Generates a file input field.
	 * To use a file input field, you should set the enclosing form's "enctype" attribute to
	 * be "multipart/form-data". After the form is submitted, the uploaded file information
	 * can be obtained via $_FILES[$name] (see PHP documentation).
	 *
	 * @param string $name    the name attribute.
	 * @param string $value   the value attribute. If it is null, the value attribute will not be generated.
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated button tag
	 */
	function fileInput( $name, $value = null, $options = array() ) {
		return htmlInput( 'file', $name, $value, $options );
	}

	/**
	 * Generates a text area input.
	 *
	 * @param string $name    the input name
	 * @param string $value   the input value. Note that it will be encoded using [[encode()]].
	 * @param array  $options the tag options in terms of name-value pairs. These will be rendered as
	 *                        the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 *                        If a value is null, the corresponding attribute will not be rendered.
	 *                        See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated text area tag
	 */
	function htmlTextarea( $name, $value = '', $options = array() ) {
		$options[ 'name' ] = $name;

		return htmlTag( 'textarea', htmlEncode( $value ), $options );
	}


	/**
	 * Renders the option tags that can be used by [[dropDownList()]] and [[listBox()]].
	 *
	 * @param string|array $selection  the selected value(s). This can be either a string for single selection
	 *                                 or an array for multiple selections.
	 * @param array        $items      the option data items. The array keys are option values, and the array values
	 *                                 are the corresponding option labels. The array can also be nested (i.e. some
	 *                                 array values are arrays too). For each sub-array, an option group will be
	 *                                 generated whose label is the key associated with the sub-array. If you have a
	 *                                 list of data models, you may convert them into the format described above using
	 *                                 [[self::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param array        $tagOptions the $options parameter that is passed to the [[dropDownList()]] or [[listBox()]]
	 *                                 call. This method will take out these elements, if any: "prompt", "options" and
	 *                                 "groups". See more details in [[dropDownList()]] for the explanation of these
	 *                                 elements.
	 *
	 * @return string the generated list options
	 */
	function htmlRenderSelectOptions( $selection, $items, &$tagOptions = array() ) {
		Phoenix_Framework::load( 'array' );
		$lines        = array();
		$encodeSpaces = arrayRemove( $tagOptions, 'encodeSpaces', false );
		if ( isset( $tagOptions[ 'prompt' ] ) ) {
			$prompt   = $encodeSpaces ? str_replace( ' ', '&nbsp;', htmlEncode( $tagOptions[ 'prompt' ] ) ) : htmlEncode( $tagOptions[ 'prompt' ] );
			$lines[ ] = htmlTag( 'option', $prompt, array( 'value' => '' ) );
		}

		$options = isset( $tagOptions[ 'options' ] ) ? $tagOptions[ 'options' ] : array();
		$groups  = isset( $tagOptions[ 'groups' ] ) ? $tagOptions[ 'groups' ] : array();
		unset( $tagOptions[ 'prompt' ], $tagOptions[ 'options' ], $tagOptions[ 'groups' ] );
		$options[ 'encodeSpaces' ] = arrayGetValue( $options, 'encodeSpaces', $encodeSpaces );

		foreach ( $items as $key => $value ) {
			if ( is_array( $value ) ) {
				$groupAttrs            = isset( $groups[ $key ] ) ? $groups[ $key ] : array();
				$groupAttrs[ 'label' ] = $key;
				$attrs                 = array( 'options' => $options, 'groups' => $groups );
				$content               = htmlRenderSelectOptions( $selection, $value, $attrs );
				$lines[ ]              = htmlTag( 'optgroup', "\n" . $content . "\n", $groupAttrs );
			} else {
				$attrs               = isset( $options[ $key ] ) ? $options[ $key ] : array();
				$attrs[ 'value' ]    = (string) $key;
				$attrs[ 'selected' ] = $selection !== null &&
				                       ( ! is_array( $selection ) && ! strcmp( $key, $selection )
				                         || is_array( $selection ) && in_array( $key, $selection ) );
				$lines[ ]            = htmlTag( 'option', ( $encodeSpaces ? str_replace( ' ', '&nbsp;', htmlEncode( $value ) ) : htmlEncode( $value ) ), $attrs );
			}
		}

		return implode( "\n", $lines );
	}

	/**
	 * Generates a list box.
	 *
	 * @param string       $name      the input name
	 * @param string|array $selection the selected value(s)
	 * @param array        $items     the option data items. The array keys are option values, and the array values
	 *                                are the corresponding option labels. The array can also be nested (i.e. some
	 *                                array values are arrays too). For each sub-array, an option group will be
	 *                                generated whose label is the key associated with the sub-array. If you have a
	 *                                list of data models, you may convert them into the format described above using
	 *                                [[self::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param array        $options   the tag options in terms of name-value pairs. The following options are specially
	 *                                handled:
	 *
	 * - prompt: string, a prompt text to be displayed as the first option;
	 * - options: array, the attributes for the select option tags. The array keys must be valid option values,
	 *   and the array values are the extra attributes for the corresponding option tags. For example,
	 *
	 *   ~~~
	 *   [
	 *       'value1' => ['disabled' => true],
	 *       'value2' => ['label' => 'value 2'],
	 *   ];
	 *   ~~~
	 *
	 * - groups: array, the attributes for the optgroup tags. The structure of this is similar to that of 'options',
	 *   except that the array keys represent the optgroup labels specified in $items.
	 * - unselect: string, the value that will be submitted when no option is selected.
	 *   When this attribute is set, a hidden field will be generated so that if no option is selected in multiple
	 *   mode, we can still obtain the posted unselect value.
	 * - encodeSpaces: bool, whether to encode spaces in option prompt and option value with `&nbsp;` character.
	 *   Defaults to `false`.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated list box tag
	 */
	function htmlListBox( $name, $selection = null, $items = array(), $options = array() ) {
		if ( ! array_key_exists( 'size', $options ) ) {
			$options[ 'size' ] = 4;
		}
		if ( ! empty( $options[ 'multiple' ] ) && substr( $name, - 2 ) !== '[]' ) {
			$name .= '[]';
		}
		$options[ 'name' ] = $name;
		if ( isset( $options[ 'unselect' ] ) ) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			if ( substr( $name, - 2 ) === '[]' ) {
				$name = substr( $name, 0, - 2 );
			}
			$hidden = htmlHiddenInput( $name, $options[ 'unselect' ] );
			unset( $options[ 'unselect' ] );
		} else {
			$hidden = '';
		}
		$selectOptions = htmlRenderSelectOptions( $selection, $items, $options );

		return $hidden . htmlTag( 'select', "\n" . $selectOptions . "\n", $options );
	}


	/**
	 * Generates a drop-down list.
	 *
	 * @param string $name      the input name
	 * @param string $selection the selected value
	 * @param array  $items     the option data items. The array keys are option values, and the array values
	 *                          are the corresponding option labels. The array can also be nested (i.e. some array
	 *                          values are arrays too). For each sub-array, an option group will be generated whose
	 *                          label is the key associated with the sub-array. If you have a list of data models, you
	 *                          may convert them into the format described above using
	 *                          [[self::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param array  $options   the tag options in terms of name-value pairs. The following options are specially
	 *                          handled:
	 *
	 * - prompt: string, a prompt text to be displayed as the first option;
	 * - options: array, the attributes for the select option tags. The array keys must be valid option values,
	 *   and the array values are the extra attributes for the corresponding option tags. For example,
	 *
	 *   ~~~
	 *   [
	 *       'value1' => ['disabled' => true],
	 *       'value2' => ['label' => 'value 2'],
	 *   ];
	 *   ~~~
	 *
	 * - groups: array, the attributes for the optgroup tags. The structure of this is similar to that of 'options',
	 *   except that the array keys represent the optgroup labels specified in $items.
	 * - encodeSpaces: bool, whether to encode spaces in option prompt and option value with `&nbsp;` character.
	 *   Defaults to `false`.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated drop-down list tag
	 */
	function htmlDropDownList( $name, $selection = null, $items = array(), $options = array() ) {
		if ( ! empty( $options[ 'multiple' ] ) ) {
			return htmlListBox( $name, $selection, $items, $options );
		}
		$options[ 'name' ] = $name;
		unset( $options[ 'unselect' ] );
		$selectOptions = htmlRenderSelectOptions( $selection, $items, $options );

		return htmlTag( 'select', "\n" . $selectOptions . "\n", $options );
	}

	/**
	 * Generates a radio button input.
	 *
	 * @param string  $name    the name attribute.
	 * @param boolean $checked whether the radio button should be checked.
	 * @param array   $options the tag options in terms of name-value pairs. The following options are specially
	 *                         handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. When this attribute
	 *   is present, a hidden input will be generated so that if the radio button is not checked and is submitted,
	 *   the value of this attribute will still be submitted to the server via the hidden input.
	 * - label: string, a label displayed next to the radio button.  It will NOT be HTML-encoded. Therefore you can
	 * pass
	 *   in HTML code such as an image tag. If this is is coming from end users, you should [[encode()]] it to prevent
	 *   XSS attacks. When this option is specified, the radio button will be enclosed by a label tag.
	 * - label_options: array, the HTML attributes for the label tag. This is only used when the "label" option is
	 * specified.
	 * - container: array|boolean, the HTML attributes for the container tag. This is only used when the "label" option
	 * is specified. If it is false, no container will be rendered. If it is an array or not, a "div" container will be
	 * rendered around the the radio button.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting radio button tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated radio button tag
	 */
	function radio( $name, $checked = false, $options = array() ) {
		$options[ 'checked' ] = (boolean) $checked;
		$value                = array_key_exists( 'value', $options ) ? $options[ 'value' ] : '1';
		if ( isset( $options[ 'uncheck' ] ) ) {
			// add a hidden field so that if the radio button is not selected, it still submits a value
			$hidden = htmlHiddenInput( $name, $options[ 'uncheck' ] );
			unset( $options[ 'uncheck' ] );
		} else {
			$hidden = '';
		}
		if ( isset( $options[ 'label' ] ) ) {
			$label         = $options[ 'label' ];
			$label_options = isset( $options[ 'label_options' ] ) ? $options[ 'label_options' ] : array();
			$container     = isset( $options[ 'container' ] ) ? $options[ 'container' ] : array( 'class' => 'radio' );
			unset( $options[ 'label' ], $options[ 'label_options' ], $options[ 'container' ] );
			$content = label( htmlInput( 'radio', $name, $value, $options ) . ' ' . $label, null, $label_options );
			if ( is_array( $container ) ) {
				return $hidden . htmlTag( 'div', $content, $container );
			} else {
				return $hidden . $content;
			}
		} else {
			return $hidden . htmlInput( 'radio', $name, $value, $options );
		}
	}

	/**
	 * Generates a checkbox input.
	 *
	 * @param string  $name    the name attribute.
	 * @param boolean $checked whether the checkbox should be checked.
	 * @param array   $options the tag options in terms of name-value pairs. The following options are specially
	 *                         handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the checkbox. When this attribute
	 *   is present, a hidden input will be generated so that if the checkbox is not checked and is submitted,
	 *   the value of this attribute will still be submitted to the server via the hidden input.
	 * - label: string, a label displayed next to the checkbox.  It will NOT be HTML-encoded. Therefore you can pass
	 *   in HTML code such as an image tag. If this is is coming from end users, you should [[encode()]] it to prevent
	 *   XSS attacks. When this option is specified, the checkbox will be enclosed by a label tag.
	 * - label_options: array, the HTML attributes for the label tag. This is only used when the "label" option is
	 * specified.
	 * - container: array|boolean, the HTML attributes for the container tag. This is only used when the "label" option
	 * is specified. If it is false, no container will be rendered. If it is an array or not, a "div" container will be
	 * rendered around the the radio button.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting checkbox tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated checkbox tag
	 */
	function htmlCheckbox( $name, $checked = false, $options = array() ) {
		$options[ 'checked' ] = (boolean) $checked;
		$value                = array_key_exists( 'value', $options ) ? $options[ 'value' ] : '1';
		if ( isset( $options[ 'uncheck' ] ) ) {
			// add a hidden field so that if the checkbox is not selected, it still submits a value
			$hidden = htmlHiddenInput( $name, $options[ 'uncheck' ] );
			unset( $options[ 'uncheck' ] );
		} else {
			$hidden = '';
		}
		if ( isset( $options[ 'label' ] ) ) {
			$label         = $options[ 'label' ];
			$label_options = isset( $options[ 'label_options' ] ) ? $options[ 'label_options' ] : array();
			$container     = isset( $options[ 'container' ] ) ? $options[ 'container' ] : array( 'class' => 'checkbox' );
			unset( $options[ 'label' ], $options[ 'label_options' ], $options[ 'container' ] );
			$content = label( htmlInput( 'checkbox', $name, $value, $options ) . ' ' . $label, null, $label_options );
			if ( is_array( $container ) ) {
				return $hidden . htmlTag( 'div', $content, $container );
			} else {
				return $hidden . $content;
			}
		} else {
			return $hidden . htmlInput( 'checkbox', $name, $value, $options );
		}
	}


	/**
	 * Generates a list of checkboxes.
	 * A checkbox list allows multiple selection, like [[listBox()]].
	 * As a result, the corresponding submitted value is an array.
	 *
	 * @param string       $name      the name attribute of each checkbox.
	 * @param string|array $selection the selected value(s).
	 * @param array        $items     the data item used to generate the checkboxes.
	 *                                The array keys are the checkbox values, while the array values are the
	 *                                corresponding labels.
	 * @param array        $options   options (name => config) for the checkbox list container tag.
	 *                                The following options are specially handled:
	 *
	 * - tag: string, the tag name of the container element.
	 * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
	 *   By setting this option, a hidden input will be generated.
	 * - encode: boolean, whether to HTML-encode the checkbox labels. Defaults to true.
	 *   This option is ignored if `item` option is set.
	 * - separator: string, the HTML code that separates items.
	 * - itemOptions: array, the options for generating the radio button tag using [[checkbox()]].
	 * - item: callable, a callback that can be used to customize the generation of the HTML code
	 *   corresponding to a single item in $items. The signature of this callback must be:
	 *
	 *   ~~~
	 *   function ($index, $label, $name, $checked, $value)
	 *   ~~~
	 *
	 *   where $index is the zero-based index of the checkbox in the whole list; $label
	 *   is the label for the checkbox; and $name, $value and $checked represent the name,
	 *   value and the checked status of the checkbox input, respectively.
	 *
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated checkbox list
	 */
	function htmlCheckboxList( $name, $selection = null, $items = array(), $options = array() ) {
		if ( substr( $name, - 2 ) !== '[]' ) {
			$name .= '[]';
		}

		$formatter   = isset( $options[ 'item' ] ) ? $options[ 'item' ] : null;
		$itemOptions = isset( $options[ 'itemOptions' ] ) ? $options[ 'itemOptions' ] : array();
		$encode      = ! isset( $options[ 'encode' ] ) || $options[ 'encode' ];
		$lines       = array();
		$index       = 0;
		foreach ( $items as $value => $label ) {
			$checked = $selection !== null &&
			           ( ! is_array( $selection ) && ! strcmp( $value, $selection )
			             || is_array( $selection ) && in_array( $value, $selection ) );
			if ( $formatter !== null ) {
				$lines[ ] = call_user_func( $formatter, $index, $label, $name, $checked, $value );
			} else {
				$lines[ ] = htmlCheckbox( $name, $checked, array_merge( $itemOptions, array(
					'value' => $value,
					'label' => $encode ? htmlEncode( $label ) : $label,
				) ) );
			}
			$index ++;
		}

		if ( isset( $options[ 'unselect' ] ) ) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			$name2  = substr( $name, - 2 ) === '[]' ? substr( $name, 0, - 2 ) : $name;
			$hidden = htmlHiddenInput( $name2, $options[ 'unselect' ] );
		} else {
			$hidden = '';
		}
		$separator = isset( $options[ 'separator' ] ) ? $options[ 'separator' ] : "\n";

		$tag = isset( $options[ 'tag' ] ) ? $options[ 'tag' ] : 'div';
		unset( $options[ 'tag' ], $options[ 'unselect' ], $options[ 'encode' ], $options[ 'separator' ], $options[ 'item' ], $options[ 'itemOptions' ] );

		return $hidden . htmlTag( $tag, implode( $separator, $lines ), $options );
	}

	/**
	 * Generates a list of radio buttons.
	 * A radio button list is like a checkbox list, except that it only allows single selection.
	 *
	 * @param string       $name      the name attribute of each radio button.
	 * @param string|array $selection the selected value(s).
	 * @param array        $items     the data item used to generate the radio buttons.
	 *                                The array keys are the radio button values, while the array values are the
	 *                                corresponding labels.
	 * @param array        $options   options (name => config) for the radio button list. The following options are
	 *                                supported:
	 *
	 * - unselect: string, the value that should be submitted when none of the radio buttons is selected.
	 *   By setting this option, a hidden input will be generated.
	 * - encode: boolean, whether to HTML-encode the checkbox labels. Defaults to true.
	 *   This option is ignored if `item` option is set.
	 * - separator: string, the HTML code that separates items.
	 * - itemOptions: array, the options for generating the radio button tag using [[radio()]].
	 * - item: callable, a callback that can be used to customize the generation of the HTML code
	 *   corresponding to a single item in $items. The signature of this callback must be:
	 *
	 *   ~~~
	 *   function ($index, $label, $name, $checked, $value)
	 *   ~~~
	 *
	 *   where $index is the zero-based index of the radio button in the whole list; $label
	 *   is the label for the radio button; and $name, $value and $checked represent the name,
	 *   value and the checked status of the radio button input, respectively.
	 *
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated radio button list
	 */
	function htmlRadioList( $name, $selection = null, $items = array(), $options = array() ) {
		$encode      = ! isset( $options[ 'encode' ] ) || $options[ 'encode' ];
		$formatter   = isset( $options[ 'item' ] ) ? $options[ 'item' ] : null;
		$itemOptions = isset( $options[ 'itemOptions' ] ) ? $options[ 'itemOptions' ] : array();
		$lines       = array();
		$index       = 0;
		foreach ( $items as $value => $label ) {
			$checked = $selection !== null &&
			           ( ! is_array( $selection ) && ! strcmp( $value, $selection )
			             || is_array( $selection ) && in_array( $value, $selection ) );
			if ( $formatter !== null ) {
				$lines[ ] = call_user_func( $formatter, $index, $label, $name, $checked, $value );
			} else {
				$lines[ ] = radio( $name, $checked, array_merge( $itemOptions, array(
					'value' => $value,
					'label' => $encode ? htmlEncode( $label ) : $label,
				) ) );
			}
			$index ++;
		}

		$separator = isset( $options[ 'separator' ] ) ? $options[ 'separator' ] : "\n";
		if ( isset( $options[ 'unselect' ] ) ) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			$hidden = htmlHiddenInput( $name, $options[ 'unselect' ] );
		} else {
			$hidden = '';
		}

		$tag = isset( $options[ 'tag' ] ) ? $options[ 'tag' ] : 'div';
		unset( $options[ 'tag' ], $options[ 'unselect' ], $options[ 'encode' ], $options[ 'separator' ], $options[ 'item' ], $options[ 'itemOptions' ] );

		return $hidden . htmlTag( $tag, implode( $separator, $lines ), $options );
	}

	/**
	 * Generates an unordered list.
	 *
	 * @param array|\Traversable $items   the items for generating the list. Each item generates a single list item.
	 *                                    Note that items will be automatically HTML encoded if `$options['encode']` is
	 *                                    not set or true.
	 * @param array              $options options (name => config) for the radio button list. The following options are
	 *                                    supported:
	 *
	 * - encode: boolean, whether to HTML-encode the items. Defaults to true.
	 *   This option is ignored if the `item` option is specified.
	 * - itemOptions: array, the HTML attributes for the `li` tags. This option is ignored if the `item` option is
	 * specified.
	 * - item: callable, a callback that is used to generate each individual list item.
	 *   The signature of this callback must be:
	 *
	 *   ~~~
	 *   function ($item, $index)
	 *   ~~~
	 *
	 *   where $index is the array key corresponding to `$item` in `$items`. The callback should return
	 *   the whole list item tag.
	 *
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated unordered list. An empty string is returned if `$items` is empty.
	 */
	function htmlUl( $items, $options = array() ) {
		if ( empty( $items ) ) {
			return '';
		}
		$tag         = isset( $options[ 'tag' ] ) ? $options[ 'tag' ] : 'ul';
		$encode      = ! isset( $options[ 'encode' ] ) || $options[ 'encode' ];
		$formatter   = isset( $options[ 'item' ] ) ? $options[ 'item' ] : null;
		$itemOptions = isset( $options[ 'itemOptions' ] ) ? $options[ 'itemOptions' ] : array();
		unset( $options[ 'tag' ], $options[ 'encode' ], $options[ 'item' ], $options[ 'itemOptions' ] );
		$results = array();
		foreach ( $items as $index => $item ) {
			if ( $formatter !== null ) {
				$results[ ] = call_user_func( $formatter, $item, $index );
			} else {
				$results[ ] = htmlTag( 'li', $encode ? htmlEncode( $item ) : $item, $itemOptions );
			}
		}

		return htmlTag( $tag, "\n" . implode( "\n", $results ) . "\n", $options );
	}

	/**
	 * Generates an ordered list.
	 *
	 * @param array|\Traversable $items   the items for generating the list. Each item generates a single list item.
	 *                                    Note that items will be automatically HTML encoded if `$options['encode']` is
	 *                                    not set or true.
	 * @param array              $options options (name => config) for the radio button list. The following options are
	 *                                    supported:
	 *
	 * - encode: boolean, whether to HTML-encode the items. Defaults to true.
	 *   This option is ignored if the `item` option is specified.
	 * - itemOptions: array, the HTML attributes for the `li` tags. This option is ignored if the `item` option is
	 * specified.
	 * - item: callable, a callback that is used to generate each individual list item.
	 *   The signature of this callback must be:
	 *
	 *   ~~~
	 *   function ($item, $index)
	 *   ~~~
	 *
	 *   where $index is the array key corresponding to `$item` in `$items`. The callback should return
	 *   the whole list item tag.
	 *
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 * @return string the generated ordered list. An empty string is returned if `$items` is empty.
	 */
	function htmlOl( $items, $options = array() ) {
		$options[ 'tag' ] = 'ol';

		return htmlUl( $items, $options );
	}


	/**
	 * Renders the HTML tag attributes.
	 *
	 * Attributes whose values are of boolean type will be treated as
	 * [boolean attributes](http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes).
	 *
	 * Attributes whose values are null will not be rendered.
	 *
	 * The values of attributes will be HTML-encoded using [[encode()]].
	 *
	 * The "data" attribute is specially handled when it is receiving an array value. In this case,
	 * the array will be "expanded" and a list data attributes will be rendered. For example,
	 *
	 * @param array $attributes attributes to be rendered. The attribute values will be HTML-encoded using [[encode()]].
	 *
	 * @return string the rendering result. If the attributes are not empty, they will be rendered
	 * into a string with a leading white space (so that it can be directly appended to the tag name
	 * in a tag. If there is no attribute, an empty string will be returned.
	 */
	function htmlRenderTagAttributes( $attributes ) {
		global $htmlAttributeOrder;
		if ( count( $attributes ) > 1 ) {
			$sorted = array();
			foreach ( $htmlAttributeOrder as $name ) {
				if ( isset( $attributes[ $name ] ) ) {
					$sorted[ $name ] = $attributes[ $name ];
				}
			}
			$attributes = array_merge( $sorted, $attributes );
		}

		$html = '';
		foreach ( $attributes as $name => $value ) {
			if ( is_bool( $value ) ) {
				if ( $value ) {
					$html .= " $name";
				}
			} elseif ( is_array( $value ) && $name === 'data' ) {
				foreach ( $value as $n => $v ) {
					if ( is_array( $v ) ) {
						$html .= " $name-$n='" . json_encode( $v, JSON_HEX_APOS ) . "'";
					} else {
						$html .= " $name-$n=\"" . htmlEncode( $v ) . '"';
					}
				}
			} elseif ( $value !== null ) {
				$html .= " $name=\"" . htmlEncode( $value ) . '"';
			}
		}

		return $html;
	}

	/**
	 * Adds a CSS class to the specified options.
	 * If the CSS class is already in the options, it will not be added again.
	 *
	 * @param array  $options the options to be modified.
	 * @param string $class   the CSS class to be added
	 */
	function htmlAddCssClass( &$options, $class ) {
		if ( isset( $options[ 'class' ] ) ) {
			$classes = ' ' . $options[ 'class' ] . ' ';
			if ( strpos( $classes, ' ' . $class . ' ' ) === false ) {
				$options[ 'class' ] .= ' ' . $class;
			}
		} else {
			$options[ 'class' ] = $class;
		}
	}

	/**
	 * Removes a CSS class from the specified options.
	 *
	 * @param array  $options the options to be modified.
	 * @param string $class   the CSS class to be removed
	 */
	function htmlRemoveCssClass( &$options, $class ) {
		if ( isset( $options[ 'class' ] ) ) {
			$classes = array_unique( preg_split( '/\s+/', $options[ 'class' ] . ' ' . $class, - 1, PREG_SPLIT_NO_EMPTY ) );
			if ( ( $index = array_search( $class, $classes ) ) !== false ) {
				unset( $classes[ $index ] );
			}
			if ( empty( $classes ) ) {
				unset( $options[ 'class' ] );
			} else {
				$options[ 'class' ] = implode( ' ', $classes );
			}
		}
	}

	/**
	 * Adds the specified CSS style to the HTML options.
	 *
	 * If the options already contain a `style` element, the new style will be merged
	 * with the existing one. If a CSS property exists in both the new and the old styles,
	 * the old one may be overwritten if `$overwrite` is true.
	 *
	 * For example,
	 *
	 * ```php
	 * Html::addCssStyle($options, 'width: 100px; height: 200px');
	 * ```
	 *
	 * @param array        $options   the HTML options to be modified.
	 * @param string|array $style     the new style string (e.g. `'width: 100px; height: 200px'`) or
	 *                                array (e.g. `['width' => '100px', 'height' => '200px']`).
	 * @param boolean      $overwrite whether to overwrite existing CSS properties if the new style
	 *                                contain them too.
	 *
	 * @see htmlRemoveCssStyle()
	 * @see htmlCssStyleFromArray()
	 * @see htmlCssStyleToArray()
	 */
	function htmlAddCssStyle( &$options, $style, $overwrite = true ) {
		if ( ! empty( $options[ 'style' ] ) ) {
			$oldStyle = htmlCssStyleToArray( $options[ 'style' ] );
			$newStyle = is_array( $style ) ? $style : htmlCssStyleToArray( $style );
			if ( ! $overwrite ) {
				foreach ( $newStyle as $property => $value ) {
					if ( isset( $oldStyle[ $property ] ) ) {
						unset( $newStyle[ $property ] );
					}
				}
			}
			$style = htmlCssStyleFromArray( array_merge( $oldStyle, $newStyle ) );
		}
		$options[ 'style' ] = $style;
	}

	/**
	 * Removes the specified CSS style from the HTML options.
	 *
	 * For example,
	 *
	 * ```php
	 * Html::removeCssStyle($options, ['width', 'height']);
	 * ```
	 *
	 * @param array        $options    the HTML options to be modified.
	 * @param string|array $properties the CSS properties to be removed. You may use a string
	 *                                 if you are removing a single property.
	 *
	 * @see htmlAddCssStyle()
	 */
	function htmlRemoveCssStyle( &$options, $properties ) {
		if ( ! empty( $options[ 'style' ] ) ) {
			$style = htmlCssStyleToArray( $options[ 'style' ] );
			foreach ( (array) $properties as $property ) {
				unset( $style[ $property ] );
			}
			$options[ 'style' ] = htmlCssStyleFromArray( $style );
		}
	}

	/**
	 * Converts a CSS style array into a string representation.
	 *
	 * For example,
	 *
	 * ```php
	 * print_r(Html::cssStyleFromArray(['width' => '100px', 'height' => '200px']));
	 * // will display: 'width: 100px; height: 200px;'
	 * ```
	 *
	 * @param array $style the CSS style array. The array keys are the CSS property names,
	 *                     and the array values are the corresponding CSS property values.
	 *
	 * @return string the CSS style string. If the CSS style is empty, a null will be returned.
	 */
	function htmlCssStyleFromArray( array $style ) {
		$result = '';
		foreach ( $style as $name => $value ) {
			$result .= "$name: $value; ";
		}

		// return null if empty to avoid rendering the "style" attribute
		return $result === '' ? null : rtrim( $result );
	}

	/**
	 * Converts a CSS style string into an array representation.
	 *
	 * The array keys are the CSS property names, and the array values
	 * are the corresponding CSS property values.
	 *
	 * For example,
	 *
	 * ```php
	 * print_r(Html::cssStyleToArray('width: 100px; height: 200px;'));
	 * // will display: ['width' => '100px', 'height' => '200px']
	 * ```
	 *
	 * @param string $style the CSS style string
	 *
	 * @return array the array representation of the CSS style
	 */
	function htmlCssStyleToArray( $style ) {
		$result = array();
		foreach ( explode( ';', $style ) as $property ) {
			$property = explode( ':', $property );
			if ( count( $property ) > 1 ) {
				$result[ trim( $property[ 0 ] ) ] = trim( $property[ 1 ] );
			}
		}

		return $result;
	}

	/**
	 * Returns the real attribute name from the given attribute expression.
	 *
	 * An attribute expression is an attribute name prefixed and/or suffixed with array indexes.
	 * It is mainly used in tabular data input and/or input of array type. Below are some examples:
	 *
	 * - `[0]content` is used in tabular data input to represent the "content" attribute
	 *   for the first model in tabular input;
	 * - `dates[0]` represents the first array element of the "dates" attribute;
	 * - `[0]dates[0]` represents the first array element of the "dates" attribute
	 *   for the first model in tabular input.
	 *
	 * If `$attribute` has neither prefix nor suffix, it will be returned back without change.
	 *
	 * @param string $attribute the attribute name or expression
	 *
	 * @return string the attribute name without prefix and suffix.
	 * @throws Exception if the attribute name contains non-word characters.
	 */
	function htmlGetAttributeName( $attribute ) {
		if ( preg_match( '/(^|.*\])([\w\.]+)(\[.*|$)/', $attribute, $matches ) ) {
			return $matches[ 2 ];
		} else {
			throw new Exception( 'Attribute name must contain word characters only.' );
		}
	}

	