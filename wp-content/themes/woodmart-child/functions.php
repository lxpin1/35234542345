<?php




/************************/

function register_ajax_filter_products() {
    add_action('wp_ajax_filter_products', 'filter_products_ajax_handler');
    add_action('wp_ajax_nopriv_filter_products', 'filter_products_ajax_handler');
}

function filter_products_ajax_handler() {
    $length = isset($_POST['f_pa_length']) ? intval($_POST['f_pa_length']) : '';
    $width = isset($_POST['f_pa_width']) ? intval($_POST['f_pa_width']) : '';
    $height = isset($_POST['f_pa_height']) ? intval($_POST['f_pa_height']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    $cached_dimensions = get_option('dimensions_cache', []);

    if (!isset($cached_dimensions[$category])) {
        echo '<p>Товары не найдены.</p>';
        wp_die();
    }

    $dimensions_for_category = $cached_dimensions[$category];
    $filtered_product_ids = array();

    foreach ($dimensions_for_category as $product_id => $dimension) {
        $cached_length = intval($dimension['length']);
        $cached_width = intval($dimension['width']);
        $cached_height = intval($dimension['height']);

        if (($length && $cached_length == $length) || ($width && $cached_width == $width) || ($height && $cached_height == $height)) {
            $filtered_product_ids[] = $product_id;
        }
    }

    if (empty($filtered_product_ids)) {
        echo '<p>Товары не найдены.</p>';
        wp_die();
    }

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post__in' => $filtered_product_ids,
    );

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }
    } else {
        echo '<p>Товары не найдены.</p>';
    }
    wp_reset_postdata();
    $filtered_products_html = ob_get_clean();

    echo $filtered_products_html;
    wp_die();
}



function get_dimension_term_slugs($value, $taxonomy) {
    $tolerance = get_option('dimension_tolerance', 50);
    $term_slugs = array();
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));

    foreach ($terms as $term) {
        $term_value = floatval($term->name);
        if ($term_value >= ($value - $tolerance) && $term_value <= ($value + $tolerance)) {
            $term_slugs[] = $term->slug;
        }
    }

    return $term_slugs;
}

add_action('init', 'register_ajax_filter_products');

