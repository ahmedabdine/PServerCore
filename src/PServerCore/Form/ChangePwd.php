<?php

namespace PServerCore\Form;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;

class ChangePwd extends ProvidesEventsForm
{

    public function __construct()
    {
        parent::__construct();

        $this->add([
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'eugzhoe45gh3o49ug2wrtu7gz50'
        ]);

        $this->add([
            'name' => 'currentPassword',
            'options' => [
                'label' => 'Current Web Password',
            ],
            'attributes' => [
                'class' => 'form-control',
                'type' => 'password',
                'placeholder' => 'Current Web Password',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'password',
            'options' => [
                'label' => 'New Password',
            ],
            'attributes' => [
                'class' => 'form-control',
                'type' => 'password',
                'placeholder' => 'New Password',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'passwordVerify',
            'options' => [
                'label' => 'Confirm new Password',
            ],
            'attributes' => [
                'class' => 'form-control',
                'type' => 'password',
                'placeholder' => 'Confirm new Password',
                'required' => true,
            ],
        ]);

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel('Change Password')
            ->setAttributes([
                'class' => 'btn btn-primary',
                'type' => 'submit',
            ]);

        $this->add($submitElement, [
            'priority' => -100,
        ]);
    }

    /**
     * @param $which
     * @return $this
     */
    public function setWhich($which)
    {
        $hidden = new Element\Hidden('which');
        $hidden->setLabel(' ');
        $hidden->setValue($which);
        $this->add($hidden);

        return $this;
    }
}



