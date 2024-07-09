<?php
/**
 * Компоновщик массивов.
 * Объектно-ориентированное представление работы с массивами.
 * Этот объект создаёт поле для последущего использование внутри контейнера или других
 * сущностей имплементированные от интерфейса CompositeInterface.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */
namespace Biatech\Lazev\Base\Composite;


class Field implements CompositeInterface
{

    private string $key;

    private $value;

    public array $text;

    const UNCORRECT_OBJECT = 'Некорректный объект композиции';

    /**
     * Конструктор класса.
     * @param array $data
     * @throws \Exception
     */

    public function __construct(array $data)
    {
        $this->key = $data[0];
        (is_object($data[1]))?$this->setValueObj($data[1]):$this->value = $data[1];
    }

    /**
         * Метод осуществляющий проверку входящих данных.
         * @param $data
         * @throws \Exception
         */
    private function setValueObj($data):void
    {

        if(!method_exists($data, 'toArray'))
            throw new \Exception(self::UNCORRECT_OBJECT);

        $this->value = $data->toArray();

    }


    /**
         * Обязательный метод приводящий значения из объекта к массиву.
         * @return array
         */
    public function toArray(): array
    {
        return $this->text = [$this->key => $this->value];
    }
}