function product_dimensions_filter_shortcode() {
    ob_start();
    ?>
    <form id="product-dimensions-filter" action="#" method="POST">
        <div class="dimension-filter">
            <label for="f_pa_length">Длина (мм):</label>
            <div class="input-container">
                <input type="search" id="f_pa_length" name="f_pa_length">
            </div>
            <div class="dimension-hint" id="length-hint"></div>
        </div>
        <div class="dimension-filter">
            <label for="f_pa_width">Ширина (мм):</label>
            <div class="input-container">
                <input type="search" id="f_pa_width" name="f_pa_width">
            </div>
            <div class="dimension-hint" id="width-hint"></div>
        </div>
        <div class="dimension-filter">
            <label for="f_pa_height">Высота (мм):</label>
            <div class="input-container">
                <input type="search" id="f_pa_height" name="f_pa_height">
            </div>
            <div class="dimension-hint" id="height-hint"></div>
        </div>
        <div class="dimension-filter-submit">
            <label style="color:white">.</label>
            <div class="input-container">
                <button type="submit">Фильтр</button>
            </div>
            <div style="color:white" class="dimension-hint">.</div>
        </div>
        <div class="dimension-filter-clear">
            <label style="color:white">.</label>
            <div class="input-container">
                <button type="button" id="clear-fields">Очистить</button>
            </div>
            <div style="color:white" class="dimension-hint">.</div>
        </div>
    </form>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        function filterProducts() {
            var formData = $('#product-dimensions-filter').serialize();
            var category = '<?php echo single_term_title("", false); ?>';

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData + '&action=filter_products&category=' + category,
                beforeSend: function() {
                    $('.woocommerce .products').html('<div class="loading">Ищем на складе...</div>');
                },
                success: function(response) {
                    $('.woocommerce .products').html(response);
                    var length = $('#f_pa_length').val();
                    var width = $('#f_pa_width').val();
                    var height = $('#f_pa_height').val();
                    var title = 'АКБ по запросу ' + length + ' x ' + width + ' x ' + height + ' мм:';
                    $('#custom-h1-title').html('<h1 id="custom-h1-title" style="margin-top: 20px;">' + title + '</h1>');
                    var newUrl = window.location.pathname + '?' + formData;
                    window.history.pushState({path: newUrl}, '', newUrl);
                    $(document).trigger("SizeFilterApplied");
                },
                error: function(error) {
                    console.log('Ошибка AJAX-запроса: ', error);
                }
            });
        }

        function setFieldsAndFilter() {
            var length = getParameterByName('f_pa_length');
            var width = getParameterByName('f_pa_width');
            var height = getParameterByName('f_pa_height');

            if (length || width || height) {
                $('#f_pa_length').val(length);
                $('#f_pa_width').val(width);
                $('#f_pa_height').val(height);
                filterProducts();
            }
        }

        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        setFieldsAndFilter();

        $('#product-dimensions-filter').on('submit', function(event) {
            event.preventDefault();
            filterProducts();
        });

        var category = '<?php echo single_term_title("", false); ?>';
        var dimensions = <?php echo json_encode(get_option('dimensions_cache', [])); ?>;

        function updateHints() {
            var lengthVal = $('#f_pa_length').val();
            var widthVal = $('#f_pa_width').val();
            var heightVal = $('#f_pa_height').val();

            var dimensionsArray = dimensions[category] ? Object.values(dimensions[category]) : [];

            var availableDimensions = dimensionsArray.filter(function(d) {
                return (!lengthVal || parseInt(d.length) >= parseInt(lengthVal)) &&
                       (!widthVal || parseInt(d.width) >= parseInt(widthVal)) &&
                       (!heightVal || parseInt(d.height) >= parseInt(heightVal));
            });

            var lengthOptions = availableDimensions.map(function(d) { return parseInt(d.length); });
            var widthOptions = availableDimensions.map(function(d) { return parseInt(d.width); });
            var heightOptions = availableDimensions.map(function(d) { return parseInt(d.height); });

            $('#length-hint').text(lengthOptions.length ? 'от ' + Math.min.apply(Math, lengthOptions) + ' до ' + Math.max.apply(Math, lengthOptions) : 'Введите длину');
            $('#width-hint').text(widthOptions.length ? 'от ' + Math.min.apply(Math, widthOptions) + ' до ' + Math.max.apply(Math, widthOptions) : 'Введите ширину');
            $('#height-hint').text(heightOptions.length ? 'от ' + Math.min.apply(Math, heightOptions) + ' до ' + Math.max.apply(Math, heightOptions) : 'Введите высоту');
        }

        $('#f_pa_length, #f_pa_width, #f_pa_height').on('input', updateHints);

        updateHints();
        $('#clear-fields').on('click', function() {
            $('#f_pa_length, #f_pa_width, #f_pa_height').val('');
            updateHints();
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('product_dimensions_filter', 'product_dimensions_filter_shortcode');

/*****************************************************/


// Кэширование данных
function cache_product_info() {
    $products = wc_get_products(array(
        'status' => 'publish',
        'limit' => -1,
        'return' => 'ids',
    ));

    $dimensions_cache = array();
    $models_by_brand = array();

    foreach ($products as $product_id) {
        cache_product_dimensions($product_id, $dimensions_cache);
        cache_product_models_by_brand($product_id, $models_by_brand);
    }

    update_option('dimensions_cache', $dimensions_cache);
    update_option('models_by_brand_cache', $models_by_brand);
}

function cache_product_dimensions($product_id, &$dimensions_cache) {
    $product = wc_get_product($product_id);
    $length = $product->get_attribute('pa_length');
    $width = $product->get_attribute('pa_width');
    $height = $product->get_attribute('pa_height');
    $category_terms = wc_get_product_terms($product_id, 'product_cat', array('fields' => 'slugs'));

    // Проверка наличия всех атрибутов
    if ($length && $width && $height) {
        foreach ($category_terms as $category) {
            if (!isset($dimensions_cache[$category])) {
                $dimensions_cache[$category] = array();
            }
            $dimensions_cache[$category][$product_id] = array(
                'length' => intval($length),
                'width' => intval($width),
                'height' => intval($height)
            );
        }
    }
}

function cache_product_models_by_brand($product_id, &$models_by_brand) {
    $category_terms = wc_get_product_terms($product_id, 'product_cat', array('fields' => 'slugs'));
    $brand_terms = wc_get_product_terms($product_id, 'pa_brand', array('fields' => 'names'));
    $model_terms = wc_get_product_terms($product_id, 'pa_model', array('fields' => 'names'));

    if (!empty($brand_terms) && !empty($model_terms)) {
        $brand = $brand_terms[0];
        $model = $model_terms[0];

        foreach ($category_terms as $category) {
            if (!isset($models_by_brand[$category])) {
                $models_by_brand[$category] = array();
            }
            if (!isset($models_by_brand[$category][$brand])) {
                $models_by_brand[$category][$brand] = array();
            }
            if (!in_array($model, $models_by_brand[$category][$brand])) {
                $models_by_brand[$category][$brand][] = $model;
            }
        }
    }
}

// Запускаем кэширование два раза в сутки через CRON
function schedule_cache_product_info() {
    if (!wp_next_scheduled('cache_product_info_event')) {
        wp_schedule_event(time(), 'twicedaily', 'cache_product_info_event');
    }
}
add_action('wp', 'schedule_cache_product_info');

add_action('cache_product_info_event', 'cache_product_info');

// Функция для запуска кэширования по URL
function manual_cache_product_info() {
    if (isset($_GET['cache-product-info']) && $_GET['cache-product-info'] == 'true') {
        cache_product_info();
        echo 'Product info cached successfully.';
        exit;
    }
}
add_action('init', 'manual_cache_product_info');

// Функция для отображения кэша
function show_cached_info() {
    if (isset($_GET['show_cache']) && $_GET['show_cache'] == 'true') {
        $dimensions_cache = get_option('dimensions_cache');
        $models_by_brand = get_option('models_by_brand_cache');

        echo '<pre>';
        print_r($dimensions_cache);
        print_r($models_by_brand);
        echo '</pre>';
        exit;
    }
}
add_action('init', 'show_cached_info');




/*****=================================================================================*******/


function enqueue_select2() {
    // Подключаем Select2 CSS
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');

    // Подключаем Select2 JS
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
                
                    // Добавляем инлайн скрипт для кастомизации Select2
    wp_add_inline_script('select2-js', "
        jQuery(document).ready(function($) {
            // Глобальная настройка языка для Select2
            $.fn.select2.defaults.set('language', {
                noResults: function() {
                    return 'Результаты не найдены';
                }
            });
            $('select').select2();
        });
    ");
                // Добавляем пользовательские стили для Select2
    $custom_css = "
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: var(--wd-primary-color) !important;
    }
                .select2-container--default .select2-search--dropdown .select2-search__field {
                               border: 0px !important;
                }
                .select2-container--open .select2-dropdown--below {
                               margin-top: 6px;
                }
                               button.select2-selection__clear {
                               display: none !important;
                }
                li.select2-results__option.select2-results__option--selectable {
                               margin-bottom: 0;
                               padding-top: 4px;
                               padding-bottom: 4px;
                }

                input.select2-search__field {
                               height: 30px !important;
                }
                ";
    wp_add_inline_style('select2-css', $custom_css);
}
add_action('wp_enqueue_scripts', 'enqueue_select2');



/*===========================*/

add_action( 'woocommerce_product_query', 'custom_pre_get_products_query' );
function custom_pre_get_products_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_product_category() ) {
        $current_term = get_queried_object();
        $current_term_slug = $current_term->slug;
        $hide_category_slug = 'akb';

        if ( $current_term_slug == $hide_category_slug && empty($_GET['filter_brand']) && empty($_GET['filter_model']) ) {
            // Изменяем запрос так, чтобы возвращался хотя бы один товар
            $query->set( 'post__in', array(37431) ); // ID товара, который будет скрыт
        }
    }
}

add_action( 'woocommerce_after_shop_loop', 'custom_shop_message', 25 );
function custom_shop_message() {
    if ( is_product_category('akb') && empty($_GET['filter_brand']) && empty($_GET['filter_model']) ) {
        // Ваше сообщение или инструкция
        echo '<div class="woocommerc1e-info">Выберите фильтры для отображения товаров.</div>';
    }
}

add_action('wp_footer', 'hide_single_product_css');
function hide_single_product_css() {
    // Скрываем товар с ID 37431 по умолчанию
    $style = '<style>.post-37431 { display: none !important; }</style>';

    if ( is_product_category('akb') ) {
        // Проверяем, установлены ли параметры фильтров
        if ( ! empty($_GET['filter_brand']) || ! empty($_GET['filter_model']) ) {
            // Если установлен хотя бы один фильтр, не скрываем товар
            $style = '';
        }

        echo $style;
    }
}


/*===========================*/


