<?php
/*
 * Copyright (C) 2016 E-Comprocessing™
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      E-ComProcessing
 * @copyright   2016 E-Comprocessing™
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

namespace XLite\Module\EComProcessing\Genesis\View\Model;

/**
 * Settings Page Definition
 */
class EComProcessingDirect extends \XLite\Module\EComProcessing\Genesis\View\Model\AEComProcessing
{

    /**
     * Save current form reference and initialize the cache
     *
     * @param array $params   Widget params OPTIONAL
     * @param array $sections Sections list OPTIONAL
     */
    public function __construct(array $params = array(), array $sections = array())
    {
        parent::__construct($params, $sections);

        $this->schemaAccount['token'] = array(
                self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
                self::SCHEMA_LABEL    => 'Token',
                self::SCHEMA_HELP     => 'Token of your Genesis account.',
                self::SCHEMA_REQUIRED => true
        );

        $this->schemaAdditional['transaction_type'] = array(
            self::SCHEMA_CLASS    =>
                '\XLite\Module\EComProcessing\Genesis\View\FormField\Direct\Select\TransactionType',
            self::SCHEMA_LABEL    => 'Transaction type',
            self::SCHEMA_HELP     =>
                'You can select which transaction types can be attempted (from the Gateway) upon customer processing',
            self::SCHEMA_REQUIRED => true
        );
    }
}
