<?php

namespace RPI\Framework\Form\FormItem;

class Date extends \RPI\Framework\Form\FormItem\Input
{
    public $format = "dd-mm-yyyy";
    private $dateValidator = null;

    public function __construct($id, $displayText, array $args = null, \RPI\Framework\Form\Button $defaultButton = null)
    {
        parent::__construct($id, $displayText, $args, $defaultButton);

        $this->maxLength = \RPI\Framework\Helpers\Utils::getNamedValue($args, "maxLength", 10);
        $this->isMultiLine = false;

        $this->format = \RPI\Framework\App\Locale::getDateFormat();

        $this->format = str_replace("/", "-", $this->format);

        $this->elementClassName = "c-calendar c-calendar-format-".$this->format;

        $formatParts = explode("-", $this->format);
        $regEx = "";
        for ($i = 0; $i < count($formatParts); $i++) {
            $regEx .= "\d{1,".strlen($formatParts[$i])."}";
            if ($i < count($formatParts) - 1) {
                $regEx .= "[-\/\.]{1}";
            }
        }
        $this->dateValidator = new \RPI\Framework\Form\Validator\RegularExpression(
            "/^$regEx$/",
            \RPI\Framework\Facade::localisation()->t("rpi.framework.form.validator.date")
        );
        $this->addValidator($this->dateValidator);
    }

    public function addValidator(\RPI\Framework\Form\Validator $validator)
    {
        if (isset($validator->buttons)) {
            $this->dateValidator->buttons = $validator->buttons;
        }

        return parent::addValidator($validator);
    }

    public function __get($key)
    {
        if ($key == "date") {
            $value = preg_replace("/[-\/\.]{1}/", "-", $this->value);

            $format = str_replace("dd", "%d", $this->format);
            $format = str_replace("mm", "%m", $format);
            $format = str_replace("yyyy", "%Y", $format);
            $format = str_replace("yy", "%y", $format);

            $ftime = strptime($value, $format);
            if ($ftime !== false) {
                $timestamp = mktime(
                    $ftime['tm_hour'],
                    $ftime['tm_min'],
                    $ftime['tm_sec'],
                    $ftime['tm_mon'] + 1,
                    $ftime['tm_mday'],
                    $ftime['tm_year'] + 1900
                );

                return new \DateTime(date("c", $timestamp));
            } else {
                $ftime = strptime($value, str_replace("%Y", "%y", $format));
                if ($ftime !== false) {
                    $timestamp = mktime(
                        $ftime['tm_hour'],
                        $ftime['tm_min'],
                        $ftime['tm_sec'],
                        $ftime['tm_mon'] + 1,
                        $ftime['tm_mday'],
                        $ftime['tm_year'] + 1900
                    );

                    return new \DateTime(date("c", $timestamp));
                }

                return false;
            }
        } else {
            return parent::__get($key);
        }
    }

    public function getValue()
    {
        return $this->date;
    }

    public function setValue($value)
    {
        $format = str_replace("dd", "d", $this->format);
        $format = str_replace("mm", "m", $format);
        $format = str_replace("yyyy", "Y", $format);
        $format = str_replace("yy", "y", $format);
        if ($value instanceof \DateTime) {
            return $this->value = $value->format($format);
        } elseif (is_string($value)) {
            $dateParts = date_parse($value);
            if ($dateParts !== false) {
                $dateTime = new \DateTime($dateParts["year"]."-".$dateParts["month"]."-".$dateParts["day"]);

                return $this->value = $dateTime->format($format);
            } else {
                return $this->value = null;
            }
        } else {
            return parent::setValue(date($format, $value));
        }
    }

    protected function renderFormItemInput($inputType, $attributes)
    {
        return <<<EOT
            <input class="t {$this->elementClassName}" type="{$inputType}" id="{$this->fullId}"
                name="{$this->id}" maxlength="{$this->maxLength}" value="{$this->value}"{$attributes}/>
            <span class="date-format">
                ({$this->format})
            </span>
EOT;
    }
}
