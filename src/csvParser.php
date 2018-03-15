<?php

namespace root\src;

/**
 * Class CsvParser
 * @package root\src
 */
class CsvParser
{
    protected $files;
    protected $regexForFiles = '/[\d]+\-[\d]+\-[\d]+\-[\d]+\.csv/si';
    protected $csvSeparator = ',';
    protected $db;

    /**
     * CsvParser constructor.
     * @param DbConnect $db
     */
    public function __construct(DbConnect $db)
    {
        $this->db = $db;

        $this->fileScanner();

        $this->db->closeConnection();

    }

    /**
     * @param void
     * @return void
     */
    protected function fileScanner()
    {
        $filesInDir = scandir(UPLOADED, SCANDIR_SORT_DESCENDING);

        if (count($filesInDir) > 0)
        {
            foreach ($filesInDir as $file) {

                preg_match($this->regexForFiles, $file, $matches);

                if (count($matches) > 0) {

                    $this->csvIterator($matches[0]);
                }
            }
        } else {
            error_log('There is no file to process!');
        }
    }

    protected function csvIterator($file)
    {
        // move files to inprocess dir, continue on success
        if ($this->moveFiles(UPLOADED . $file, INPROCESS . $file)) {

            if (($handle = fopen(INPROCESS . $file, "r")) !== FALSE) {

                // find the header
                $header = fgetcsv($handle, 0, $this->csvSeparator);

                //iterate through the rest of the data
                while (($row = fgetcsv($handle, 0, $this->csvSeparator)) !== FALSE) {

                    if (array(null) !== $row) { // ignore blank lines

                        // combine headers with data
                        $data = array_combine($header, $row);

                        // validation
                        $isValid = validator::isValid($data);

                        $i = 0;
                        foreach ($isValid as $item) {

                            if ($item === TRUE) {
                                $i++;
                            } else {
                                $i--;
                            }

                        }

                        if ($i == 5) {

                            /**
                             * check duplication
                             *
                             * TODO implement proper duplication check
                             * commented out for now
                             */
                            // if ($this->db->dbDuplicationCheck($file) == 0) {

                                // add file name to data array
                                $data['fileName'] = $file;

                                // insert it to db
                                // make sure that data is map to correct field name before insert
                                $this->db->dbInsert($this->dataMapping($data));

                            //}

                        } else {

                            // write it into reports folder
                            $this->createReport($data, $file, $isValid);

                        }
                    }

                }
                // close the file
                fclose($handle);

                /**
                 * move file to completed
                 *
                 * TODO Check if all failed (by validator) move to failed folder
                 */
                $this->moveFiles(INPROCESS . $file,  COMPLETED . $file);

            } else {
                error_log('Couldn\'t open the file!');
            }
        } else {
            error_log('Couldn\'t move the file!');
        }
    }

    /**
     * @param $data
     * @param $file
     * @param $validatorMsg
     *
     * @TODO Create better file reporting
     */
    protected function createReport($data, $file, $validatorMsg)
    {

        file_put_contents(REPORTS . $file .'.json',
            'ValidatorMsg [' . json_encode($validatorMsg) . ']' . PHP_EOL .
            'data ' . json_encode($data) . PHP_EOL,
            FILE_APPEND);

    }

    /**
     * @param $source
     * @param $destination
     * @return bool
     */
    protected function moveFiles($source, $destination)
    {

         // double check if the file still exists
        if (file_exists($source)) {

            // check if the rename worked fine
            if (rename($source, $destination) === TRUE) {

                return TRUE;
            } else {

                return FALSE;
            }
        }
    }
    /**
     * @param $data data from the csv
     *
     * @return $params array
     */
    public function dataMapping($data)
    {
        $params = array(
            $this->db->fileds['eventDatetime']     => strval($data['eventDatetime']),
            $this->db->fileds['eventAction']       => strval($data['eventAction']),
            $this->db->fileds['callRef']           => intval($data['callRef']),
            $this->db->fileds['eventValue']        => floatval($data['eventValue']),
            $this->db->fileds['eventCurrencyCode'] => strval($data['eventCurrencyCode']),
            $this->db->fileds['fileName']          => strval($data['fileName']),
        );
        return $params;
    }
}
