<?php
/**
 * BankSettingsDatabase.php
 * Copyright (c) 2018 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Services\FinTS;


class BankSettingsDatabase
{
    const DATABASE_LOCATION = 'resources/data/import/fints/FinBanks.csv';
    const FILE_ENCODING = 'ISO-8859-1';

    /** @var string */
    private $databaseFile;

    /** @var array */
    private $rawRecords;

    /** @var array */
    private $records;

    /**
     * BankSettingsDatabase constructor.
     */
    public function __construct()
    {
        $this->databaseFile = base_path(self::DATABASE_LOCATION);
    }

    /**
     * @return array
     */
    public function getRecords()
    {
        if (null == $this->rawRecords) {
            $this->loadDatabaseFile();
        }
        if (null == $this->records) {
            $this->parseRawRecords();
        }
        return $this->records;
    }

    /**
     * Loads the database file and parses it into an array
     */
    private function loadDatabaseFile()
    {
        $file_handle = fopen($this->databaseFile, 'r');
        while (!feof($file_handle) ) {
            $records[] = fgetcsv($file_handle, 1024, ';');
        }
        fclose($file_handle);

        $this->rawRecords = $records;
    }

    /**
     * Parses the loaded records into an array and decodes the strings from ISO-8859-1
     */
    private function parseRawRecords()
    {
        $keys = [];
        $this->records = [];
        foreach ($this->rawRecords as $i => $record) {
            if ($i == 0) {
                foreach ($record as $j => $cell) {
                    $keys[$j] = str_slug($cell);
                }
            } else {
                if (is_array($record)) {
                    $parsedRecord = [];
                    foreach ($record as $j => $cell) {
                        $parsedRecord[$keys[$j]] = mb_convert_encoding($cell, "UTF-8", self::FILE_ENCODING);
                    }
                    $this->records[] = $parsedRecord;
                }
            }
        }
    }
}