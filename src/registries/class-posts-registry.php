<?php
/**
 * Class Posts_Registry
 *
 * Registers configuration-driven virtual WordPress posts without saving
 * them to the WordPress database.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Registries;

use DealerluxUtils\Traits\Singleton as DealerluxUtils_Singleton;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Runtime registry for virtual posts.
 *
 * Post definitions are loaded from the compiled config/posts.php registry.
 * Each matched virtual post is represented by a synthetic WP_Post object.
 */
class Posts_Registry {

	/**
	 * Use the singleton loader.
	 *
	 * This prevents the class from being instantiated more than once
	 * during a single WordPress request.
	 */
	use DealerluxUtils_Singleton;

	/**
	 * Loaded posts configuration.
	 *
	 * @var array
	 */
	private $registry = array();

	/**
	 * Currently matched virtual post.
	 *
	 * @var array
	 */
	private $current_post = array();

	/**
	 * Current virtual post type.
	 *
	 * @var string
	 */
	private $current_post_type = '';

	/**
	 * Current virtual post registry key.
	 *
	 * @var string
	 */
	private $current_post_key = '';

	/**
	 * Prevent repeated request matching.
	 *
	 * @var bool
	 */
	private $request_matched = false;

    /**
     * Query variable used to identify virtual post requests.
     *
     * @var string
     */
    private $virtual_post_query_var = 'dealerlux_virtual_post';

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->registry = $this->load_config();
	}

	/**
	 * Determine whether this class should be registered.
	 *
	 * @return bool True when the registry should be initialized.
	 */
	protected static function can_register() {
		return true;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
        if ( ! $this->is_enabled() ) {
            return;
        }

        add_action(
            'parse_request',
            array( $this, 'match_request' ),
            1
        );

        add_filter(
            'posts_pre_query',
            array( $this, 'inject_virtual_post' ),
            10,
            2
        );

        add_filter(
            'document_title_parts',
            array( $this, 'filter_document_title' )
        );

        add_filter(
            'body_class',
            array( $this, 'filter_body_classes' )
        );

        add_filter(
            'post_class',
            array( $this, 'filter_post_classes' ),
            10,
            3
        );

        add_filter(
            'template_include',
            array( $this, 'filter_template' ),
            99
        );

        add_filter(
            'redirect_canonical',
            array( $this, 'disable_canonical_redirect' ),
            10,
            2
        );

        add_filter(
            'get_canonical_url',
            array( $this, 'filter_canonical_url' ),
            10,
            2
        );

        add_filter(
            'query_vars',
            array( $this, 'register_query_vars' )
        );
	}

	/**
	 * Get the complete posts registry configuration.
	 *
	 * @return array
	 */
	public function all() {
		return $this->registry;
	}

	/**
	 * Determine whether the registry was loaded.
	 *
	 * @return bool
	 */
	public function is_loaded() {
		return ! empty( $this->registry );
	}

	/**
	 * Determine whether virtual posts are enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$enabled = $this->get_config_value(
			array(
				'settings',
				'enable',
			),
			false
		);

		if ( is_bool( $enabled ) ) {
			return $enabled;
		}

		if (
			is_string( $enabled ) &&
			function_exists(
				'dl_convert_string_to_boolean'
			)
		) {
			$converted = dl_convert_string_to_boolean(
				$enabled
			);

			return null !== $converted
				? $converted
				: false;
		}

		return (bool) $enabled;
	}

	/**
	 * Get all registered post types.
	 *
	 * @return array
	 */
	public function get_post_types() {
		$posts = $this->get_config_value(
			array( 'posts' ),
			array()
		);

		return is_array( $posts )
			? $posts
			: array();
	}

	/**
	 * Get all virtual posts belonging to a post type.
	 *
	 * @param string $post_type Post type.
	 * @return array
	 */
	public function get_posts( $post_type ) {
		$post_type = $this->normalize_key(
			$post_type
		);

		if ( '' === $post_type ) {
			return array();
		}

		$post_types = $this->get_post_types();

		return isset( $post_types[ $post_type ] )
			&& is_array( $post_types[ $post_type ] )
				? $post_types[ $post_type ]
				: array();
	}

	/**
	 * Get one virtual post by post type and registry key.
	 *
	 * @param string $post_type Post type.
	 * @param string $post_key  Stable registry key.
	 * @return array
	 */
	public function get_post(
		$post_type,
		$post_key
	) {
		$post_type = $this->normalize_key(
			$post_type
		);

		$post_key = $this->normalize_registry_key(
			$post_key
		);

		if (
			'' === $post_type ||
			'' === $post_key
		) {
			return array();
		}

		$posts = $this->get_posts(
			$post_type
		);

		if (
			! isset( $posts[ $post_key ] ) ||
			! is_array( $posts[ $post_key ] )
		) {
			return array();
		}

		return $this->normalize_post(
			$post_type,
			$post_key,
			$posts[ $post_key ]
		);
	}

	/**
	 * Determine whether a virtual post is registered.
	 *
	 * @param string $post_type Post type.
	 * @param string $post_key  Stable registry key.
	 * @return bool
	 */
	public function has_post(
		$post_type,
		$post_key
	) {
		return ! empty(
			$this->get_post(
				$post_type,
				$post_key
			)
		);
	}

	/**
	 * Get a virtual post by its full URL path.
	 *
	 * Examples:
	 *
	 * forms
	 * forms/service-request
	 * forms/service/booking
	 *
	 * @param string $path Requested path.
	 * @return array
	 */
	public function get_post_by_path( $path ) {
		if ( ! is_string( $path ) ) {
			return array();
		}

		$path = trim(
			wp_normalize_path( $path ),
			'/'
		);

		if ( '' === $path ) {
			return array();
		}

		foreach (
			$this->get_post_types()
			as $post_type => $posts
		) {
			if (
				! is_string( $post_type ) ||
				! is_array( $posts )
			) {
				continue;
			}

			foreach ( $posts as $post_key => $post ) {
				if (
					! is_string( $post_key ) ||
					! is_array( $post )
				) {
					continue;
				}

				$post_path = $this->get_post_path(
					$post_type,
					$post_key
				);

				if (
					'' !== $post_path &&
					$path === $post_path
				) {
					return $this->get_post(
						$post_type,
						$post_key
					);
				}
			}
		}

		return array();
	}

	/**
	 * Get the full nested path for a virtual post.
	 *
	 * @param string $post_type Post type.
	 * @param string $post_key  Stable registry key.
	 * @param array  $visited   Previously visited registry keys.
	 * @return string
	 */
	public function get_post_path(
		$post_type,
		$post_key,
		array $visited = array()
	) {
		$post_type = $this->normalize_key(
			$post_type
		);

		$post_key = $this->normalize_registry_key(
			$post_key
		);

		if (
			'' === $post_type ||
			'' === $post_key
		) {
			return '';
		}

		$visited_key = $post_type . ':' . $post_key;

		if (
			in_array(
				$visited_key,
				$visited,
				true
			)
		) {
			return '';
		}

		$visited[] = $visited_key;

		$post = $this->get_post(
			$post_type,
			$post_key
		);

		if ( empty( $post ) ) {
			return '';
		}

		$slug = isset( $post['slug'] )
			&& is_string( $post['slug'] )
				? trim( $post['slug'], '/' )
				: '';

		if ( '' === $slug ) {
			return '';
		}

		$parent_key = isset( $post['parent'] )
			&& is_string( $post['parent'] )
				? $this->normalize_registry_key(
					$post['parent']
				)
				: '';

		if ( '' === $parent_key ) {
			return $slug;
		}

		$parent = $this->get_post(
			$post_type,
			$parent_key
		);

		if ( empty( $parent ) ) {
			return '';
		}

		$parent_path = $this->get_post_path(
			$post_type,
			$parent_key,
			$visited
		);

		if ( '' === $parent_path ) {
			return '';
		}

		return $parent_path . '/' . $slug;
	}

	/**
	 * Get the public URL for a virtual post.
	 *
	 * @param string $post_type Post type.
	 * @param string $post_key  Stable registry key.
	 * @param string $fallback  Fallback URL.
	 * @return string
	 */
	public function get_url(
		$post_type,
		$post_key,
		$fallback = ''
	) {
		$path = $this->get_post_path(
			$post_type,
			$post_key
		);

		if ( '' === $path ) {
			return $fallback;
		}

		return home_url(
			'/' . user_trailingslashit( $path )
		);
	}

	/**
	 * Get the currently matched virtual post.
	 *
	 * @return array
	 */
	public function get_current_post() {
		return $this->current_post;
	}

	/**
	 * Get the current virtual post type.
	 *
	 * @return string
	 */
	public function get_current_post_type() {
		return $this->current_post_type;
	}

	/**
	 * Get the current virtual post registry key.
	 *
	 * @return string
	 */
	public function get_current_post_key() {
		return $this->current_post_key;
	}

    /**
     * Register custom virtual-post query variables.
     *
     * @param array $query_vars Public query variables.
     * @return array
     */
    public function register_query_vars( $query_vars ) {
        $query_vars[] = $this->virtual_post_query_var;

        return array_values(
            array_unique( $query_vars )
        );
    }

	/**
	 * Determine whether the current request is a virtual post.
	 *
	 * When no arguments are supplied, checks for any virtual post.
	 *
	 * @param string $post_type Optional post type.
	 * @param string $post_key  Optional registry key.
	 * @return bool
	 */
	public function is_virtual_post(
		$post_type = '',
		$post_key = ''
	) {
		if ( empty( $this->current_post ) ) {
			return false;
		}

		if (
			'' !== $post_type &&
			$this->current_post_type !== $post_type
		) {
			return false;
		}

		if (
			'' !== $post_key &&
			$this->current_post_key !== $post_key
		) {
			return false;
		}

		return true;
	}

    /**
     * Match the current request against the virtual posts registry.
     *
     * @param \WP $wp Current WordPress request object.
     * @return void
     */
	public function match_request( $wp ) {
		if (
			$this->request_matched ||
			$this->should_ignore_request() ||
			! $wp instanceof \WP
		) {
			return;
		}

		$this->request_matched = true;

		$request_path = isset( $wp->request )
			&& is_string( $wp->request )
				? trim(
					wp_normalize_path( $wp->request ),
					'/'
				)
				: '';

		if ( '' === $request_path ) {
			return;
		}

		$post = $this->get_post_by_path(
			$request_path
		);

		if ( empty( $post ) ) {
			return;
		}

		$this->current_post      = $post;
		$this->current_post_type = $post['post_type'];
		$this->current_post_key  = $post['registry_key'];

		$wp->query_vars = array_merge(
			$wp->query_vars,
			$this->build_query_vars( $post )
		);

		$wp->query_vars[ $this->virtual_post_query_var ] = 1;
	}

    /**
     * Short-circuit the database query and inject a synthetic post.
     *
     * @param array|null $posts Existing short-circuited posts.
     * @param \WP_Query  $query Current query object.
     * @return array|null
     */
    public function inject_virtual_post(
        $posts,
        $query
    ) {
        if (
            empty( $this->current_post ) ||
            ! $query instanceof \WP_Query ||
            ! $query->is_main_query()
        ) {
            return $posts;
        }

        $virtual_post = $this->create_wp_post(
            $this->current_post
        );

        if ( ! $virtual_post instanceof \WP_Post ) {
            return $posts;
        }

        $this->configure_query(
            $query,
            $virtual_post
        );

        /*
        * Assigning these here is important because the normal database
        * query has been bypassed.
        */
        $query->posts             = array( $virtual_post );
        $query->post              = $virtual_post;
        $query->post_count        = 1;
        $query->found_posts       = 1;
        $query->max_num_pages     = 1;
        $query->queried_object    = $virtual_post;
        $query->queried_object_id = $virtual_post->ID;

        status_header( 200 );

        return array( $virtual_post );
    }

	/**
	 * Filter the document title.
	 *
	 * @param array $title_parts Document title parts.
	 * @return array
	 */
	public function filter_document_title(
		$title_parts
	) {
		if (
			! $this->is_virtual_post() ||
			empty( $this->current_post['title'] )
		) {
			return $title_parts;
		}

		$title_parts['title'] =
			$this->current_post['title'];

		return $title_parts;
	}

	/**
	 * Filter legacy document titles.
	 *
	 * @param string $title Document title.
	 * @return string
	 */
	public function filter_legacy_document_title(
		$title
	) {
		if (
			! $this->is_virtual_post() ||
			empty( $this->current_post['title'] )
		) {
			return $title;
		}

		return $this->current_post['title'];
	}

	/**
	 * Add virtual-post body classes.
	 *
	 * @param array $classes Existing classes.
	 * @return array
	 */
	public function filter_body_classes(
		$classes
	) {
		if ( ! $this->is_virtual_post() ) {
			return $classes;
		}

		$classes[] = 'virtual-post';

		$classes[] = sanitize_html_class(
			'virtual-post-type-'
			. $this->current_post_type
		);

		$classes[] = sanitize_html_class(
			'virtual-post-'
			. $this->current_post_key
		);

		if (
			'page' === $this->current_post_type
		) {
			$classes[] = 'page';
			$classes[] = 'page-template-default';
		}

		return array_values(
			array_unique( $classes )
		);
	}

	/**
	 * Add virtual-post post classes.
	 *
	 * @param array       $classes Existing classes.
	 * @param string[]    $css_class Additional classes.
	 * @param int|\WP_Post $post Post ID or object.
	 * @return array
	 */
	public function filter_post_classes(
		$classes,
		$css_class,
		$post
	) {
		unset( $css_class );

		if (
			! $this->is_virtual_post() ||
			! $post instanceof \WP_Post ||
			$post->ID !== $this->get_virtual_post_id(
				$this->current_post_type,
				$this->current_post_key
			)
		) {
			return $classes;
		}

		$classes[] = 'virtual-post-entry';

		return array_values(
			array_unique( $classes )
		);
	}

	/**
	 * Select a custom template for a virtual post.
	 *
	 * A configured template may be:
	 *
	 * - an absolute readable file path;
	 * - a path relative to the active theme;
	 * - an empty value, which uses WordPress's normal template hierarchy.
	 *
	 * @param string $template Current template.
	 * @return string
	 */
	public function filter_template( $template ) {
		if ( ! $this->is_virtual_post() ) {
			return $template;
		}

		$configured_template = isset(
			$this->current_post['template']
		)
		&& is_string(
			$this->current_post['template']
		)
			? trim(
				$this->current_post['template']
			)
			: '';

		if ( '' !== $configured_template ) {
			if (
				is_file( $configured_template ) &&
				is_readable( $configured_template )
			) {
				return $configured_template;
			}

			$located_template = locate_template(
				$configured_template
			);

			if ( '' !== $located_template ) {
				return $located_template;
			}
		}

		$hierarchy_template =
			$this->locate_default_template();

		return '' !== $hierarchy_template
			? $hierarchy_template
			: $template;
	}

	/**
	 * Disable canonical redirects for virtual posts.
	 *
	 * @param string|false $redirect_url  Proposed redirect URL.
	 * @param string       $requested_url Requested URL.
	 * @return string|false
	 */
	public function disable_canonical_redirect(
		$redirect_url,
		$requested_url
	) {
		unset( $requested_url );

		return $this->is_virtual_post()
			? false
			: $redirect_url;
	}

	/**
	 * Return the virtual post URL as its canonical URL.
	 *
	 * @param string|false $canonical_url Existing canonical URL.
	 * @param \WP_Post     $post          Current post.
	 * @return string|false
	 */
	public function filter_canonical_url(
		$canonical_url,
		$post
	) {
		if (
			! $this->is_virtual_post() ||
			! $post instanceof \WP_Post
		) {
			return $canonical_url;
		}

		return $this->get_url(
			$this->current_post_type,
			$this->current_post_key,
			$canonical_url
		);
	}

	/**
	 * Load config/posts.php.
	 *
	 * The config file is responsible for discovery, compilation, environment
	 * filtering, content loading, and cache invalidation.
	 *
	 * @return array
	 */
	private function load_config() {
		$config_file = dirname(
			__DIR__,
			2
		) . '/config/posts.php';

		$config_file = wp_normalize_path(
			$config_file
		);

		if (
			! is_file( $config_file ) ||
			! is_readable( $config_file )
		) {
			return array();
		}

		$config = require $config_file;

		return is_array( $config )
			? $config
			: array();
	}

	/**
	 * Get a nested configuration value.
	 *
	 * Uses dl_map_array_item() when available.
	 *
	 * @param array $path     Configuration path.
	 * @param mixed $fallback Fallback value.
	 * @return mixed
	 */
	private function get_config_value(
		array $path,
		$fallback = null
	) {
		if (
			function_exists(
				'dl_map_array_item'
			)
		) {
			return dl_map_array_item(
				array(
					'collection'     => $this->registry,
					'collection_map' => $path,
					'default'        => $fallback,
				)
			);
		}

		$value = $this->registry;

		foreach ( $path as $key ) {
			if (
				! is_array( $value ) ||
				! array_key_exists( $key, $value )
			) {
				return $fallback;
			}

			$value = $value[ $key ];
		}

		return $value;
	}

	/**
	 * Normalize a virtual post definition.
	 *
	 * @param string $post_type  Post type.
	 * @param string $post_key   Stable registry key.
	 * @param array  $definition Raw post definition.
	 * @return array
	 */
	private function normalize_post(
		$post_type,
		$post_key,
		array $definition
	) {
		$slug = isset( $definition['slug'] )
			&& is_string( $definition['slug'] )
				? sanitize_title(
					$definition['slug']
				)
				: '';

		$title = isset( $definition['title'] )
			&& is_string( $definition['title'] )
				? trim( $definition['title'] )
				: '';

		if (
			'' === $slug ||
			'' === $title
		) {
			return array();
		}

		$status = isset( $definition['status'] )
			&& is_string( $definition['status'] )
				? sanitize_key(
					$definition['status']
				)
				: 'publish';

		if ( '' === $status ) {
			$status = 'publish';
		}

		$parent = isset( $definition['parent'] )
			&& is_string( $definition['parent'] )
				? trim( $definition['parent'] )
				: '';

		return array(
			'registry_key' => $post_key,
			'post_type'    => $post_type,
			'parent'       => $parent,
			'slug'         => $slug,
			'title'        => $title,
			'content'      => isset(
				$definition['content']
			)
			&& is_string(
				$definition['content']
			)
				? $definition['content']
				: '',
			'excerpt'      => isset(
				$definition['excerpt']
			)
			&& is_string(
				$definition['excerpt']
			)
				? $definition['excerpt']
				: '',
			'template'     => isset(
				$definition['template']
			)
			&& is_string(
				$definition['template']
			)
				? trim(
					$definition['template']
				)
				: '',
			'status'       => $status,
			'author'       => isset(
				$definition['author']
			)
				? absint(
					$definition['author']
				)
				: 0,
			'menu_order'   => isset(
				$definition['menu_order']
			)
				? (int) $definition['menu_order']
				: 0,
			'description'  => isset(
				$definition['description']
			)
			&& is_string(
				$definition['description']
			)
				? $definition['description']
				: '',
			'meta'         => isset(
				$definition['meta']
			)
			&& is_array(
				$definition['meta']
			)
				? $definition['meta']
				: array(),
		);
	}

    /**
     * Build query variables for a matched virtual post.
     *
     * @param array $post Virtual post definition.
     * @return array
     */
    private function build_query_vars( array $post ) {
        $path = $this->get_post_path(
            $post['post_type'],
            $post['registry_key']
        );

        if ( 'page' === $post['post_type'] ) {
            return array(
                'post_type' => 'page',
                'pagename'  => $path,
                'name'      => $post['slug'],
            );
        }

        return array(
            'post_type' => $post['post_type'],
            'name'      => $post['slug'],
        );
    }

    /**
     * Configure the main query for a synthetic post.
     *
     * @param \WP_Query $query Query object.
     * @param \WP_Post  $post  Synthetic post.
     * @return void
     */
    private function configure_query(
        $query,
        $post
    ) {
        $query->is_404               = false;
        $query->is_home              = false;
        $query->is_front_page        = false;
        $query->is_archive           = false;
        $query->is_search            = false;
        $query->is_feed              = false;
        $query->is_comment_feed      = false;
        $query->is_trackback         = false;
        $query->is_posts_page        = false;
        $query->is_post_type_archive = false;
        $query->is_category          = false;
        $query->is_tag               = false;
        $query->is_tax               = false;
        $query->is_author            = false;
        $query->is_date              = false;
        $query->is_year              = false;
        $query->is_month             = false;
        $query->is_day               = false;
        $query->is_time              = false;
        $query->is_singular          = true;

        if ( 'page' === $post->post_type ) {
            $query->is_page       = true;
            $query->is_single     = false;
            $query->is_attachment = false;
        } elseif ( 'attachment' === $post->post_type ) {
            $query->is_page       = false;
            $query->is_single     = false;
            $query->is_attachment = true;
        } else {
            $query->is_page       = false;
            $query->is_single     = true;
            $query->is_attachment = false;
        }
    }

	/**
	 * Create a synthetic WP_Post object.
	 *
	 * @param array $post Virtual post definition.
	 * @return \WP_Post|null
	 */
	private function create_wp_post( array $post ) {
		if (
			empty( $post['registry_key'] ) ||
			empty( $post['post_type'] ) ||
			empty( $post['slug'] ) ||
			empty( $post['title'] )
		) {
			return null;
		}

		$now = current_time( 'mysql' );
		$gmt = current_time( 'mysql', true );

		$post_data = new \stdClass();

		$post_data->ID = $this->get_virtual_post_id(
			$post['post_type'],
			$post['registry_key']
		);

		$post_data->post_author =
			isset( $post['author'] )
				? absint( $post['author'] )
				: 0;

		$post_data->post_date             = $now;
		$post_data->post_date_gmt         = $gmt;
		$post_data->post_content          = $post['content'];
		$post_data->post_title            = $post['title'];
		$post_data->post_excerpt          = $post['excerpt'];
		$post_data->post_status           = $post['status'];
		$post_data->comment_status        = 'closed';
		$post_data->ping_status           = 'closed';
		$post_data->post_password         = '';
		$post_data->post_name             = $post['slug'];
		$post_data->to_ping               = '';
		$post_data->pinged                = '';
		$post_data->post_modified         = $now;
		$post_data->post_modified_gmt     = $gmt;
		$post_data->post_content_filtered = '';
		$post_data->post_parent           =
			$this->get_parent_post_id(
				$post
			);
		$post_data->guid = $this->get_url(
			$post['post_type'],
			$post['registry_key']
		);
		$post_data->menu_order =
			isset( $post['menu_order'] )
				? (int) $post['menu_order']
				: 0;
		$post_data->post_type      = $post['post_type'];
		$post_data->post_mime_type = '';
		$post_data->comment_count  = 0;
		$post_data->filter         = 'raw';

		return new \WP_Post( $post_data );
	}

	/**
	 * Get the synthetic parent post ID.
	 *
	 * @param array $post Virtual post definition.
	 * @return int
	 */
	private function get_parent_post_id(
		array $post
	) {
		if (
			empty( $post['parent'] ) ||
			! is_string( $post['parent'] )
		) {
			return 0;
		}

		$parent = $this->get_post(
			$post['post_type'],
			$post['parent']
		);

		if ( empty( $parent ) ) {
			return 0;
		}

		return $this->get_virtual_post_id(
			$post['post_type'],
			$post['parent']
		);
	}

	/**
	 * Generate a stable negative synthetic post ID.
	 *
	 * @param string $post_type Post type.
	 * @param string $post_key  Registry key.
	 * @return int
	 */
	private function get_virtual_post_id(
		$post_type,
		$post_key
	) {
		$hash = sprintf(
			'%u',
			crc32(
				$post_type . ':' . $post_key
			)
		);

		return -1 * ( (int) $hash + 1 );
	}

	/**
	 * Locate the normal theme template for the current virtual post.
	 *
	 * @return string
	 */
	private function locate_default_template() {
		if ( empty( $this->current_post ) ) {
			return '';
		}

		$post_type = $this->current_post_type;
		$slug      = $this->current_post['slug'];

		if ( 'page' === $post_type ) {
			return get_page_template();
		}

		$templates = array(
			'single-' . $post_type . '-' . $slug . '.php',
			'single-' . $post_type . '.php',
			'single.php',
			'singular.php',
			'index.php',
		);

		return locate_template(
			$templates
		);
	}

	/**
	 * Determine whether the current request should be ignored.
	 *
	 * @return bool
	 */
	private function should_ignore_request() {
		if ( is_admin() ) {
			return true;
		}

		if (
			function_exists( 'wp_doing_ajax' ) &&
			wp_doing_ajax()
		) {
			return true;
		}

		if (
			function_exists( 'wp_doing_cron' ) &&
			wp_doing_cron()
		) {
			return true;
		}

		if (
			defined( 'REST_REQUEST' ) &&
			REST_REQUEST
		) {
			return true;
		}

		if (
			defined( 'XMLRPC_REQUEST' ) &&
			XMLRPC_REQUEST
		) {
			return true;
		}

		return false;
	}

	/**
	 * Normalize a post type or category key.
	 *
	 * @param mixed $value Value to normalize.
	 * @return string
	 */
	private function normalize_key( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return sanitize_key(
			trim( $value )
		);
	}

	/**
	 * Normalize a registry key.
	 *
	 * Registry keys may contain underscores or hyphens.
	 *
	 * @param mixed $value Value to normalize.
	 * @return string
	 */
	private function normalize_registry_key(
		$value
	) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( $value );

		if ( '' === $value ) {
			return '';
		}

		return preg_replace(
			'/[^A-Za-z0-9_-]/',
			'',
			$value
		);
	}
}