<?php

defined( 'ABSPATH' ) || exit;

?>

<section class="woocommerce-order-details">
    <h2 class="woocommerce-order-details__title">Информация о доставке</h2>
    <table class="woocommerce-table">
        <tbody>
        <tr>
            <th>Ваш метод доставки:</th>
            <td>Деловые линии</td>
        </tr>
        <tr>
            <th>Адрес доставки/Терминала:</th>
            <td><?php echo esc_html($fullAddress); ?></td>
        </tr>
        <?php
        if ($typeShipping == 'term'):
            ?>
            <tr>
                <th>Время работы терминала:</th>
                <td><?php echo esc_html($terminal['terminal']->calcSchedule->arrival); ?></td>
            </tr>
        <?php endif ?>
        <tr>
            <th>Тип доставки:</th>
            <td><?php ($typeShipping == 'term')? print(esc_html("до терминала")): print(esc_html('до адреса'));?></td>
        </tr>
        <tr>
            <th>Трек номер:</th>
            <td><?php ($trackId == '')? print(esc_html('Заявка не создана')) : print(esc_html($trackId)) ;  ?></td>
        </tr>
        <tr>
            <th>Статус доставки:</th>
            <td><?php ($trackId == '')? print(esc_html('Заявка не создана')) :print(esc_html($trackStatus)); ?></td>
        </tr>
        </tbody>
    </table>
</section>
