<?php

namespace Biatech\Lazev\Factories;




final class FactoryProfileMetaData
{

    public ?int $track_id;
    public ?string $fullAddress;
    public ?int $terminal_id;
    public ?string $status;
    public ?string $deliveryType;
    
    public ?array $terminalInfo;
    
    
    public function __construct(?int $track_id, ?string $fullAddress, ?int $terminal_id,
                                ?string $status, ?string $deliveryType, ?array $terminalInfo)
    {
        $this->track_id = $track_id;
        $this->fullAddress = $fullAddress;
        $this->terminal_id = $terminal_id;
        $this->status = $status;
        $this->deliveryType = $deliveryType;
        $this->terminalInfo = $terminalInfo;
        
    }

    public function create()
    {

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
                        <td><?php echo esc_html($this->fullAddress); ?></td>
                    </tr>
                <?php
                if ($this->terminal_id):
                    ?>
                    <tr>
                        <th>Время работы терминала:</th>
                        <td><?php echo esc_html($this->terminalInfo['terminal']->calcSchedule->arrival); ?></td>
                    </tr>
                <?php endif ?>
                    <tr>
                        <th>Тип доставки:</th>
                        <td><?php echo esc_html($this->deliveryType); ?></td>
                    </tr>
                    <tr>
                        <th>Трек номер:</th>
                        <td><?php !isset($this->track_id)? print(esc_html('Заявка не создана')) : print(esc_html($this->track_id)) ;  ?></td>
                    </tr>
                    <tr>
                        <th>Статус доставки:</th>
                        <td><?php !isset($this->track_id)? print(esc_html('Заявка не создана')) :print(esc_html($this->status)); ?></td>
                    </tr>
                </tbody>
            </table>
        </section>
        <?php

    }
}