<?php

defined( 'ABSPATH' ) || exit;


?>
     <div id="dellinDetailModal" style="display:none;">
        <div id="dellin_modal">
            <div id="container">
                <div class="dellin__modal_header" style='width:100%'>
                    <h3 class="dellin__modal_header-title">
                    Подробные сведения по доставке "Деловые линии". Заказ №:
                    <?php
                   echo esc_html($post->ID);
                    ?>
                    </h3>
                </div>
                <div class='dellin__modal_rows'>
                    <?php
                    foreach ($arRowsStatus as $value){
                        DellinShipping_Admin::getRowInModal(esc_html($value[0]), esc_html($value[1]));
                    }
                    ?>
                    <hr/>
                </div>
                <div class='dellin__modal_rows'>
                    <?php
                        foreach ($arRowsContact as $value){
                            DellinShipping_Admin::getRowInModal(esc_html($value[0]), esc_html($value[1]));
                        }
                    ?>
                    <hr/>
                </div>
                <div class='dellin__modal_rows-item'>
                    <strong>
                    Дополнительные параметры загрузки:
                    </strong>
                    <div class='dellin__modal_rows-itemlist'>
                    <?php
                        foreach ($arRowsLoad as $value){
                            DellinShipping_Admin::getRowInModal(esc_html($value[0]), esc_html($value[1]));
                        }
                    ?>
                    </div>
                </div>
                <hr/>
                <div class='dellin__modal_rows-item'>
                    <strong>
                    Дополнительные параметры выгрузки:
                    </strong>
                    <div class='dellin__modal_rows-itemlist'>
                    <?php
                        foreach ($arRowsLoad as $value){
                            DellinShipping_Admin::getRowInModal(esc_html($value[0]), esc_html($value[1]));
                        }
                    ?>
                    </div>


                </div>
                <hr/>
                <div>
                    <?php
                        foreach ($arRowsOther as $value){
                            DellinShipping_Admin::getRowInModal(esc_html($value[0]), esc_html($value[1]));
                        }
                    ?>
                </div>
                <a id="dellinSendRequest">
            <button type="button" class="thickbox button button-primary calculate-action" style="margin: 15px;"><?php echo esc_html( 'Создать заявку'); ?></button>
            </a>
            </div>
        </div>
    </div>
<div id='postcustomstuff'>
    <table id="list-table" style="text-align: center;">
        <thead>
        <tr>
            <th><?php
				echo esc_html( 'Трек номер' );
				?></th>
            <th><?php
                echo esc_html( 'Статус');
				?></th>
            <th><?php
                echo esc_html( 'Действие' );
				?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
				<?php
                echo esc_html('Трек номер отсутствует'); ?>
            </td>
            <td><?php

                echo esc_html( 'Статус не опеределён'); ?>

            </td>
            <td>
                <a href="/?TB_inline&width=600&height=550&inlineId=dellinDetailModal" class="thickbox">
                    <button type="button" class="thickbox button button-primary calculate-action"><?php echo esc_html( 'Создать заявку'); ?></button>
                </a>
            </td>
        </tr>
        </tbody>
    </table>
</div>





    </div>
