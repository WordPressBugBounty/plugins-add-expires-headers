<?php
if (! defined('ABSPATH')) {
    die;
}

/*
* Declaring Class
*/

class AEH_Lazy_Loading
{
    private static $instance = null;
    public $settings;
    public $lazy_loading_settings;
    public function __construct()
    {
        $this->settings = AEH_Settings::get_instance();
        // $this->lazy_loading_settings = get_option('aeh_lazy_loading_settings', $this->settings->init_lazy_loading_default());
        $this->lazy_loading_settings = get_option('aeh_lazy_loading_settings');
        if (isset($this->lazy_loading_settings['enable']) && $this->lazy_loading_settings['enable']) {
            add_filter('the_content', array($this, 'aeh_add_lazyload_functionality'), 10000);
            add_action('wp_footer', array($this, 'lazyload_images_script'));
            if (isset($this->lazy_loading_settings['lazyload_shortcodes']) && $this->lazy_loading_settings['lazyload_shortcodes']) {
                add_filter('do_shortcode_tag', array($this, 'lazyload_wrap_shortcode_output'), 10, 4);
            }
            if (isset($this->lazy_loading_settings['lazyload_widgets']) && $this->lazy_loading_settings['lazyload_widgets']) {
                add_filter('widget_display_callback', array($this, 'lazyload_wrap_widget_output'), 10, 3);
            }
            add_action('wp_enqueue_scripts', function () {
                wp_add_inline_script('jquery', file_get_contents(AEH_DIR . 'assests/js/lazyload-widgets.js'));
            });
            // Media uploader is now handled in main admin class
        }
    }
    public static function get_instance()
    {
        if (! self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function lazyload_wrap_widget_output($instance, $widget, $args)
    {
        ob_start();
        $widget->widget($args, $instance);
        $output = ob_get_clean();

        $encoded = base64_encode($output);

        echo '<div class="lazy-widget" data-content="' . esc_attr($encoded) . '">
              <noscript>' . $output . '</noscript>
          </div>';

        return false;
    }
    public function lazyload_wrap_shortcode_output($output, $tag, $attr, $m)
    {
        $included =  $this->lazy_loading_settings['shortcode_tags'];
        $cleaned = array_column($included, 'shortcodeTag');
        $included_string = implode(",", $cleaned);
        preg_match_all('/\[([a-zA-Z0-9\-_]+)(\s[^\]]*)?\]/', $included_string, $matches);
        $shortcode_tags = array_unique($matches[1]);
        if (!in_array($tag, $shortcode_tags)) {
            return $output;
        }
        $encoded = base64_encode($output);

        return '<div class="lazy-shortcode" data-content="' . esc_attr($encoded) . '">
                <noscript>' . $output . '</noscript>
            </div>';
    }
    public function aeh_add_lazyload_functionality($content)
    {
        if (isset($this->lazy_loading_settings['exclude_selectors']) && !empty($this->lazy_loading_settings['exclude_selectors'])) {
            $result = $this->separate_numbers_strings();
        } else {
            $result = ['numbers' => [], 'classes' => [], 'ids' => []];
        }
        $excluded_ids = isset($result['numbers']) ? $result['numbers'] : []; // example post/page IDs
        if (!empty($excluded_ids)) {
            if (is_singular() && in_array(get_the_ID(), $excluded_ids)) {
                return $content;
            }
        }
        // Tags allowed by settings
        $lazy_iframe_enabled = !empty($this->lazy_loading_settings['lazyload_iframes']);
        $lazy_video_enabled  = !empty($this->lazy_loading_settings['lazyload_videos']);
        $lazy_image_enabled  = !empty($this->lazy_loading_settings['lazyload_images']);
        $lazy_bg_enabled     = !empty($this->lazy_loading_settings['lazyload_backgrounds']);
        // Placeholder
        $placeholder = isset($this->lazy_loading_settings['placeholder_url']) &&
            is_string($this->lazy_loading_settings['placeholder_url']) &&
            filter_var($this->lazy_loading_settings['placeholder_url'], FILTER_VALIDATE_URL)
            ? $this->lazy_loading_settings['placeholder_url']
            : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

        // Define classes or IDs to exclude
        $excluded_classes = isset($result['classes']) ? $result['classes'] : [];
        $excluded_ids     = isset($result['ids']) ? $result['ids'] : [];

        $content = preg_replace_callback('/<(img|iframe|video)[^>]+>/i', function ($matches) use ($placeholder, $lazy_iframe_enabled, $lazy_video_enabled, $lazy_image_enabled, $excluded_classes, $excluded_ids) {
            $tag = $matches[0];
            $tagName = strtolower($matches[1]);

            // Skip if lazy loading disabled by settings
            if (
                ($tagName === 'img' && !$lazy_image_enabled) ||
                ($tagName === 'iframe' && !$lazy_iframe_enabled) ||
                ($tagName === 'video' && !$lazy_video_enabled)
            ) {
                return $tag;
            }

            // Skip if tag already lazy-loaded
            if (strpos($tag, 'loading="lazy"') !== false || strpos($tag, 'data-src') !== false) {
                return $tag;
            }

            // Check for excluded class
            if (!empty($excluded_classes)) {
                foreach ($excluded_classes as $cls) {
                    if (preg_match('/class=["\'][^"\']*' . preg_quote($cls, '/') . '[^"\']*["\']/', $tag)) {
                        return $tag;
                    }
                }
            }

            // Check for excluded ID
            if (!empty($excluded_ids)) {
                foreach ($excluded_ids as $id) {
                    if (preg_match('/id=["\']' . preg_quote($id, '/') . '["\']/', $tag)) {
                        return $tag;
                    }
                }
            }

            // Get original src
            preg_match('/src=["\']([^"\']+)["\']/', $tag, $srcMatch);
            $originalSrc = $srcMatch[1] ?? '';

            // Apply lazyload logic
            if ($tagName === 'img' || $tagName === 'iframe') {
                $tag = preg_replace('/src=["\'][^"\']+["\']/', 'src="' . esc_url($placeholder) . '" data-src="' . esc_url($originalSrc) . '"', $tag);
            } elseif ($tagName === 'video') {
                $tag = preg_replace('/src=["\'][^"\']+["\']/', 'data-src="' . esc_url($originalSrc) . '"', $tag);
                // Also update source tags
                $tag = preg_replace_callback('/<source[^>]+src=["\']([^"\']+)["\']/', function ($srcMatch) {
                    return str_replace('src="' . $srcMatch[1] . '"', 'data-src="' . esc_url($srcMatch[1]) . '"', $srcMatch[0]);
                }, $tag);
            }

            // Add loading="lazy"
            if (strpos($tag, 'loading=') === false) {
                $tag = preg_replace('/<' . $tagName . '/', '<' . $tagName . ' loading="lazy"', $tag);
            }

            // Add class="lazyload" and animation class
            $animation_class = isset($this->lazy_loading_settings['animation_on_load']) && $this->lazy_loading_settings['animation_on_load'] !== 'none' ? ' ' . $this->lazy_loading_settings['animation_on_load'] : '';

            if (preg_match('/class=["\']([^"\']*)["\']/', $tag)) {
                $tag = preg_replace('/class=["\']([^"\']*)["\']/', 'class="$1 lazyload' . $animation_class . '"', $tag);
            } else {
                $tag = preg_replace('/<' . $tagName . '/', '<' . $tagName . ' class="lazyload' . $animation_class . '"', $tag);
            }

            return $tag . '<noscript>' . $matches[0] . '</noscript>';
        }, $content);
        if ($lazy_bg_enabled) {
            $content = preg_replace_callback('/<([a-z0-9]+)([^>]*?)style=["\'][^"\']*background(?:-image)?\s*:\s*url\(([^"\')]+)\)[^"\']*["\']([^>]*)>/i', function ($matches) use ($excluded_classes, $excluded_ids) {
                $tagName = $matches[1];
                $beforeStyle = $matches[2];
                $bgUrl = $matches[3];
                $afterStyle = $matches[4];

                $fullTag = $matches[0];

                // Check excluded classes and IDs
                if (!empty($excluded_classes)) {
                    foreach ($excluded_classes as $cls) {
                        if (preg_match('/class=["\'][^"\']*' . preg_quote($cls, '/') . '[^"\']*["\']/', $fullTag)) {
                            return $fullTag;
                        }
                    }
                }
                if (!empty($excluded_ids)) {
                    foreach ($excluded_ids as $id) {
                        if (preg_match('/id=["\']' . preg_quote($id, '/') . '["\']/', $fullTag)) {
                            return $fullTag;
                        }
                    }
                }

                // Remove inline background-image
                $tag = preg_replace('/style=["\'][^"\']*background(?:-image)?\s*:\s*url\([^"\']+\)[^"\']*["\']/', '', $fullTag);

                // Add data-bg attribute
                $tag = preg_replace('/<([a-z0-9]+)/i', '<$1 data-bg="' . esc_url($bgUrl) . '"', $tag);

                // Add lazyload-bg class and animation class
                $animation_class = isset($this->lazy_loading_settings['animation_on_load']) && $this->lazy_loading_settings['animation_on_load'] !== 'none' ? ' ' . $this->lazy_loading_settings['animation_on_load'] : '';

                if (preg_match('/class=["\']([^"\']*)["\']/', $tag)) {
                    $tag = preg_replace('/class=["\']([^"\']*)["\']/', 'class="$1 lazyload-bg' . $animation_class . '"', $tag);
                } else {
                    $tag = preg_replace('/<' . $tagName . '/', '<' . $tagName . ' class="lazyload-bg' . $animation_class . '"', $tag);
                }

                return $tag . '<noscript>' . $fullTag . '</noscript>';
            }, $content);
        }
        return $content;
    }
    public function separate_numbers_strings()
    {
        $input = $this->lazy_loading_settings['exclude_selectors'];
        $items = array_map('trim', explode(',', $input));
        $items = array_map(function ($item) {
            return preg_replace('/\s+/', '', $item);
        }, $items);
        $classes = [];
        $ids = [];
        $numbers = [];

        foreach ($items as $item) {
            if (is_numeric($item)) {
                $numbers[] = $item;
            } elseif (preg_match('/^\.[a-zA-Z0-9_-]+$/', $item)) {
                $classes[] = str_replace('.', '', $item);
            } elseif (preg_match('/^#[a-zA-Z0-9_-]+$/', $item)) {
                $ids[] = str_replace('#', '', $item);
            }
            // else: ignore or collect as "other"
        }
        $result['numbers'] = $numbers;
        $result['classes'] = $classes;
        $result['ids'] = $ids;
        return $result;
    }
    /* Load lazy loading JS (optional if you use data-src) */
    public function lazyload_images_script()
    {
        // Get animation setting
        $animation_setting = isset($this->lazy_loading_settings['animation_on_load']) ? $this->lazy_loading_settings['animation_on_load'] : 'none';

?> <script>
            document.addEventListener("DOMContentLoaded", function() {
                const lazyElems = document.querySelectorAll('.lazyload[data-src], .lazyload video, .lazyload source, .lazyload-bg[data-bg]');
                const animationType = '<?php echo esc_js($animation_setting); ?>';

                const loadLazyElement = el => {
                    // IMG or IFRAME
                    if (el.dataset.src) {
                        el.setAttribute('src', el.dataset.src);
                        el.removeAttribute('data-src');
                    }

                    // VIDEO
                    if (el.tagName === 'VIDEO') {
                        const sources = el.querySelectorAll('source[data-src]');
                        sources.forEach(source => {
                            source.setAttribute('src', source.dataset.src);
                            source.removeAttribute('data-src');
                        });
                        el.load();
                    }

                    // BACKGROUND IMAGE
                    if (el.dataset.bg) {
                        el.style.backgroundImage = `url('${el.dataset.bg}')`;
                        el.removeAttribute('data-bg');
                    }

                    // Apply animation class if not 'none'
                    if (animationType !== 'none') {
                        el.classList.add(animationType);
                    }

                    // Add loaded class and remove lazy classes
                    el.classList.add('lazyloaded');
                    el.classList.remove('lazyload', 'lazyload-bg');
                };

                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            loadLazyElement(entry.target);
                            obs.unobserve(entry.target);
                        }
                    });
                }, {
                    rootMargin: "200px 0px", // preload early
                    threshold: 0.01
                });

                lazyElems.forEach(el => observer.observe(el));
            });
        </script>
        <!-- <script>
            document.addEventListener("DOMContentLoaded", function() {
                const lazyImages = [].slice.call(document.querySelectorAll("img.lazyload"));

                if ("IntersectionObserver" in window) {
                    let observer = new IntersectionObserver(function(entries, observer) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                let img = entry.target;
                                img.src = img.dataset.src;
                                img.classList.remove("lazyload");
                                observer.unobserve(img);
                            }
                        });
                    });

                    lazyImages.forEach(function(img) {
                        observer.observe(img);
                    });
                } else {
                    // Fallback: load all images
                    lazyImages.forEach(function(img) {
                        img.src = img.dataset.src;
                        img.classList.remove("lazyload");
                    });
                }
            });
        </script>-->
<?php
    }
}