// Код для фильтра с выпадающими списками
// Код для фильтра с выпадающими списками
function brand_model_dropdowns_shortcode() {
    if (!is_product_category()) {
        return ''; // Выходим, если не на странице категории
    }

    $current_category = get_queried_object();
    $current_category_slug = $current_category->slug;
    $cached_models_by_brand = get_option('models_by_brand_cache'); // Изменено на get_option

    if (!isset($cached_models_by_brand[$current_category_slug])) {
        return ''; // Выходим, если нет данных для текущей категории
    }

    $brandsModels = $cached_models_by_brand[$current_category_slug];

    ob_start();
    ?>
    <!-- HTML for dropdowns -->
    <div id="brand-model">
        <label for="brand-select">Бренд:</label>
        <select id="brand-select" style="width: 200px;">
            <option value="">Выберите бренд</option>
            <!-- Brand options will be added here -->
        </select>
        
        <label for="model-select">Модель:</label>
        <select id="model-select" style="width: 200px;">
            <option value="">Выберите модель</option>
            <!-- Model options will be added here -->
        </select>
    </div>

    <!-- JavaScript to initialize Select2 and handle filtering -->
    <script>
    jQuery(function($) {
        var brandsModels = <?php echo json_encode($brandsModels); ?>;
        var selectedBrand = getParameterByName('filter_brand');
        var selectedModel = getParameterByName('filter_model');

        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        function populateBrands() {
            $.each(brandsModels, function(brand, models) {
                var isSelected = brand === selectedBrand;
                $('#brand-select').append(new Option(brand, brand, isSelected, isSelected));
            });
        }

        function populateModels(brand) {
            var models = brandsModels[brand] || [];
            $('#model-select').empty().append(new Option('Выберите модель', '')).prop('disabled', models.length === 0);
            $.each(models, function(index, model) {
                var isSelected = model === selectedModel;
                $('#model-select').append(new Option(model, model, isSelected, isSelected));
            });
        }

        // Initialize Select2 for brands
        $('#brand-select').select2({
            placeholder: "Выберите бренд",
            allowClear: true
        }).on('select2:select', function(e) {
            selectedBrand = e.params.data.id;
            populateModels(selectedBrand);
            $('#model-select').prop('disabled', false).val('').trigger('change');
        });

        // Initialize Select2 for models
        $('#model-select').select2({
            placeholder: "Выберите модель",
            allowClear: true
        }).on('select2:select', function(e) {
            selectedModel = e.params.data.id;
            updatePjaxUrl();
        });

            function updatePjaxUrl() {
                if (!selectedModel) {
                    return; // Не обновляем URL, если модель не выбрана
                }
                var pjaxUrl = '?';
                if (selectedBrand) {
                    pjaxUrl += 'filter_brand=' + encodeURIComponent(selectedBrand);
                }
                if (selectedModel) {
                    pjaxUrl += '&filter_model=' + encodeURIComponent(selectedModel);
                }
                pjaxUrl += '&include_akb=true';
                $.pjax({
                    url: pjaxUrl,
                    container: '.main-page-wrapper',
                    timeout: 5000
                });
            }

            // Заполняем бренды и модели при загрузке страницы
            populateBrands();
            if (selectedBrand) {
                populateModels(selectedBrand);
                if (selectedModel) {
                    $('#model-select').prop('disabled', false).val(selectedModel).trigger('change');
                }
            }

            // Повторная инициализация Select2 после завершения PJAX-запроса
            $(document).on('pjax:end', function() {
                $('#brand-select').select2('destroy').select2({
                    placeholder: "Выберите бренд",
                    allowClear: true
                });
                $('#model-select').select2('destroy').select2({
                    placeholder: "Выберите модель",
                    allowClear: true
                });
                populateBrands();
                if (selectedBrand) {
                    populateModels(selectedBrand);
                    if (selectedModel) {
                        $('#model-select').prop('disabled', false).val(selectedModel).trigger('change');
                    }
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('brand_model_dropdowns', 'brand_model_dropdowns_shortcode');








/*===============================================*/

function add_custom_h1_title() {
    // Получаем значения параметров из URL
    $brand = isset($_GET['filter_brand']) ? sanitize_text_field($_GET['filter_brand']) : '';
    $model = isset($_GET['filter_model']) ? sanitize_text_field($_GET['filter_model']) : '';
    $length = isset($_GET['f_pa_length']) ? sanitize_text_field($_GET['f_pa_length']) : '';
    $width = isset($_GET['f_pa_width']) ? sanitize_text_field($_GET['f_pa_width']) : '';
    $height = isset($_GET['f_pa_height']) ? sanitize_text_field($_GET['f_pa_height']) : '';

    // Проверяем, заданы ли все параметры
    if (!empty($brand) && !empty($model)) {
        // Формируем текст заголовка
        $title = 'Тяговые АКБ для ' . $brand . ' ' . $model;
    } elseif (!empty($length) && !empty($width) && !empty($height)) {
        $title = 'АКБ по запросу: ' . $length . ' x ' . $width . ' x ' . $height . ' мм ';
    } else {
        // Если ни один из параметров не задан, выходим из функции
        return;
    }

    // Выводим заголовок H1
    echo '<h1 id="custom-h1-title" style="margin-top: 20px;">' . esc_html($title) . '</h1>';
}

// Добавляем действие, которое будет вызывать функцию add_custom_h1_title
// после фильтров и перед списком товаров.
add_action('woodmart_shop_filters_area', 'add_custom_h1_title', 40);


/*********************************************************************/











/***************************************/
/***************************************/
/***************************************/
/***************************************/


add_filter('woocommerce_checkout_fields', 'populate_checkout_fields');
function populate_checkout_fields($fields) {
    if (WC()->session->get('billing_address_1')) {
        $fields['billing']['billing_address_1']['default'] = WC()->session->get('billing_address_1');
    }
    if (WC()->session->get('billing_city')) {
        $fields['billing']['billing_city']['default'] = WC()->session->get('billing_city');
    }
    if (WC()->session->get('billing_state')) {
        $fields['billing']['billing_state']['default'] = WC()->session->get('billing_state');
    }
    return $fields;
}
/*########### --- ##################*/
function save_billing_details() {
    if ( isset($_POST['billing_city'], $_POST['billing_address_1'], $_POST['billing_state']) ) {
        if ( !session_id() && !headers_sent() ) {
            session_start();
        }
        $billing_city = sanitize_text_field($_POST['billing_city']);
        $billing_address_1 = sanitize_text_field($_POST['billing_address_1']);
        $billing_state = sanitize_text_field($_POST['billing_state']);
        WC()->session->set('billing_city', $billing_city);
        WC()->session->set('billing_address_1', $billing_address_1);
        WC()->session->set('billing_state', $billing_state);

        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            update_user_meta( $current_user->ID, 'billing_city', $billing_city );
            update_user_meta( $current_user->ID, 'billing_address_1', $billing_address_1 );
            update_user_meta( $current_user->ID, 'billing_state', $billing_state );
        }
    }
}
add_action('init', 'save_billing_details');

/*########### --- ##################*/

function show_billing_details_popup_shortcode() {
    $billing_city = WC()->session->get('billing_city', '');
    $billing_address_1 = WC()->session->get('billing_address_1', '');
    $billing_state = WC()->session->get('billing_state', '');

    $form = '<form action="" method="post" style="display: flex; flex-wrap: wrap; row-gap: 10px;">';
    $form .= '<h2 style="color:black; width: 100% !important;">Выберите Ваш город</h2>';
    $form .= '<label for="billing_address_1">Адрес: <input type="text" name="billing_address_1" id="billing_address_1" class="input-text suggestions-input" value="' . esc_attr($billing_address_1) . '" placeholder="Адрес" /></label>';
    $form .= '<label for="billing_city">Город: <input type="text" name="billing_city" id="billing_city" value="' . esc_attr($billing_city) . '" placeholder="Город" readonly /></label>';
    $form .= '<label for="billing_state">Область: <input type="text" class="input-text " value="' . esc_attr($billing_state) . '" placeholder="" name="billing_state" id="billing_state" autocomplete="address-level1" data-input-classes="" readonly></label>';
    $form .= '<input type="submit" class="single_add_to_cart_button alt" style="margin-top: 15px;"value="Сохранить">';
    $form .= '</form>';

    $link_text = $billing_city ? esc_html($billing_city) : 'Выберите город';

    $popup = '<a href="#" id="open-popup" onclick="startSession()">' . $link_text . '</a>';
    $popup .= '<div id="popup" style="display: none;">';
    $popup .= '<a href="#" id="close-popup">Закрыть</a>';
    $popup .= '<div class="popup-content">' . $form . '</div>';
    $popup .= '</div>';

    return $popup;
}
add_shortcode('billing_city', 'show_billing_details_popup_shortcode');



/*########### добавляем время сборки (CarbonFields ##################*/
use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'carbon_fields_register_fields', 'crb_attach_product_meta' );
function crb_attach_product_meta() {
    Container::make( 'post_meta', __( 'Product Information' ) )
    ->where( 'post_type', '=', 'product' )
    ->add_fields( array(
        Field::make( 'radio', 'crb_build_type', 'Build Type' )
            ->add_options(array(
                'fixed' => 'Fixed Days',
                'calendar' =>  'Calendar Date'
            )),
        Field::make( 'text', 'crb_build_days', __( 'Set build Days' ) )
            ->set_attribute( 'type', 'number' )
            ->set_attribute( 'min', 0 )
            ->set_attribute( 'max', 365 )
            ->set_conditional_logic(array(
                array(
                    'field' => 'crb_build_type',
                    'value' => 'fixed',
                )
            )),
        Field::make( 'date', 'crb_build_date', __( 'Choose build Date' ) )
            ->set_conditional_logic(array(
                array(
                    'field' => 'crb_build_type',
                    'value' => 'calendar',
                )
            )),
    ));
}


add_action( 'woocommerce_single_product_summary', 'crb_display_product_build_information', 29 );
function crb_display_product_build_information() {
    global $post;
    $build_type = carbon_get_post_meta( $post->ID, 'crb_build_type' );

    if ($build_type == 'fixed') {
        $build_days = carbon_get_post_meta( $post->ID, 'crb_build_days' );
        if ( ! empty( $build_days ) ) {
            echo '<div class="build-days">Готовность к отгрузке (раб.дней): ' . esc_html( $build_days ) . '</div>';
        }
    } 
    else if ($build_type == 'calendar') {
        $build_date = carbon_get_post_meta( $post->ID, 'crb_build_date' );
        if ( ! empty( $build_date ) ) {
            // Create a DateTime object from the stored string
            $build_date_object = DateTime::createFromFormat('Y-m-d', $build_date);
            // Format the date as 'dd.mm.yyyy'
            $formatted_build_date = $build_date_object->format('d.m.Y');
            echo '<div class="build-days">Ожидаемый срок готовности к отгрузке: ' . esc_html( $formatted_build_date ) . '</div>';
        }
    }
}


/*########### добавляем время сборки - конец ##################*/



/*########### Отображаем преимущества магазина под "в корзину" ##################*/
add_action( 'woocommerce_single_product_summary', 'display_hello_world', 39 );

function display_hello_world() {
    echo do_shortcode('[html_block id="53226"]');
}
/*########### Отображаем преимущества магазина под "в корзину" - конец ##################*/


/*########### добавляем контент в no-products-found ##################*/

function custom_no_products_found_text() {
    if 
	(is_product_category('akb')) {
		echo '<button onclick="window.location.href=window.location.pathname;" style="margin:30px 0;">Новый поиск</button>';
		echo do_shortcode('[contact-form-7 id="97acbd8" title="battery request"]');
    } elseif 
	(is_product_category('forrks')) {
        echo '<p class="woocommerce-info">Custom message for Category 2 here</p>';
    } elseif 
	(is_product_category('qwerfdas')) {
        echo '<p class="woocommerce-info">Custom message for Category 2 here</p>';
    } else {
        echo '<p class="woocommerce-info"></p><button onclick="window.location.href=window.location.pathname;" style="margin:30px 0;">Новый поиск</button>';
    }
}

add_action('woocommerce_no_products_found', 'custom_no_products_found_text');
/*########### добавляем контент в no-products-found - конец ##################*/



add_filter( 'woocommerce_order_item_name', 'display_product_title_as_link', 10, 2 );
function display_product_title_as_link( $item_name, $item ) {

    $_product = wc_get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );

    $link = get_permalink( $_product->get_id() );

    return '<a href="'. $link .'"  rel="nofollow">'. $item_name .'</a>';
}

add_action( 'woocommerce_email_after_order_table', 'add_link_back_to_order', 10, 2 );

function add_link_back_to_order( $order, $is_admin ) {

	// Only for admin emails
	if ( ! $is_admin ) {
		return;
	}

	// Open the section with a paragraph so it is separated from the other content
	$link = '<p>';

	// Add the anchor link with the admin path to the order page
	$link .= '<a href="'. admin_url( 'post.php?post=' . absint( $order->id ) . '&action=edit' ) .'" >';

	// Clickable text
	$link .= __( 'Click here to go to the order page', 'your_domain' );

	// Close the link
	$link .= '</a>';

	// Close the paragraph
	$link .= '</p>';

	// Return the link into the email
	echo $link;

}



function so_39394127_attributes_shortcode( $atts ) {

    global $product;
    if( ! is_object( $product ) || ! $product->has_attributes() ){
        return;
    }
    // parse the shortcode attributes
    $args = shortcode_atts( array(
        'attributes' => array_keys( $product->get_attributes() ), // by default show all attributes
    ), $atts );
    // is pass an attributes param, turn into array
    if( is_string( $args['attributes'] ) ){
        $args['attributes'] = array_map( 'trim', explode( '|' , $args['attributes'] ) );
    }
    // start with a null string because shortcodes need to return not echo a value
    $html="";
    if( ! empty( $args['attributes'] ) ){
        foreach ( $args['attributes'] as $attribute ) {
            // get the WC-standard attribute taxonomy name
            $taxonomy = strpos( $attribute, 'pa_' ) === false ? wc_attribute_taxonomy_name( $attribute ) : $attribute;
            if( taxonomy_is_product_attribute( $taxonomy ) ){
                $html .= strip_tags(get_the_term_list($product->get_id(), $taxonomy));
            }
        }
    } 
    return $html;
}
add_shortcode( 'display_attributes', 'so_39394127_attributes_shortcode' );

add_shortcode( 'post_title','get_the_title' );




function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );


add_filter( 'default_checkout_billing_country', 'truemisha_default_checkout_country' );
 
function truemisha_default_checkout_country( $country ) {
	return 'RU'; // двухбуквенный ISO код страны
}

add_filter( 'woocommerce_new_customer_data', function( $data ) {
    $data['user_login'] = $data['user_email'];
    return $data;
} );



/**
 * @snippet       Display Products From 1 Category @ Shop Page
 * @how-to        Get CustomizeWoo.com FREE
 */

add_filter( 'woocommerce_account_menu_items', 'misha_remove_my_account_links' );
function misha_remove_my_account_links( $menu_links ){
	unset( $menu_links[ 'downloads' ] ); // Disable Downloads
	return $menu_links;
}


/*
* Reduce the strength requirement for woocommerce registration password.
* Strength Settings:
* 0 = Nothing = Anything
* 1 = Weak
* 2 = Medium
* 3 = Strong (default)
*/

add_filter( 'woocommerce_min_password_strength', 'wpglorify_woocommerce_password_filter', 10 );
function wpglorify_woocommerce_password_filter() {
return 0; } //2 represent medium strength password




function wooc_extra_register_fields() {?>
       <p class="form-row form-row-wide">
       <label for="reg_account_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
       <input type="text" class="input-text" name="account_phone" id="reg_account_phone" value="<?php esc_attr_e( $_POST['account_phone'] ); ?>" />
       </p>
       <p class="form-row form-row-first">
       <label for="reg_account_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
       <input type="text" class="input-text" name="account_first_name" id="reg_account_first_name" value="<?php if ( ! empty( $_POST['account_first_name'] ) ) esc_attr_e( $_POST['account_first_name'] ); ?>" />
       </p>
       <p class="form-row form-row-last">
       <label for="reg_account_last_name"><?php _e( 'Last name', 'woocommerce' ); ?><span class="required">*</span></label>
       <input type="text" class="input-text" name="account_last_name" id="reg_account_last_name" value="<?php if ( ! empty( $_POST['account_last_name'] ) ) esc_attr_e( $_POST['account_last_name'] ); ?>" />
       </p>
       <div class="clear"></div>
       <?php
 }
 add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );


/**
* Below code save extra fields.
*/
function wooc_save_extra_register_fields( $customer_id ) {
    if ( isset( $_POST['account_phone'] ) ) {
                 // Phone input filed which is used in WooCommerce
                 update_user_meta( $customer_id, 'account_phone', sanitize_text_field( $_POST['account_phone'] ) );
          }
      if ( isset( $_POST['account_first_name'] ) ) {
             //First name field which is by default
             update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['account_first_name'] ) );
             // First name field which is used in WooCommerce
             update_user_meta( $customer_id, 'account_first_name', sanitize_text_field( $_POST['account_first_name'] ) );
      }
      if ( isset( $_POST['account_last_name'] ) ) {
             // Last name field which is by default
             update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['account_last_name'] ) );
             // Last name field which is used in WooCommerce
             update_user_meta( $customer_id, 'account_last_name', sanitize_text_field( $_POST['account_last_name'] ) );
      }
}
add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );





