<?php

namespace RPI\Framework\Form\Validator;

class Standard extends RegularExpression
{
    private $regex_type;

    public function __construct($type, array $buttons = null)
    {
        $this->regex_type = $type;

        switch ($this->regex_type) {
            case \RPI\Framework\Form\Validator\Standard\Type::EMAIL:
                parent::__construct(
                    "/^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|".
                    "([A-Za-z0-9]+\++))*[A-Za-z0-9_]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/",
                    \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.standard.email"),
                    $buttons
                );
                break;
            case \RPI\Framework\Form\Validator\Standard\Type::TELEPHONE_NUMBER_UK:
                parent::__construct(
                    "/^\s*\(?(0\s*[2]{1}\s*[0]{1}\s*[7,8]{1}\s*\)?[1-9]{1}\s*\d\s*\d\s*\d\s*\d\s*\d\s*\d\s*)|".
                    "([0]{1}\s*[1-8]{1}\s*\d\s*\d\s*\d\s*\)?\s*\d\s*\d\s*\d\s*\d\s*\d\s*\d\s*)\s*$/",
                    \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.standard.telephoneNumberUK"),
                    $buttons
                );
                break;
            case \RPI\Framework\Form\Validator\Standard\Type::TELEPHONE_NUMBER_MOBILE_UK:
                parent::__construct(
                    "/^\s*(\+\s*4\s*4\s*?7\s*\d\s*\d\s*\d\s*|\(?0\s*7\s*\d\s*\d\s*\d\s*\)?)".
                    "\d\s*\d\s*\d\s*\d\s*\d\s*\d\s*$/",
                    \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.standard.telephoneNumberMobileUK"),
                    $buttons
                );
                break;
            case \RPI\Framework\Form\Validator\Standard\Type::CURRENCY:
                parent::__construct(
                    "/^\d+(?:\.\d{0,2})?$/",
                    \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.standard.currency"),
                    $buttons
                );
                break;
            case \RPI\Framework\Form\Validator\Standard\Type::PASSWORD:
                parent::__construct(
                    "/^\w{6,20}$/",
                    \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.standard.password"),
                    $buttons
                );
                break;
            case \RPI\Framework\Form\Validator\Standard\Type::POSTCODE_UK:
                parent::__construct(
                    "/^([a-pr-uwyzA-PR-UWYZ]\d\d?\s*\d[abd-hjlnp-uw-zABD-HJLNP-UW-Z]{2}|".
                    "[a-pr-uwyzA-PR-UWYZ][a-hk-yA-HK-Y]\d\d?\s*\d[abd-hjlnp-uw-zABD-HJLNP-UW-Z]{2}|".
                    "[a-pr-uwyzA-PR-UWYZ]\d\s*[a-hjkstuwA-HJKSTUW]\d[abd-hjlnp-uw-zABD-HJLNP-UW-Z]{2}".
                    "|[a-pr-uwyzA-PR-UWYZ][a-hk-yA-HK-Y]\d[a-hjkrstuwA-HJKRSTUW]\d\s*".
                    "[abd-hjlnp-uw-zABD-HJLNP-UW-Z]{2}|GIR0AA)$/",
                    \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.standard.postcodeUK"),
                    $buttons
                );
                break;
            case \RPI\Framework\Form\Validator\Standard\Type::CARD_NAME:
                parent::__construct(
                    "/^[A-Za-z ]{4,40}$/",
                    \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.standard.creditName"),
                    $buttons
                );
                break;
            case \RPI\Framework\Form\Validator\Standard\Type::CREDIT_CARD:
                parent::__construct(
                    "/^(\d{6}[-\s]?\d{12})$|^(\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{3})".
                    "$|^(\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4})$|^3[4,7]\d{13}|[\*]{12}\d{4}$/",
                    \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.standard.creditCard"),
                    $buttons
                );
                break;
        }
    }
}
