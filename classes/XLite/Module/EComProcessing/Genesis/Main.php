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

namespace XLite\Module\EComProcessing\Genesis;

/**
 * E-Comprocessing Module
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Name of the E-Comprocessing Checkout method
     */
    const ECP_CHECKOUT = 'EComprocessingCheckout';
    const ECP_DIRECT = 'EComprocessingDirect';


    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'E-Comprocessing';
    }

    /**
     * Author Website
     *
     * @return string
     */
    public static function getAuthorWebsite()
    {
        return 'http://e-comprocessing.com';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'E-Comprocessing';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.3';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '4';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Accept payments through E-Comprocessing\'s Payment Gateway - Genesis';
    }

    /**
     * The module is defined as the payment module
     *
     * @return int|null
     */
    public static function getModuleType()
    {
        return static::MODULE_TYPE_PAYMENT;
    }

    /**
     * Returns payment method
     *
     * @param string  $service_name Service name
     * @param boolean $enabled      Enabled status OPTIONAL
     *
     * @return \XLite\Model\Payment\Method
     */
    public static function getPaymentMethod($service_name, $enabled = null)
    {
        $condition = array(
            'service_name' => $service_name
        );

        if (null !== $enabled) {
            $condition['enabled'] = (bool) $enabled;
        }

        return $paymentMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                                                    ->findOneBy($condition);
    }

    /**
     * Returns true if EComProcessingCheckout payment is enabled
     *
     * @param \XLite\Model\Cart $order Cart object OPTIONAL
     *
     * @return boolean
     */
    public static function isEComProcessingCheckoutEnabled($order = null)
    {
        static $result;

        $index = isset($order) ? 1 : 0;

        if (!isset($result[$index])) {
            $paymentMethod = self::getPaymentMethod(self::ECP_CHECKOUT, true);

            if ($order && $result[$index]) {
                $result[$index] = $paymentMethod->getProcessor()->isApplicable($order, $paymentMethod);
            }
        }

        return $result[$index];
    }

    /**
     * Returns true if EComProcessingDirect payment is enabled
     *
     * @param \XLite\Model\Cart $order Cart object OPTIONAL
     *
     * @return boolean
     */
    public static function isEComProcessingDirectEnabled($order = null)
    {
        static $result;

        $index = isset($order) ? 1 : 0;

        if (!isset($result[$index])) {
            $paymentMethod = self::getPaymentMethod(self::ECP_DIRECT, true);

            if ($order && $result[$index]) {
                $result[$index] = $paymentMethod->getProcessor()->isApplicable($order, $paymentMethod);
            }
        }

        return $result[$index];
    }

    /**
     * Check - SSL Enabled
     *
     * @return boolean
     */
    public static function isStoreOverSecuredConnection()
    {
        return \XLite\Core\Config::getInstance()->Security->customer_security;
    }

    /**
     * Retrieves the X-Cart Core Version
     *
     * @return string
     */
    public static function getCurrentCoreVersion()
    {
        return \XLite::getInstance()->getVersion();
    }

    /**
     * Detects if the X-Cart Core Version is 5.2
     *
     * @return bool
     */
    public static function getIsCoreVersion52()
    {
        return
            version_compare(self::getCurrentCoreVersion(), '5.2', '>=') &&
            version_compare(self::getCurrentCoreVersion(), '5.3', '<=');
    }

    /**
     * Detects if the X-Cart Core Version is 5.3
     *
     * @return bool
     */
    public static function getIsCoreVersion53()
    {
        return
            version_compare(self::getCurrentCoreVersion(), '5.3', '>=') &&
            version_compare(self::getCurrentCoreVersion(), '5.4', '<=');
    }
}
