<?php

defined( 'ABSPATH' ) || exit;

?>

<div id='postcustomstuff'>
    <table id="list-table" style="text-align: center;">
        <thead>
        <tr>
            <th><?php
               echo esc_html( 'Трек номер' );
                ?></th>
            <th><?php
               echo esc_html( 'Статус' );
                ?></th>
            <th><?php
              echo  esc_html( 'Действие' );
                ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
	            <?php
	            echo esc_html(($trackId == '')?'Заявка не создана': $trackId);  ?>
            </td>
            <td><?php

	            echo esc_html( $trackStatus);  ?>

            </td>
            <td>

            </td>
        </tr>
        </tbody>
    </table>
</div>

