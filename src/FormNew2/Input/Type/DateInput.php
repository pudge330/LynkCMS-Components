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
use Lynk\Component\FormNew2\Input\Type\View\DateView;

/**
 * Range input type.
 */
class DateInput extends InputType {

    /**
     * @var string Input field name.
     */
    protected $fieldName = 'dateField';

    /**
     * Create view class.
     * 
     * @return InputView View instance
     */
    protected function createView() {
        return new DateView($this);
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
        $min = $min ? Datetime::createFromFormat('Y-m-d H:i', $min . ' 00:00') : null;
        $max = $max ? Datetime::createFromFormat('Y-m-d H:i', $max . ' 00:00') : null;

        $isSubmitted = (isset($data[$this->name]) && $data[$this->name] && $data[$this->name] != '');

        if ($this->settings->options->required && !$isSubmitted) {
            return [false, "{$displayName} is required"];
        }
        else if ($isSubmitted) {
            $submittedObject = new Datetime($data[$this->name]);
            if ($submittedObject) {
                $passedMinTest = $passedMaxTest = false;
                if ($min)
                    $passedMinTest = ((int)$submittedObject->format('Ymd') >= (int)$min->format('Ymd'));
                if ($max)
                    $passedMaxTest = ((int)$submittedObject->format('Ymd') <= (int)$max->format('Ymd'));
                $formattedMin = $min ? $min->format('Y-m-d') : '';
                $formattedMax = $max ? $max->format('Y-m-d') : '';
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