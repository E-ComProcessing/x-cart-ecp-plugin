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
 * @author      E-Comprocessing
 * @copyright   2016 E-Comprocessing™
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

namespace XLite\Module\EComProcessing\Genesis\Controller\Admin;

/**
 * E-Comprocessing Settings Controller
 *
 * @package XLite\Module\EComProcessing\Genesis\Controller\Admin
 */
class EcomprocessingSettings extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Module Path
     */
    const MODULE_NAME = 'EComProcessing_Genesis';

    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('method_id');

    /**
     * Get page title
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t($this->getPaymentMethod()->getTitle() . ' Settings');
    }

    /**
     * Get Should Display Message While Configuring the Module
     *
     * @return boolean
     */
    public function getShouldDisplayMessage()
    {
        return ($this->getPaymentMethod()->getServiceName() == \XLite\Module\EComProcessing\Genesis\Main::ECP_DIRECT);
    }

    /**
     * Returns message style class
     *
     * @return string
     */
    public function getDisplayMessageClass()
    {
        return \XLite\Module\EComProcessing\Genesis\Main::isStoreOverSecuredConnection()
            ? 'alert alert-warning'
            : 'alert alert-danger';
    }

    /**
     * Returns message text
     *
     * @return string
     */
    public function getDisplayMessageText()
    {
        return \XLite\Module\EComProcessing\Genesis\Main::isStoreOverSecuredConnection()
            ? 'HTTPS connection is enabled. You need PCI-DSS certificate in order to use this payment method'
            : 'This payment method requires HTTPS connection in order to process payment data! ';
    }

    /**
     * Returns Author Website
     *
     * @return string
     */
    public function getAuthorWebSite()
    {
        return \XLite\Module\EComProcessing\Genesis\Main::getAuthorWebsite();
    }

    /**
     * Return class name for the controller main form
     *
     * @return string
     */
    public function getModelFormClass()
    {
        return sprintf(
            '\XLite\Module\EComProcessing\Genesis\View\Model\%s',
            $this->getPaymentMethod()->getServiceName()
        );
    }

    /**
     * Get method id from request
     *
     * @return int
     */
    public function getMethodId()
    {
        return \XLite\Core\Request::getInstance()->method_id;
    }

    /**
     * Get configuration setting
     *
     * @param $key
     *
     * @return string|void
     */
    public function getSetting($key)
    {
        $paymentMethod = $this->getPaymentMethod();

        return $paymentMethod
            ? $paymentMethod->getSetting($key)
            : '';
    }

    /**
     * Get payment method
     *
     * @return \XLite\Model\Payment\Method
     */
    public function getPaymentMethod()
    {
        $paymentMethod = $this->getMethodId()
            ? \XLite\Core\Database::getRepo('\XLite\Model\Payment\Method')->find($this->getMethodId())
            : null;

        return $paymentMethod && static::MODULE_NAME === $paymentMethod->getModuleName()
            ? $paymentMethod
            : null;
    }

    /**
     * Do action 'Update'
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $this->getModelForm()->performAction('modify');
    }
}