/************************/


/**
* register fields Validating.
*/
function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
      if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
             $validation_errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'woocommerce' ) );
      }
      if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
             $validation_errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required!.', 'woocommerce' ) );
      }
         return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );




/*====================

// Conditional Show hide checkout fields based on chosen shipping methods
add_action( 'wp_footer', 'conditionally_hidding_billing_company' );
function conditionally_hidding_billing_company(){
    // Only on checkout page
    if( ! ( is_checkout() && ! is_wc_endpoint_url() ) ) return;

    // HERE your shipping methods rate ID "Home delivery"
    $home_delivery = 'pakkelabels_shipping_gls1';
    ?>
    <script>
        jQuery(function($){
            // Choosen shipping method selectors slug
            var shipMethod = 'input[id^="shipping_method_0_free_shipping1"]',
                shipMethodChecked = shipMethod+':checked';

            // Function that shows or hide imput select fields
            function showHide( actionToDo='show', selector='' ){
                if( actionToDo == 'show' )
                    $(selector).show( 200, function(){
                        $(this).addClass("validate-required");
                    });
                else
                    $(selector).hide( 200, function(){
                        $(this).removeClass("validate-required");
                    });
                $(selector).removeClass("woocommerce-validated");
                $(selector).removeClass("woocommerce-invalid woocommerce-invalid-required-field");
            }

            // Initialising: Hide if choosen shipping method is "Home delivery"
            if( $(shipMethodChecked).val() == '<?php echo $home_delivery; ?>' )
                showHide('hide','#billing_company_field' );

            // Live event (When shipping method is changed)
            $( 'form.checkout' ).on( 'change', shipMethod, function() {
                if( $(shipMethodChecked).val() == '<?php echo $home_delivery; ?>' )
                    showHide('hide','#billing_company_field');
                else
                    showHide('show','#billing_company_field');
            });
        });
    </script>
    <?php
}

 =======================================*/



