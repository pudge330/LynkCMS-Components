<?php
/**
 * This file is part of the Lynk Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Lynk Components
 * @subpackage Form
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\FormNew2\Input\Type;

use Lynk\Component\FormNew2\Input\InputType;
use Lynk\Component\FormNew2\Input\Type\View\DatetimeView;

/**
 * Range input type.
 */
class DatetimeInput extends InputType {

    /**
     * @var string Input field name.
     */
    protected $fieldName = 'datetimeField';

    /**
     * Create view class.
     * 
     * @return InputView View instance
     */
    protected function createView() {
        return new DatetimeView($this);
    }

    /**
     * Validate submitted data value.
     * 
     * @param Array $data Data values.
     * 
     * @return Array Boolean as first value that indicates whether or not the value was valid.
     *               Second ootional value describes the error.
     */
    public function validateData($data) {
        $displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
        
        $min = $this->settings->options->min;
        $max = $this->settings->options->max;
        $min = $min ? Datetime::createFromFormat('Y-m-d H:i', $min) : null;
        $max = $max ? Datetime::createFromFormat('Y-m-d H:i', $max) : null;

        $submittedDate = isset($data["{$this->name}_date"]) && $data["{$this->name}_date"] != '' ? $data["{$this->name}_date"] : null;
        $submittedTime = isset($data["{$this->name}_time"]) && $data["{$this->name}_time"] != '' ? $data["{$this->name}_time"] : null;
        $isSubmitted = ($submittedDate && $submittedTime);

        if ($this->settings->options->required && !$isSubmitted) {
            return [false, "{$displayName} is required"];
        }
        else if ($isSubmitted) {
            $submittedObject = Datetime::createFromFormat('Y-m-d H:i', "{$submittedDate} {$submittedTime}");
            if ($submittedObject) {
                $passedMinTest = $passedMaxTest = false;
                if ($min)
                    $passedMinTest = ((int)$submittedObject->format('YmdHi') >= (int)$min->format('YmdHi'));
                if ($max)
                    $passedMaxTest = ((int)$submittedObject->format('YmdHi') <= (int)$max->format('YmdHi'));
                $formattedMin = $min ? $min->format('Y-m-d H:i') : '';
                $formattedMax = $max ? $max->format('Y-m-d H:i') : '';
                if ($min && $max && (!$passedMinTest || !$passedMaxTest)) {
                    return [false, "{$displayName} must be on or between {$formattedMin} and {$formattedMax}"];
                }
                else if ($min && !$passedMinTest) {
                    return [false, "{$displayName} must be on or after {$formattedMin}"];
                }
                else if ($max && !$passedMaxTest) {
                    return [false, "{$displayName} must be on or before {$formattedMax}"];
                }
            }
            else {
                return [false, "{$displayName} is an invalid format"];
            }
        }
        return [true];
    }
}