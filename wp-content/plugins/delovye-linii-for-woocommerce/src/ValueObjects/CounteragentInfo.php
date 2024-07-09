<?php

declare(strict_types=1);


namespace Biatech\Lazev\ValueObjects;

use Biatech\Lazev\DTOs\YuriInfoDTO;
use Biatech\Lazev\DTOs\PersonDTO;
use \ArrayObject;

final class CounteragentInfo
{
    public ?string $opf_form;
    public bool $is_yuri;
    public ?string $opf_country;
    public ?bool $is_anonymous;
    public bool $is_payer;
    public ?ArrayObject $contacts_info;
    public string $role;
    public ?YuriInfoDTO $yuri_info;
    
    public ?string $uid;
    

    public function __construct(string $opf_form, bool $is_yuri, bool $is_payer,
                                ?string $opf_country, string $role, ?string $uid, $is_anonymous = false)
    {
        $this->contacts_info = new ArrayObject([]);
        $this->opf_form = $opf_form;
        $this->opf_country = $opf_country;
        $this->is_yuri = $is_yuri;
        $this->role = $role;
        $this->is_payer = $is_payer;
        $this->uid = $uid;
        $this->is_anonymous = $is_anonymous;
    }

    public function add_contact_person(PersonDTO $person): void
    {
        $this->contacts_info->append($person);
    }

    public function set_yuri_info(?YuriInfoDTO $yuri_info): void
    {
        $this->yuri_info = $yuri_info;
    }




}