// Display the cart item dimensions in cart and checkout pages 
/*
add_filter( 'woocommerce_get_item_data', 'display_custom_item_data_dimensions', 10, 2 ); 
function display_custom_item_data_dimensions( $cart_item_data, $cart_item ) { 
    if ( $cart_item['data']->has_dimensions() > 0 ){ 
        $cart_item_data[] = array( 'name' => __( 'Dimensions', 'woocommerce' ), 'value' => $cart_item['data']->get_dimensions() . ' ' . get_option('woocommerce_dimensions_unit') ); 
    } 
    return $cart_item_data; 
}

// Save and Display the order item dimensions (everywhere) 
add_action( 'woocommerce_checkout_create_order_line_item', 'display_order_item_data_dimensions', 20, 4 ); 
function display_order_item_data_dimensions( $item, $cart_item_key, $values, $order ) { 
    if ( $values['data']->has_dimensions() > 0 ){ 
        $item->update_meta_data( __( 'Dimensions', 'woocommerce' ), $values['data']->get_dimensions() . ' ' . get_option('woocommerce_dimensions_unit') ); 
    } 
}
*/

// перевод единиц измерений габаритов.
function localize_weight_units($weight) {
    return str_replace('kg', 'кг', $weight); // указываем ЕИ веса
}
add_filter('woocommerce_format_weight', 'localize_weight_units');
function localize_dimensions_units($dimensions) {
    return str_replace('mm', 'мм', $dimensions); //указываем ЕИ длины
}
add_filter('woocommerce_format_dimensions', 'localize_dimensions_units');







/*************360 three_sixty_framerate **********************/

add_filter( 'woodmart_three_sixty_framerate', function () {
	return 6;
});
add_filter( 'woodmart_three_sixty_prev_next_frames', function () {
	return 1;
});



add_filter( 'intermediate_image_sizes', 'delete_intermediate_image_sizes' );

function delete_intermediate_image_sizes( $sizes ){
	return array_diff( $sizes, [
		'1536x1536', 	// размеры которые нужно удалить
		'2048x2048',
	] );
}




