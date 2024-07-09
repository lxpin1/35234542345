<?php

namespace Biatech\Lazev\Factories;




final class FactoryMetaboxStatus
{

    public ?string $track_id;
    public ?string $status;
    
    public function __construct(?string $track_id, ?string $status)
    {
        $this->track_id = $track_id;
        $this->status = $status;
    }

    public function create()
    {

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
    	            echo esc_html(($this->track_id == '')?'Заявка не создана': $this->track_id);  ?>
                    </td>
                    <td><?php
    
    	            echo esc_html( $this->status);  ?>
    
                    </td>
                    <td>
    
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php

    }
    
    
    
    

}