<?php
declare(strict_types=1);

namespace Biatech\Lazev\DTOs;

use Biatech\Lazev\ValueObjects\Counteragent;

final class AuthDTOFields {
   
     /**
      * Ключ приложения. Для получения ключа необходимо пройти регистрацию на сайте dellin.ru
      * @var string
      */
     public ?string $appkey;
     /**
      * Логин от Личного кабинета.
      * В качестве логина можно использовать как email, так и номер телефона.
      * Формат номера телефона: "+7XXXXXXXXXX" - 12 символов, начиная с "+7"
      * @var string
      */
     public ?string $login;
     /**
      * Пароль от Личного кабинета на dellin.ru
      * @var string
      */
     public ?string $password;


     public function __construct(?string $appkey, ?string $login, ?string $password)
     {
        $this->appkey = $appkey;
        $this->login = $login;
        $this->password = $password;
     }
}