/********Скрытие атрибутов *******************************/

function devise_hide_attributes_from_additional_info_tabs( $attributes, $product ) {
    $hidden_attributes = [
        'pa_p-number',
		'pa_chargesp',
		'pa_readiness',
		'pa_\%d0\%bc\%d0\%b0\%d1\%80\%d0\%ba\%d0\%b0-\%d1\%82\%d0\%b5\%d1\%85\%d0\%bd\%d0\%b8\%d0\%ba\%d0\%b8',
		'pa_%d1%81%d1%80%d0%be%d0%ba-%d0%b3%d0%be%d1%82%d0%be%d0%b2%d0%bd%d0%be%d1%81%d1%82%d0%b8-%d0%ba-%d0%be%d1%82%d0%b3%d1%80%d1%83%d0%b7%d0%ba%d0%b5-%d0%b4%d0%bd%d0%b5%d0%b9',
		'pa_%d0%b2%d1%8b%d1%81%d0%be%d1%82%d0%b0-%d0%bc%d0%bc',
    ];
    foreach ( $hidden_attributes as $hidden_attribute ) {
        if ( ! isset( $attributes[ $hidden_attribute ] ) ) {
            continue;
        }
        $attribute = $attributes[ $hidden_attribute ];
        $attribute->set_visible( false );
    }
    return $attributes;
}
add_filter( 'woocommerce_product_get_attributes', 'devise_hide_attributes_from_additional_info_tabs', 20, 2 );  






add_filter( 'woocommerce_default_address_fields', 'custom_override_address_fields', 999, 1 );
function custom_override_address_fields( $address_fields ) {

    // set as not required
    $address_fields['postcode']['required'] = false;
    $address_fields['city']['required'] = false;
    $address_fields['state']['required'] = false;
    $address_fields['address_1']['required'] = false;

    // remove validation
    unset( $address_fields['postcode']['validate'] );
    unset( $address_fields['city']['validate'] );
    unset( $address_fields['state']['validate'] );
    unset( $address_fields['state']['validate'] );
    unset( $address_fields['address_1']['validate'] );

    return $address_fields;
}
 
 
/* ========== ИСПРАВЛЕНИЕ ОТОБРАЖЕНИЯ СТОКА В Карточке ТОВАРа =======*/ 
 
add_filter( 'woocommerce_get_availability', 'custom_override_get_availability', 10, 2);

function custom_override_get_availability( $availability, $_product ) {
	if ( $_product->is_in_stock() ) $availability['availability'] = __('In stock', 'woodmart');
	return $availability;
} 
 
 
/* ========== ИСПРАВЛЕНИЕ ОТОБРАЖЕНИЯ СТОКА В АРХИВЕ ТОВАРОВ =======

if ( ! function_exists( 'woodmart_stock_status_after_title' ) ) {
	/**
	 * Output stock status after title.
	 *
	 * @return void
	 *
	function woodmart_stock_status_after_title() {
		if ( 'after_title' !== woodmart_get_opt( 'stock_status_position' ) ) {
			return;
		}

		global $product;

		$stock_status   = $product->get_availability();
		$wrapper_class  = 'stock';
		$wrapper_class .= 'instock' === $product->get_stock_status() ? ' in-stock' : ' out-of-stock';
		$wrapper_class .= ' wd-style-default';

		if ( ! empty( $stock_status['availability'] ) ) {
			$stock_status_text = $stock_status['availability'];
		} else {
			$stock_status_text = esc_html__( 'In stock', 'woodmart' );
		}

		woodmart_enqueue_inline_style( 'woo-mod-stock-status' );

		?>
			<p class="wd-product-stock <?php echo esc_attr( $wrapper_class ); ?>">
				<?php echo wp_kses( $stock_status_text, 'true' ); ?>
			</p>
		<?php
	}
}
*/

/**
 * @snippet       Hide Subcat Products @ WooCommerce Category Page
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 8
 * @donate $9     https://businessbloomer.com/bloomer-armada/

 
add_action( 'woocommerce_product_query', 'bbloomer_hide_products_subcategory', 9999 );
    
function bbloomer_hide_products_subcategory( $q ) {
    
   if ( ! is_product_category() ) return;
    
   $parent_id = get_queried_object_id();
   $subcats = woocommerce_get_product_subcategories( $parent_id );
   if ( empty( $subcats ) ) return;
    
   $tax_query = (array) $q->get( 'tax_query' );
   $tax_query[] = array(
      'taxonomy' => 'product_cat',
      'field' => 'slug',
      'terms' => array_column( $subcats, 'slug' ),
      'operator' => 'NOT IN'
   );
    
   $q->set( 'tax_query', $tax_query, true );
    
}
 */


//Шорткод для вывода Аттрибутов
function get_product_attributes_shortcode($atts ) {
    // Extract shortcode attributes
    extract( shortcode_atts( array(
        'id'    => get_the_ID(),
    ), $atts, 'display-attributes' ) );

    global $product;

    if ( ! is_a($product, 'WC_Product') ) {
        $product = wc_get_product( $id );
    }

    if ( is_a($product, 'WC_Product') ) {
        $html = []; // Initializing

        foreach ( $product->get_attributes() as $attribute => $values ) {
            $attribute_name = wc_attribute_label($values->get_name());
            $attribute_data = $values->get_data();
            $is_taxonomy    = $attribute_data['is_taxonomy'];

            $option_values    = array(); // Initializing

            // For taxonomy product attribute values
            if( $is_taxonomy ) {
                $terms = $values->get_terms(); // Get attribute WP_Terms

                // Loop through attribute WP_Term(s)
                foreach ( $terms as $term ) {
                    $term_link       = get_term_link( $term, $attribute );
                    $option_values[] = '<strong>'.$term->name.'</strong>';
                }
            }
            // For "custom" product attributes values
            else {
                // Loop through attribute option values
                foreach ( $values->get_options() as $term_name ) {
                    $option_values[] = $term_name;
                }
            }

            $html[] = '<span>' . $attribute_name . '</span>: ' . implode(', ', $option_values);
        }

        return '<div class="product-attributes"><div>' . implode(' </div><div> ', $html) . '</div></div>';
    }
}
add_shortcode( 'display-attributes', 'get_product_attributes_shortcode' );

function custom_function1() {
	wp_enqueue_style( '/wp-content/plugins/advanced-woo-search/assets/css/common.min.css', '/wp-content/plugins/ext-files/aws-external.css');
}
add_action( 'wp_enqueue_scripts', 'custom_function1' );


//Шорткод для вывода ширины товара
add_shortcode( 'pr-width', 'pr_width_func' );

function pr_width_func() {
	// получаем объект товара из глобальной переменной
	global $product;
 	// если параметр задан, то выводим
	if(!is_null($product) && $product->get_width()) {
		return $product->get_width();
	}
}

//Шорткод для вывода длины товара
add_shortcode( 'pr-length', 'pr_length_func' );

