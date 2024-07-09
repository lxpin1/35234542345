<?php

namespace Biatech\Lazev\Factories;


use Biatech\Lazev\Base\Composite\Container;
use Biatech\Lazev\Helpers\RolesMembers;
use Biatech\Lazev\ValueObjects\CounteragentInfo;
use Biatech\Lazev\Base\Composite\Field;

final class FactoryMembersInPayload
{
    public array $counteragents;
    const CONTEXT_TYPE = ['calc', 'request'];

    public ?CounteragentInfo $sender;
    public ?CounteragentInfo $receiver;
    public ?CounteragentInfo $requester;
    public ?CounteragentInfo $third;
    public ?CounteragentInfo $payer;

    public function __construct()
    {
        $this->sender = null;
        $this->receiver = null;
        $this->requester = null;
        $this->third = null;
        $this->payer = null;

    }

    public function create(array $counteragents, string $context): Container
    {

        $this->counteragents = $counteragents;
        
        $this->setFieldCounteragents();
        
        if($context == self::CONTEXT_TYPE[0])
        {
            return $this->buildMembersForCalc();
        }    

        if($context == self::CONTEXT_TYPE[1])
        {
            //TODO реализовать для заказа
            return $this->buildMembersForRequest();
        }    
        

    }

    private function setFieldCounteragents()
    {
        foreach ($this->counteragents as $counteragent) {
            if ($counteragent->role == RolesMembers::SENDER) {
                $this->sender = $counteragent;
            }
            if ($counteragent->role == RolesMembers::RECEIVER) {
                $this->receiver = $counteragent;
            }
            if ($counteragent->role == RolesMembers::THIRD) {
                $this->third = $counteragent;
            }
            if ($counteragent->is_payer) {
                $this->payer = $counteragent;
            }

            if (isset($counteragent->uid)) {
                $this->requester = $counteragent;
            }


        }

    }

    public function buildMembersForRequest(): Container
    {
        $members = new Container();

        $requester = new Field(['requester', $this->getMembersRequester()]);
        $sender = new Field(['sender', $this->getMembersSender()]);
        $receiver = new Field(['receiver', $this->getMembersReceiver()]);
        $members->add($requester);
        $members->add($sender);
        $members->add($receiver);

        return $members;
    }

    public function buildMembersForCalc(): Container
    {
        $members = new Container();

        $requester = new Field(['requester', $this->getMembersRequester()]);
        $sender = new Field(['sender', $this->getMembersSender()]);
        $members->add($requester);
        $members->add($sender);
        
        return $members;

    }

    public function getEmailRequester()
    {

        $mail = '';
        foreach ($this->requester->contacts_info as $key => $contact)
        {
            if(isset($contact->email))
            {
                $mail = $contact->email;
            }
        }
        return $mail;
    }
    
    
    public function getMembersReceiver(): Container
    {
        $container = new Container();
    
        $fieldCounteragent = new Field(['counteragent', $this->getMembersReceiverCounteragent()]);
        $fieldContactPerson = new Field(['contactPersons', [
                                                        ['name' => $this->receiver->contacts_info[0]->name,
                                                         'save' => false]
                                                            ]
                                            ]);
        $fieldPhoneNumbers = new Field(['phoneNumbers', [['number'=> $this->receiver->contacts_info[0]->phone]]]);
        $fieldEmail = new Field(['email', $this->receiver->contacts_info[0]->email]);
        $fieldDataForReceipt = new Field(['dataForReceipt', ['send' => false]]);
    
        $container->add($fieldCounteragent);
        $container->add($fieldContactPerson);
        $container->add($fieldPhoneNumbers);
        $container->add($fieldEmail);
        $container->add($fieldDataForReceipt);
    
        return $container;
    
    }
    
    public function getMembersSender(): Container
    {
        $containerSender = new Container();

        if(!isset($this->sender))
        {
            throw new \Exception('Информация об отправителе пуста');
        }

        $fieldCounteragent = new Field(['counteragent', $this->getMemberSenderCounteragentContainer()]);
        $fieldContactPerson = new Field(['contactPersons', [
                                 ['name' => $this->sender->contacts_info[0]->name,
                                                     'save' => false]
                                                    ]
            ]);
        $fieldPhoneNumbers = new Field(['phoneNumbers', [['number'=> $this->sender->contacts_info[0]->phone]]]);


        $containerSender->add($fieldCounteragent);
        $containerSender->add($fieldContactPerson);
        $containerSender->add($fieldPhoneNumbers);


        return $containerSender;
    }
    

    public function getMembersRequester():Container
    {
        $containerRequester = new Container();

        if(!isset($this->requester))
        {
            throw new \Exception('Информация о заказчике пуста');
        }

        $fieldRole = new Field(['role', $this->requester->role]);
        $fieldEmail = new Field(['email', $this->getEmailRequester()]);
        $fieldUID = new Field(['uid', $this->requester->uid]);

        $containerRequester->add($fieldRole);
        $containerRequester->add($fieldEmail);
        (isset($this->requester->uid)) ? $containerRequester->add($fieldUID) : '';

        return $containerRequester;
    }
    
    public function getMemberSenderCounteragentContainer(): Container
    {
        $containerCounteragent = new Container();

        $fieldForm = new Field(['form', $this->sender->opf_form]);
        $fieldName = new Field(['name', $this->sender->yuri_info->yuri_name]);
        $fieldINN = new Field(['inn', $this->sender->yuri_info->yuri_inn]);
        $fieldIsAnonim = new Field(['isAnonym', false]);

        $fieldYuriAddress = new Field(['juridicalAddress', ['search' => $this->sender->yuri_info->yuri_address]]);

        $containerCounteragent->add($fieldForm);
        $containerCounteragent->add($fieldName);
        $containerCounteragent->add($fieldINN);
        $containerCounteragent->add($fieldIsAnonim);
        $containerCounteragent->add($fieldYuriAddress);

        return $containerCounteragent;
    }
    
    public function getMembersReceiverCounteragent(): Container
    {

        $containerCounteragent = new Container();

        //TODO переработать под Юриков тоже
      //модуль woocommerce юриков не поддерживает.

        $fieldForm = new Field(['form', '0xab91feea04f6d4ad48df42161b6c2e7a']);
        $fieldName = new Field(['name', $this->receiver->contacts_info[0]->name]);
        $fieldPhone = new Field(['phone', $this->receiver->contacts_info[0]->phone]);
        $fieldIsAnonim = new Field(['isAnonym', true]);

        // $fieldYuriAddress = new Field(['juridicalAddress', ['search' => $this->config->getSenderJuridicalAddress()]]);

        $containerCounteragent->add($fieldForm);
        $containerCounteragent->add($fieldName);
        $containerCounteragent->add($fieldPhone);
        $containerCounteragent->add($fieldIsAnonim);
        //   $containerCounteragent->add($fieldYuriAddress);

        return $containerCounteragent;

    }
    
    


}