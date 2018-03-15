<?php

namespace root\src;


class validator
{
    // TODO create a table for all currency codes, for now check these against the data
    public static $ISOCurrencyCodes = ['GBP','EUR','USD'];

    public static function isValid ($data)
    {
        $issetEventValue = FALSE;

        file_put_contents('/tmp/asdf', print_r($data,1), FILE_APPEND);

        $result = array();

        foreach ($data as $key => $value) {

            $result[$key] =  TRUE;

            switch($key)
            {
                // “eventDatetime” (timestamp, formatted as “yyyy-mm-dd hh:mm:ss”, required)
                case 'eventDatetime':

                    if (empty($value)){

                        $result[$key] = $key . ' cannot be empty.';

                    } elseif (\DateTime::createFromFormat('Y-m-d H:i:s', $value) == FALSE) {

                        $result[$key] =  $key . ' should be formatted to “yyyy-mm-dd hh:mm:ss”, given value: ' . $value;

                    }

                    break;
                //“eventAction” (string, 1-20 chars, required)
                case 'eventAction':

                    if (empty($value)) {

                        $result[$key] =  $key . ' cannot be empty.';

                    } elseif (gettype($value) != 'string') {

                        $result[$key] =  $key . ' should be string, given value: ' . $value . ' and the type:' . gettype($value);

                    } elseif (strlen($value) < 1 || strlen($value) > 20) {

                        $result[$key] =  $key . ' should be between 1-20 chars, given value: ' . $value;
                    }

                    break;
                //“callRef” (integer, required)
                case 'callRef':

                    $value = intval($value);
                    if (empty($value)) {

                        $result[$key] = $key . ' cannot be empty.';

                    } elseif (gettype($value) != 'integer') {

                        $result[$key] = $key . ' should be int, given value: ' . $value . ' and the type:' . gettype($value);

                    }

                    break;
                //“eventValue” (decimal, optional)
                case 'eventValue':

                    $value = floatval($value);
                    if (empty($value)) {
                    } else {

                        $issetEventValue = TRUE;

                        if (gettype($value) != 'double') {

                            $result[$key] = $key . ' should be double/float, given value: ' . $value . ' and the type:' . gettype($value);

                        }
                    }

                    break;
                //“eventCurrencyCode” (3-letter ISO 4217 currency code, required only if “eventValue” is non-zero)
                case 'eventCurrencyCode':

                    if ($issetEventValue && empty($value)) {

                        $result[$key] = $key . ' cannot be empty when eventValue is not.';

                    } elseif (array_search($value, self::$ISOCurrencyCodes) === FALSE) {

                        $result[$key] = $key . ' is not in Currency list, given value: ' . $value;

                    }

                    break;
                default:

                    $result[$key] = $key . 'is not expected.' ;

                    break;
            }
        }

        return $result;
    }


}