function pr_length_func() {
	global $product;
	if(!is_null($product) && $product->get_length()) {
		return $product->get_length();
	}
}

//Шорткод для вывода высоты товара
add_shortcode( 'pr-height', 'pr_height_func' );
function pr_height_func() {
 	global $product;
	if(!is_null($product) && $product->get_height()) {
		return $product->get_height();
	}
}

//Шорткод для вывода Атрибута товара



/*/ Custom conditional function that handle parent product categories too
function has_product_categories( $categories, $product_id = 0 ) {
    $parent_term_ids = $categories_ids = array(); // Initializing
    $taxonomy        = 'product_cat';
    $product_id      = $product_id == 0 ? get_the_id() : $product_id;

    if( is_string( $categories ) ) {
        $categories = (array) $categories;
    }

    // Convert categories term names and slugs to categories term ids
    foreach ( $categories as $category ){
        $result = (array) term_exists( $category, $taxonomy );
        if ( ! empty( $result ) ) {
            $categories_ids[] = reset($result);
        }
    }

    // Loop through the current product category terms to get only parent main category term
    foreach( get_the_terms( $product_id, $taxonomy ) as $term ){
        if( $term->parent > 0 ){
            $parent_term_ids[] = $term->parent; // Set the parent product category
            $parent_term_ids[] = $term->term_id; // (and the child)
        } else {
            $parent_term_ids[] = $term->term_id; // It is the Main category term and we set it.
        }
    }
    return array_intersect( $categories_ids, array_unique($parent_term_ids) ) ? true : false;
}
*/


// Добавление выбора физ. или юр. лицо
add_action( 'woocommerce_before_checkout_billing_form', 'organisation_checkout_field' );
function organisation_checkout_field( $checkout ) {
    echo '<div id="organisation_checkout_field">';
    woocommerce_form_field( 'organisation', array(
        'type'    => 'radio',
        'class'   => array('form-row-wide flex bordered'),
        'label'   =>  '',
	    'options' => array(
			'private_person' => 'Частное лицо',
			'company' => 'Организация'
		)
        ), $checkout->get_value( 'organisation' ));
    echo '</div>';
}
//
	
add_action( 'woocommerce_legal_face', 'my_custom_checkout_field_legal_face' );
function my_custom_checkout_field_legal_face( $checkout ) {
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;

    echo '<div class="woocommerce-organisation-fields__field-wrapper bordered" style="margin-top:0px; padding:30px;"><h3>УКАЖИТЕ РЕКВИЗИТЫ ОРГАНИЗАЦИИ</h3>';

    woocommerce_form_field( 'billing_company', array(
		'required'      => true,
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'   => __('Наименование'),
    ), get_user_meta( $user_id, 'billing_company', true ));
	
	woocommerce_form_field( 'billing_address', array(
		'required'      => true,
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'   => __('Юр.Адрес организации'),
    ), get_user_meta( $user_id, 'billing_address', true ));			
	
	woocommerce_form_field( 'billing_inn', array(
		'required'      => true,
        'type'          => 'text',
        'class'         => array('my-field-class form-row-first'),
        'label'   => __('ИНН'),
    ), get_user_meta( $user_id, 'billing_inn', true ));
	
	woocommerce_form_field( 'billing_kpp', array(
		'required'      => true,
        'type'          => 'text',
        'class'         => array('my-field-class form-row-last'),
        'label'   => __('КПП'),
    ), get_user_meta( $user_id, 'billing_kpp', true ));
	
	woocommerce_form_field( 'organisation_checking_account', array(
		'required'      => true,
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'   => __('Расчетный счет'),
    ), get_user_meta( $user_id, 'organisation_checking_account', true ));
	
	woocommerce_form_field( 'billing_bank', array(
		'required'      => false,
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'   => __('Наименование банка'),
    ), get_user_meta( $user_id, 'billing_bank', true ));	
	
	
	woocommerce_form_field( 'billing_bank_bic', array(
		'required'      => true,
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'   => __('Бик'),
    ), get_user_meta( $user_id, 'billing_bank_bic', true ));

    echo '</div>';
}

//lxxxx

function wpc_elementor_shortcode( $atts ) {
do_action( 'woocommerce_legal_face' );
}
add_shortcode( 'code_php_output', 'wpc_elementor_shortcode');
//



add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');
function my_custom_checkout_field_process() {
	$radioVal = $_POST["organisation"];

	if($radioVal == "company") {
		if ( ! $_POST['billing_company'] ) wc_add_notice( __( '<strong>Наименование организации</strong> является обязательным полем.' ), 'error' );
		if ( ! $_POST['billing_address'] ) wc_add_notice( __( '<strong>Адрес организации</strong> является обязательным полем.' ), 'error' );
		if ( ! $_POST['billing_inn'] ) wc_add_notice( __( '<strong>ИНН</strong> является обязательным полем.' ), 'error' );
		if ( ! $_POST['billing_kpp'] ) wc_add_notice( __( '<strong>КПП</strong> является обязательным полем.' ), 'error' );
		if ( ! $_POST['organisation_checking_account'] ) wc_add_notice( __( '<strong>Расчетный счет</strong> является обязательным полем.' ), 'error' );
		if ( ! $_POST['billing_bank'] ) wc_add_notice( __( '<strong>Банк</strong> является обязательным полем.' ), 'error' );	
		if ( ! $_POST['billing_bank_bic'] ) wc_add_notice( __( '<strong>Банк</strong> является обязательным полем.' ), 'error' );	
	}
}
 
 // Вывести реквизиты в бланке заказа
 
add_action( 'woocommerce_order_details_after_customer_details', 'organisation_checkout_field_echo_in_order' );
function organisation_checkout_field_echo_in_order() {
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$user_id_company = get_user_meta( $user_id, 'company', 'on' );
	if($user_id_company) {
		echo '<h2>Реквизиты компании</h2>';
		echo 'Наименование: '.get_user_meta( $user_id, 'billing_company', true ).'<br>';
		echo 'Адрес: '.get_user_meta( $user_id, 'billing_address', true ).'<br>';
		echo 'ИНН: '.get_user_meta( $user_id, 'billing_inn', true ).'<br>';
		echo 'КПП: '.get_user_meta( $user_id, 'billing_kpp', true ).'<br>';
		echo 'Расч. счет: '.get_user_meta( $user_id, 'organisation_checking_account', true ).'<br>';
		echo 'Банк: '.get_user_meta( $user_id, 'billing_bank', true );	
		echo 'Банк: '.get_user_meta( $user_id, 'billing_bank_bic', true );	
	}
}



