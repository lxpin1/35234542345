<?php
/*
Plugin Name: LX Telegram Button
Description: Добавляет кнопку Телеграм для связи с ником @lxp1n
Version: 1.1
Author: Ваше Имя
*/

function lx_tlg_btn_shortcode() {
    global $wp;
    // Получаем текущий URL страницы
    $current_url = esc_url(home_url(add_query_arg(array(), $wp->request)));

    // Проверяем, является ли это страницей товара
    if (is_product()) {
        $message = "Здравствуйте, есть вопрос по этому товару.";
    } else {
        $message = "У меня возник вопрос.";
    }

    // Создаем HTML код кнопки
    $button_html = '<a href="https://t.me/lxp1n?text=' . urlencode($current_url . ' ' . $message) . '" target="_blank" class="lx-telegram-button">';
    $button_html .= '<img src="https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg" alt="Telegram" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;">';
    $button_html .= 'Есть вопрос?</a>';

    return $button_html;
}
add_shortcode('lx_tlg_btn', 'lx_tlg_btn_shortcode');

// Добавляем стили для кнопки
function lx_tlg_btn_styles() {
    echo '<style>
    .lx-telegram-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #f4f6f9;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s;
		max-width: fit-content;
		border: 1px solid gainsboro;
		margin: -1px;
		font-weight: bold;
		color: gray;
    }
    .lx-telegram-button:hover {
        background-color: #d7d7d7;
		color: gray;
    }
    </style>';
}
add_action('wp_head', 'lx_tlg_btn_styles');

// Функция для добавления кнопки после кнопки "Купить" на странице товара
function lx_add_telegram_button_after_add_to_cart() {
    echo do_shortcode('[lx_tlg_btn]');
}
add_action('woocommerce_after_add_to_cart_button', 'lx_add_telegram_button_after_add_to_cart');