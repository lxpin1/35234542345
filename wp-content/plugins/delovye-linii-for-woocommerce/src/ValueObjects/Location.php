<?php
declare(strict_types=1);


/**
 * ValueObject\Location - структура определения местоположения автоматизированным способом;
 *
 */

namespace Biatech\Lazev\ValueObjects;

final class Location
{
    /**
     * Кладр ФИАС, может отличаться от официальной базы ФИАС, т.к. в ГК ДЛ продолжают
     * использовать классификатор, который не развивается.
     * Берётся из сервисного слоя работы с местоположениями, или записывается с фронта
     * (фронт в свою очередь берёт их из сервисного слоя работы с местоположениями)
     *
     */
    public ?string $kladr_city;
    /**
     * Координаты записанные строкой для inline ввода.
     * Пример: 59.924501, 30.241762
     */
    public ?string $coordinates_inline;
    
    /**
     * Адрес инлайновой строкой рекомендуем брать данные Яндекса или сервисов dadata.
     * Пример: 620027, Россия, Свердловская обл, Екатеринбург, ул. Шевченко, д.1
     */
    public ?string $address_inline;

    /**
     * Идентификатор структурного подразделения ГК ДЛ работающий на приём или выдачу груза
     */
    public ?string $terminal_id;
    
    

    public function  __construct(?string $address_inline = null, ?string $kladr_city = null,
                                 ?string $coordinates_inline = null, ?string $terminal_id = null)
    {
        $this->setAddressInline($address_inline);
        $this->setKladrCity($kladr_city);
        $this->setCoordinatesInline($coordinates_inline);
        $this->setTerminalId($terminal_id);
    }
    
    /**
     * @param string|null $address_inline
     */
    public function setAddressInline(?string $address_inline): void
    {
        $this->address_inline = $address_inline;
    }

    /**
     * @param string|null $coordinates_inline
     */
    public function setCoordinatesInline(?string $coordinates_inline): void
    {
        $this->coordinates_inline = $coordinates_inline;
    }
    /**
     * @param string|null $kladr_city
     */
    public function setKladrCity(?string $kladr_city): void
    {
        $this->kladr_city = $kladr_city;
    }

    /**
     * @param string|null $terminal_id
     */
    public function setTerminalId(?string $terminal_id): void
    {
        $this->terminal_id = $terminal_id;
    }

    public function buildLocation()
    {

    }
}