<?php

declare(strict_types=1);

namespace Biatech\Lazev\DTOs;

final class PackagesSettingsDTO
{

        //Деревянная обрешётка
        public bool $packingForGoodsCrate = false;
        //Жёсткий короб
        public bool $packingForGoodsCratePlus = false;
        //Картонные коробки
        public bool $packingForGoodsBox = false;
        //Дополнительная упаковка
        public bool $packingForGoodsType = false;
        //Деревянная обрешётка + амортизация
        public bool $packingForGoodsCrateWithBuble = false;
        //спец. упаковка для автостёкл
        public bool $packingForGoodsCarGlass = false;
        //спец. упаковка для автозапчастей
        public bool $packingForGoodsCarParts = false;
        //Палетный борт + амортизация
        public bool $packingForGoodsPalletWithBubble = false;
        //Мешок
        public bool $packingForGoodsBag = false;
        //Воздушно-пузырьковая плёнка
        public bool $packingForGoodsBubble = false;
        //Палетный борт
        public bool $packingForGoodsPallet = false;

        // FIXME change implements work on packages

        public function __construct(
                bool $packingForGoodsCrate, bool $packingForGoodsCratePlus,
                bool $packingForGoodsBox, bool $packingForGoodsType,
                bool $packingForGoodsCrateWithBuble, bool $packingForGoodsCarGlass,
                bool $packingForGoodsCarParts, bool $packingForGoodsPalletWithBubble,
                bool $packingForGoodsBag, bool $packingForGoodsBubble,
                bool $packingForGoodsPallet
        )
        {
                $this->packingForGoodsCrate = $packingForGoodsCrate;
                $this->packingForGoodsCratePlus = $packingForGoodsCratePlus;
                $this->packingForGoodsBox = $packingForGoodsBox;
                $this->packingForGoodsType = $packingForGoodsType;
                $this->packingForGoodsCrateWithBuble = $packingForGoodsCrateWithBuble;
                $this->packingForGoodsCarGlass = $packingForGoodsCarGlass;
                $this->packingForGoodsCarParts = $packingForGoodsCarParts;
                $this->packingForGoodsPalletWithBubble = $packingForGoodsPalletWithBubble;
                $this->packingForGoodsBag = $packingForGoodsBag;
                $this->packingForGoodsBubble = $packingForGoodsBubble;
                $this->packingForGoodsPallet = $packingForGoodsPallet;
        }
    
}