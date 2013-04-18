<?php

namespace RPI\Framework\Form\Validator\Standard;

/**
 * Regular expression types
 */
final class Type
{
    /**
     * Email regular expression
     */
    const EMAIL = 0;

    /**
     * Valid telephone number. Must be all numbers or spaces and begin with a zero
     */
    const TELEPHONE_NUMBER_UK = 1;

    /**
     * Valid mobile telephone number. Must be all numbers or spaces and begin with a zero
     */
    const TELEPHONE_NUMBER_MOBILE_UK = 2;

    /**
     * Currency
     */
    const CURRENCY = 3;

    /**
     * Password, Only characters and numbers between 6 and 10 characters long
     */
    const PASSWORD = 4;

    /**
     * Valid UK postcode
     */
    const POSTCODE_UK = 5;

    /**
     * Credit card name - should be letters only
     */
    const CARD_NAME = 6;

    /**
     * Credit card
     */
    const CREDIT_CARD = 7;

    private function __construct()
    {
    }
}
