<?php

namespace Biatech\Lazev\Factories;

defined( 'ABSPATH' ) || exit;



final class FactoryModalAdmin
{
    public int $order_id;
    
    public array $contacts;
    
    public array $status;
    
    public array $loadParams;
    
    public array $otherParams;
    
    
    
    public function __construct(int $order_id, array $contacts,
                                array $status, array $loadParams, array $otherParams)
    {
        $this->order_id = $order_id;
        $this->contacts = $contacts;
        $this->status = $status;
        $this->loadParams = $loadParams;
        $this->otherParams = $otherParams;
    }
    

    public static function getRowInModal($rowTitle, $rowData){
    
            $html = '<div class="dellin__modal_rows-item">';
            $html.= $rowTitle.'<strong>'.$rowData.'</strong>'.'</div>';
            $allowed_tags = ['div' => [
                'class' => []
                                      ],
                            'strong' => [],
                ];
            echo wp_kses($html, $allowed_tags);
    
        }

public function create(): void
    {
        


?>
     <div id="dellinDetailModal" style="display:none;">
         <div id="dellin_modal">
             <div id="container">
                 <div class="dellin__modal_header" style='width:100%'>
                     <h3 class="dellin__modal_header-title">
                    Подробные сведения по доставке "Деловые линии". Заказ №:
                    <?php
                   echo esc_html($this->order_id);
                    ?>
                     </h3>
                 </div>
                 <div class='dellin__modal_rows'>
                    <?php
                    foreach ($this->status as $value){
                        self::getRowInModal(esc_html($value[0]), esc_html($value[1]));
                    }
                    ?>
                     <hr/>
                 </div>
                 <div class='dellin__modal_rows'>
                    <?php
                        foreach ($this->contacts as $value){
                            self::getRowInModal(esc_html($value[0]), esc_html($value[1]));
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
                        foreach ($this->loadParams as $value){
                            self::getRowInModal(esc_html($value[0]), esc_html($value[1]));
                        }
                    ?>
                     </div>
                 </div>
                 <hr/>
                 <div>
                    <?php
                        foreach ($this->otherParams as $value){
                            self::getRowInModal(esc_html($value[0]), esc_html($value[1]));
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
<?php
        
    }
    
    

    
}