// Вывести реквизиты в адмике (в заказе):
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'organisation_checkout_field_echo_in_admin_order', 10 );
function organisation_checkout_field_echo_in_admin_order() {
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$user_id_company = get_user_meta( $user_id, 'company', 'on' );
	if($user_id_company) {
		echo '</div></div><div class="clear"></div>';
		echo '<div class="order_data_column_container"><div class="order_data_column_wide">';
		echo '<h3>Реквизиты компании</h3>';
		echo 'Наименование: '.get_user_meta( $user_id, 'billing_company', true ).'<br>';
		echo 'Адрес: '.get_user_meta( $user_id, 'billing_address', true ).'<br>';
		echo 'ИНН: '.get_user_meta( $user_id, 'billing_inn', true ).'<br>';
		echo 'КПП: '.get_user_meta( $user_id, 'billing_kpp', true ).'<br>';
		echo 'Расч. счет: '.get_user_meta( $user_id, 'organisation_checking_account', true ).'<br>';
		echo 'Банк: '.get_user_meta( $user_id, 'billing_bank', true );
	}
}

// Вывести в Личном кабинете во вкладке Адреса
add_action( 'woocommerce_insert_organisation_details', 'organisation_checkout_field_echo_in_order' );

// Update user meta with field value
 
add_action( 'woocommerce_checkout_update_user_meta', 'my_custom_checkout_field_update_user_meta' );
function my_custom_checkout_field_update_user_meta() {
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;

$radioVal = $_POST["organisation"];
if($radioVal == "company") { update_user_meta( $user_id, 'company', 'on' ); } else { delete_user_meta( $user_id, 'company' ); }

    if ( ! empty( $_POST['billing_company'] ) ) { update_user_meta( $user_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) ); }
    if ( ! empty( $_POST['billing_address'] ) ) { update_user_meta( $user_id, 'billing_address', sanitize_text_field( $_POST['billing_address'] ) ); }
    if ( ! empty( $_POST['billing_inn'] ) ) { update_user_meta( $user_id, 'billing_inn', sanitize_text_field( $_POST['billing_inn'] ) ); }
    if ( ! empty( $_POST['billing_kpp'] ) ) { update_user_meta( $user_id, 'billing_kpp', sanitize_text_field( $_POST['billing_kpp'] ) ); }
    if ( ! empty( $_POST['organisation_checking_account'] ) ) { update_user_meta( $user_id, 'organisation_checking_account', sanitize_text_field( $_POST['organisation_checking_account'] ) ); }
    if ( ! empty( $_POST['billing_bank'] ) ) { update_user_meta( $user_id, 'billing_bank', sanitize_text_field( $_POST['billing_bank'] ) ); }
    if ( ! empty( $_POST['billing_bank_bic'] ) ) { update_user_meta( $user_id, 'billing_bank_bic', sanitize_text_field( $_POST['billing_bank_bic'] ) ); }
}








/************* Минимальное кол-во к заказу вилы = 2 шт ***********
// On single product pages
add_filter( 'woocommerce_quantity_input_args', 'min_qty_filter_callback', 20, 2 );
function min_qty_filter_callback( $args, $product ) {
    $categories = array('forks'); // The targeted product category(ies)
    $min_qty    = 2; // The minimum product quantity

    $product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();

    if( has_term( $categories, 'product_cat', $product_id ) ){
        $args['min_value'] = $min_qty;
    }
    return $args;
}

// On shop and archives pages
add_filter( 'woocommerce_loop_add_to_cart_args', 'min_qty_loop_add_to_cart_args', 10, 2 );
function min_qty_loop_add_to_cart_args( $args, $product ) {
    $categories = array('forks'); // The targeted product category
    $min_qty    = 2; // The minimum product quantity

    $product_id = $product->get_id();

    if( has_term( $categories, 'product_cat', $product_id ) ){
        $args['quantity'] = $min_qty;
    }
    return $args;
}


***/




add_action( 'wp_enqueue_scripts', 'true_no_contact_form_css_and_js', 999 );

function true_no_contact_form_css_and_js() {
 
	wp_dequeue_style( '/wp-content/plugins/advanced-woo-search/assets/css/common.min.css' );
	wp_dequeue_style( 'aws-style' );
 
}


add_action( 'wp_enqueue_scripts', 'aws_custom_frontend', 25 );
 
function aws_custom_frontend() {
 	wp_enqueue_style( 'aws-custom', get_stylesheet_directory_uri() . '/aws-custom.css' );
}



function print_styles() {
    ?>
 <style type="text/css" media="print">

.woocommerce-product-details__short-description, p.stock.in-stock.wd-style-default,
.shop_attributes :is(th,td), .wd-accordion-title-text>span, .adv-grid-container p {
    font-size: 150% !important;
    color: black !important;
}
.footer-column.footer-column-1, form.cart, .wd-action-btn.wd-style-text, .container.related-and-upsells,
div#tab-item-title-wd_additional_tab, .wd-gallery-thumb, .single-breadcrumbs-wrapper,
.whb-row.whb-header-bottom, .whb-column.whb-col-center, .whb-column.whb-col-right,
.scrollToTop.button-show,
footer.footer-container h2  {
    display: none;
}
footer.footer-container {
    margin-top: auto;
    background-color: #f4f6f9;
    border-top: 2px solid black;
	padding-bottom: 20px;
}
.website-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
footer.footer-container p{
    color: #000;
    font-size: 20px;
}
.row.product-image-summary-wrap {
    margin-right: -80px;
    margin-left: -80px;
}
p.stock.in-stock.wd-style-default {
    padding-bottom: 20px;
}
div#tab-wd_additional_tab {
    display: none !important;
}
svg.wp-block-woocommerce-product-shipping-dimensions-fields__dimensions-image {
    margin: 25px 0;
}
.footer-column.footer-column-2.col-12.col-sm-6, .footer-column.footer-column-2.col-12.col-sm-6 p{
    max-width: 100%;
	min-width: 100%;
	margin-bottom: 0;
}

.shop_attributes th {
    background-color: unset;
}
.shop_
.wd-carousel-wrap {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.container, .container-fluid {
    padding-right: 0;
    padding-left: 0;
}
.price, 
.amount {
    font-size: 200% !important;
    color: var(--wd-entities-title-color);
}
span.wd-price-unit,
small.woocommerce-price-suffix {
    font-size: 110%;
}
.elementor-element-3c16365 {
    transform: scale(2);
    transform-origin: left;
}
.whb-general-header-inner {
    height: 124px !important;
    max-height: 124px !important;
}
.col-lg-6.col-12.col-md-6.text-left.summary.entry-summary {
    max-width: 60%;
    width: 60%;
    flex: 0 0 60%;
}
.col-lg-6.col-12.col-md-6.product-images {
    max-width: 40%;
    flex: 0 0 40%;
}
svg path {
    stroke: black;
}
svg text {
    fill: black;
}
.wd-accordion.wd-style-default .wd-accordion-item {
    border-bottom: none;
}
.tabs-location-summary .tabs-layout-accordion {
    width: calc(100% + 500px);
    margin-left: -500px;
    display: flex;
    justify-content: flex-end;
	flex-wrap: wrap;
}
.wd-accordion-item:last-child {
    width: 60% !important;
}
</style>
    <?php
}
add_action( 'wp_head', 'print_styles' );