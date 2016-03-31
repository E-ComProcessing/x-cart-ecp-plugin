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

namespace XLite\Module\EComProcessing\Genesis\Model\Payment\Processor;

abstract class AEComProcessing extends \XLite\Model\Payment\Base\Online
{
    /**
     * Pre-fill "Description" field
     *
     * @var string
     */
    const TXN_USG = 'X-Cart Transaction';

    /**
     * Transaction CID field
     *
     * @var string
     */
    const REF_CID = 'checkout_id';

    /**
     * Transaction REF field
     *
     * @var string
     */
    const REF_UID = 'unique_id';

    /**
     * Transaction TERMINAL field
     *
     * @var string
     */
    const REF_TKN = 'terminal_token';

    /**
     * Checkout Template Directory
     *
     * @var string
     */
    const CHECKOUT_TEMPLATE_DIR = 'modules/EComProcessing/Genesis/checkout/';

    /**
     * Get allowed backend transactions
     *
     * @return string Status code
     */
    public function getAllowedTransactions()
    {
        return array(
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE,
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID,
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND,
        );
    }

    /**
     * Get return type
     *
     * @return string
     */
    public function getReturnType()
    {
        return self::RETURN_TYPE_HTTP_REDIRECT;
    }

    /**
     * Get payment method configuration page URL
     *
     * @param \XLite\Model\Payment\Method   $method     Payment method
     * @param boolean                       $justAdded  Flag if the method is just added via administration panel.
     *
     * @return string
     */
    public function getConfigurationURL(\XLite\Model\Payment\Method $method, $justAdded = false)
    {
        return \XLite\Core\Converter::buildURL(
            'ecomprocessing_settings',
            '',
            array('method_id' => $method->getMethodId())
        );
    }

    /**
     * Check - payment method is configured or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isConfigured(\XLite\Model\Payment\Method $method)
    {
        return parent::isConfigured($method)
            && $method->getSetting('title')
            && $method->getSetting('username')
            && $method->getSetting('secret');
    }

    /**
     * Check - is method available for checkout
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isAvailable(\XLite\Model\Payment\Method $method)
    {
        return true;
    }

    /**
     * Check - is Method applicable for Order (depends on method requirements)
     *
     * @param \XLite\Model\Order $order
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isApplicable(\XLite\Model\Order $order, \XLite\Model\Payment\Method $method)
    {
        return parent::isApplicable($order, $method)
            && $this->isAvailable($method);
    }

    /**
     * Check - payment method has enabled test mode or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isTestMode(\XLite\Model\Payment\Method $method)
    {
        return \XLite\View\FormField\Select\TestLiveMode::TEST == $method->getSetting('mode');
    }

    /**
     * Payment method has settings into Module settings section
     *
     * @return boolean
     */
    public function hasModuleSettings()
    {
        return true;
    }

    /**
     * Get payment method admin zone icon URL
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getAdminIconURL(\XLite\Model\Payment\Method $method)
    {
        return true;
    }

    /**
     * Define the fields for Transaction Data
     *
     * @return array
     */
    protected function defineSavedData()
    {
        $data = array(
            'unique_id' => 'UniqueId',
            'transaction_id' => 'TransactionId',
            'type' => 'Type',
            'status' => 'Status',
            'timestamp' => 'Timestamp',
            'amount' => 'Amount',
            'currency' => 'Currency',
        );

        return array_merge(parent::defineSavedData(), $data);
    }

    /**
     * Before Capture transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return void
     */
    protected function doBeforeCapture(\XLite\Model\Payment\BackendTransaction $transaction)
    {

    }

    /**
     * Capture payment transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return bool
     * @throws \Exception
     */
    public function doCapture(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        // Initialize Genesis
        $this->initLibrary();

        //Additional Initialisation Code needed for the transaction
        $this->doBeforeCapture($transaction);

        try {
            // Genesis Request
            $capture = new \Genesis\Genesis('Financial\Capture');

            $capture
                ->request()
                    ->setTransactionId(md5(microtime()))
                    ->setReferenceId($this->getRefId($transaction))
                    ->setRemoteIp($this->getClientIP())
                    ->setUsage(self::TXN_USG)
                    ->setAmount($this->getFormattedPrice($transaction->getValue()))
                    ->setCurrency(
                        $transaction->getPaymentTransaction()->getOrder()->getCurrency()->getCode()
                    );

            $capture->execute();

            $result = true;

            $status = \XLite\Model\Payment\Transaction::STATUS_SUCCESS;

            $transaction->getPaymentTransaction()->getOrder()->setPaymentStatus(
                \XLite\Model\Order\Status\Payment::STATUS_PAID
            );

            $this->updateTransactionData($transaction, $capture->response()->getResponseObject());

            \XLite\Core\TopMessage::getInstance()
                ->addInfo(
                    $capture->response()->getResponseObject()->message
                );
        } catch (\Exception $e) {
            $result = false;

            $status = \XLite\Model\Payment\Transaction::STATUS_FAILED;

            \XLite\Core\TopMessage::getInstance()->addError($e->getMessage());
        }

        $transaction->setStatus($status);
        $transaction->update();

        return $result;
    }

    /**
     * Before Refund transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return void
     */
    protected function doBeforeRefund(\XLite\Model\Payment\BackendTransaction $transaction)
    {

    }

    /**
     * Refund payment transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return bool
     * @throws \Exception
     */
    public function doRefund(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        // Initialize Genesis
        $this->initLibrary();

        //Additional Initialisation Code needed for the transaction
        $this->doBeforeRefund($transaction);

        try {
            $refund = new \Genesis\Genesis('Financial\Refund');

            $refund
                ->request()
                    ->setTransactionId(md5(time()))
                    ->setReferenceId($this->getRefId($transaction))
                    ->setRemoteIp($this->getClientIP())
                    ->setUsage(self::TXN_USG)
                    ->setAmount($this->getFormattedPrice($transaction->getValue()))
                    ->setCurrency($transaction->getPaymentTransaction()->getOrder()->getCurrency()->getCode());

            $refund->execute();

            $result = true;

            $status = \XLite\Model\Payment\Transaction::STATUS_SUCCESS;

            $transaction->getPaymentTransaction()->getOrder()->setPaymentStatus(
                \XLite\Model\Order\Status\Payment::STATUS_REFUNDED
            );

            $this->updateTransactionData($transaction, $refund->response()->getResponseObject());

            \XLite\Core\TopMessage::getInstance()
                ->addInfo(
                    $refund->response()->getResponseObject()->message
                );
        } catch (\Exception $e) {
            $result = false;

            $status = \XLite\Model\Payment\Transaction::STATUS_FAILED;

            \XLite\Core\TopMessage::getInstance()
                ->addError($e->getMessage());
        }

        $transaction->setStatus($status);
        $transaction->update();

        return $result;
    }

    /**
     * Before Void transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return void
     */
    protected function doBeforeVoid(\XLite\Model\Payment\BackendTransaction $transaction)
    {

    }

    /**
     * Void a Payment transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return bool
     * @throws \Exception
     */
    protected function doVoid(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        // Initialize Genesis
        $this->initLibrary();

        //Additional Initialisation Code needed for the transaction
        $this->doBeforeVoid($transaction);

        try {
            $void = new \Genesis\Genesis('Financial\Void');

            $void
                ->request()
                    ->setTransactionId(md5(time()))
                    ->setReferenceId($this->getRefId($transaction))
                    ->setRemoteIp($this->getClientIP())
                    ->setUsage(self::TXN_USG);

            $void->execute();

            $result = true;

            $status = \XLite\Model\Payment\Transaction::STATUS_SUCCESS;

            $transaction->getPaymentTransaction()->getOrder()->setPaymentStatus(
                \XLite\Model\Order\Status\Payment::STATUS_DECLINED
            );

            $this->updateTransactionData($transaction, $void->response()->getResponseObject());

            \XLite\Core\TopMessage::getInstance()
                ->addInfo(
                    $void->response()->getResponseObject()->message
                );
        } catch (\Exception $e) {
            $result = false;

            $status = \XLite\Model\Payment\Transaction::STATUS_FAILED;

            \XLite\Core\TopMessage::getInstance()
                ->addError($e->getMessage());

        }

        $transaction->setStatus($status);
        $transaction->update();

        return $result;
    }

    /**
     * Process customer return
     *
     * @param \XLite\Model\Payment\Transaction $transaction Return-owner transaction
     *
     * @return void
     */
    public function processReturn(\XLite\Model\Payment\Transaction $transaction)
    {
        parent::processReturn($transaction);

        /** @var \XLite\Core\Request $request */
        $request = \XLite\Core\Request::getInstance();

        if (isset($request->cancel) && $request->cancel) {
            $status = $transaction::STATUS_CANCELED;

            $transaction->setNote(
                static::t('Customer cancelled the order during checkout!')
            );
        } else {
            if (isset($request->action) && $request->action == 'success') {
                if ($transaction::STATUS_INPROGRESS == $transaction->getStatus()) {
                    $status = $transaction::STATUS_PENDING;
                }

                $transaction->setNote(
                    static::t('Payment completed successfully!')
                );

            } else {
                $this->transaction->setDataCell(
                    'status',
                    static::t('Payment unsuccessful!'),
                    null,
                    static::FAILED
                );

                \XLite\Core\TopMessage::getInstance()->addError(
                    static::t('Payment unsuccessful!')
                );
            }
        }

        $transaction->setStatus($status);

        $transaction->update();

        static::log(
            'processReturn',
            array(
                'request' => $request,
                'status' => $status,
            )
        );
    }

    /**
     * Process Genesis Reconciliation Object
     *
     * @param \XLite\Model\Payment\Transaction $transaction
     * @param \stdClass $reconcile
     *
     * @return void
     */
    protected function doProcessCallbackReconciliationObject(
        \XLite\Model\Payment\Transaction $transaction,
        \stdClass $reconcile
    ) {

    }

    /**
     * Process Genesis Callback
     *
     * @param \XLite\Model\Payment\Transaction $transaction
     *
     * @return void
     */
    public function processCallback(\XLite\Model\Payment\Transaction $transaction)
    {
        parent::processCallback($transaction);


        $status = $transaction::STATUS_FAILED;

        /** @var \XLite\Core\Request $request */

        $request = \XLite\Core\Request::getInstance();

        if ($request->isPost()) {
            $this->initLibrary();

            try {
                $notification = new \Genesis\API\Notification($request->getData());

                $notification->initReconciliation();

                $reconcile = $notification->getReconciliationObject();

                $this->doProcessCallbackReconciliationObject($transaction, $reconcile);

                // Render notification
                $notification->renderResponse();
            } catch (\Exception $e) {
                $this->markCallbackRequestAsInvalid(static::t('Unable to verify Notification Authenticity!'));
            }
        } else {
            // Callback request must be POST
            $this->markCallbackRequestAsInvalid(static::t('Invalid request type, Notifications are POST-only!'));
        }

        static::log(
            'processCallback',
            array(
                'request' => $request,
                'status' => $status,
            )
        );
    }

    /**
     * Insert/Update data for PaymentTransaction/BackendTransaction
     *
     * @param mixed $transaction Backend transaction
     * @param \stdClass $responseObj Genesis Response
     */
    protected function updateTransactionData($transaction, $responseObj)
    {
        foreach ($this->defineSavedData() as $key => $name) {
            if (isset($responseObj->$key)) {
                // Handle DateTime objects
                if ($responseObj->$key instanceof \DateTime) {
                    $responseObj->$key = $responseObj->$key->format('c');
                }

                $this->setDetail($key, (string)$responseObj->$key, $name, $transaction);
            }
        }
    }

    /**
     * Insert/Update data for PaymentTransaction
     *
     * @param \XLite\Model\Payment\Transaction $transaction Transaction Object
     * @param \stdClass $responseObj Genesis Response
     */
    protected function updateInitialPaymentTransaction(\XLite\Model\Payment\Transaction $transaction, $responseObj)
    {
        $vars = array(
            'terminal_token'=> 'Terminal',
            'status'        => 'Status',
            'amount'        => 'Amount',
            'currency'      => 'Currency',
            'timestamp'     => 'Timestamp',
        );

        // Set the CheckoutID
        $this->setDetail(self::REF_CID, (string)$responseObj->unique_id, 'Checkout ID', $transaction);

        // Set the rest of the data
        $payment = $responseObj->payment_transaction;

        foreach ($vars as $key => $name) {
            if (isset($payment->$key)) {
                // Handle DateTime objects
                if ($payment->$key instanceof \DateTime) {
                    $payment->$key = $payment->$key->format('c');
                }

                $this->setDetail($key, strval($payment->$key), $name, $transaction);
            }
        }
    }

    /**
     * Get reference ID for Capture/Void/Refund transactions
     *
     * @param \XLite\Model\Payment\BackendTransaction $backendTransaction Backend transaction object
     *
     * @return string
     */
    protected function getRefId(\XLite\Model\Payment\BackendTransaction $backendTransaction)
    {
        $referenceId = null;

        $initialTransaction = $backendTransaction->getPaymentTransaction()->getInitialBackendTransaction();

        switch ($backendTransaction->getType()) {
            case \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE:
            case \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID:
                if (\XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH == $initialTransaction->getType()) {
                    $referenceId = $initialTransaction->getDataCell(self::REF_UID)->getValue();
                }
                break;
            case \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND:
                $paymentTransaction = $backendTransaction->getPaymentTransaction();

                if (\XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE == $paymentTransaction->getType()) {
                    $referenceId = $initialTransaction->getDataCell(self::REF_UID)->getValue();
                } elseif ($paymentTransaction->isCaptured()) {
                    foreach ($paymentTransaction->getBackendTransactions() as $bt) {
                        if (\XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE == $bt->getType() &&
                            \XLite\Model\Payment\Transaction::STATUS_SUCCESS == $bt->getStatus()
                        ) {
                            $referenceId = $bt->getDataCell(self::REF_UID)->getValue();
                            break;
                        }
                    }
                }
                break;
        }

        return $referenceId;
    }

    /**
     * Get X-Cart Order Status based on the payment response
     *
     * @param $payment
     *
     * @return string
     */
    protected function getPaymentStatus($payment)
    {
        // As we're using the type only, we need to verify,
        // the transaction status as well
        if (!in_array(
            $this->getTransactionStatus($payment),
            array(
                \XLite\Model\Payment\Transaction::STATUS_SUCCESS,
                \XLite\Model\Payment\Transaction::STATUS_PENDING
            )
        )
        ) {
            return \XLite\Model\Order\Status\Payment::STATUS_DECLINED;
        }

        $partialFlag = (isset($payment->partial_approval) && $payment->partial_approval) ? true : false;

        switch ($payment->transaction_type) {
            case \Genesis\API\Constants\Transaction\Types::AUTHORIZE:
            case \Genesis\API\Constants\Transaction\Types::AUTHORIZE_3D:
                $status = \XLite\Model\Order\Status\Payment::STATUS_AUTHORIZED;
                break;
            case \Genesis\API\Constants\Transaction\Types::ABNIDEAL:
            case \Genesis\API\Constants\Transaction\Types::CASHU:
            case \Genesis\API\Constants\Transaction\Types::NETELLER:
            case \Genesis\API\Constants\Transaction\Types::PAYSAFECARD:
            case \Genesis\API\Constants\Transaction\Types::PPRO:
            case \Genesis\API\Constants\Transaction\Types::SALE:
            case \Genesis\API\Constants\Transaction\Types::SALE_3D:
            case \Genesis\API\Constants\Transaction\Types::SOFORT:
                $status = ($partialFlag)
                    ? \XLite\Model\Order\Status\Payment::STATUS_PART_PAID
                    : \XLite\Model\Order\Status\Payment::STATUS_PAID;
                break;
            case \Genesis\API\Constants\Transaction\Types::REFUND:
                $status = \XLite\Model\Order\Status\Payment::STATUS_REFUNDED;
                break;
            case \Genesis\API\Constants\Transaction\Types::VOID:
                $status = \XLite\Model\Order\Status\Payment::STATUS_DECLINED;
                break;
            default:
                $status = '';
                break;
        }

        return $status;
    }

    /**
     * Get TransactionType based on the payment response
     *
     * @param $payment
     *
     * @return string
     */
    protected function getTransactionType($payment)
    {
        $partialFlag = (isset($payment->partial_approval) && $payment->partial_approval) ? true : false;

        switch ($payment->transaction_type) {
            case \Genesis\API\Constants\Transaction\Types::AUTHORIZE:
            case \Genesis\API\Constants\Transaction\Types::AUTHORIZE_3D:
                $status = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH;
                break;
            case \Genesis\API\Constants\Transaction\Types::CAPTURE:
                $status = ($partialFlag)
                    ? \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE_PART
                    : \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE;
                break;
            case \Genesis\API\Constants\Transaction\Types::ABNIDEAL:
            case \Genesis\API\Constants\Transaction\Types::CASHU:
            case \Genesis\API\Constants\Transaction\Types::NETELLER:
            case \Genesis\API\Constants\Transaction\Types::PAYSAFECARD:
            case \Genesis\API\Constants\Transaction\Types::PPRO:
            case \Genesis\API\Constants\Transaction\Types::SALE:
            case \Genesis\API\Constants\Transaction\Types::SALE_3D:
            case \Genesis\API\Constants\Transaction\Types::SOFORT:
                $status = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE;
                break;
            case \Genesis\API\Constants\Transaction\Types::REFUND:
                $status = ($partialFlag)
                    ? \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND_PART
                    : \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND;
                break;
            case \Genesis\API\Constants\Transaction\Types::VOID:
                $status = ($partialFlag)
                    ? \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID_PART
                    : \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID;
                break;
            default:
                $status = '';
                break;
        }

        return $status;
    }

    /**
     * Get X-Cart Transaction status based on the payment response
     *
     * @param $payment
     *
     * @return mixed
     */
    protected function getTransactionStatus($payment)
    {
        switch ($payment->status) {
            case \Genesis\API\Constants\Transaction\States::APPROVED:
                $status = \XLite\Model\Payment\Transaction::STATUS_SUCCESS;
                break;
            default:
            case \Genesis\API\Constants\Transaction\States::ERROR:
            case \Genesis\API\Constants\Transaction\States::DECLINED:
                $status = \XLite\Model\Payment\Transaction::STATUS_FAILED;
                break;
            case \Genesis\API\Constants\Transaction\States::PENDING:
            case \Genesis\API\Constants\Transaction\States::PENDING_ASYNC:
            case \Genesis\API\Constants\Transaction\States::NEW_STATUS:
            case \Genesis\API\Constants\Transaction\States::IN_PROGRESS:
            case \Genesis\API\Constants\Transaction\States::USER:
                $status = \XLite\Model\Payment\Transaction::STATUS_PENDING;
                break;
            case \Genesis\API\Constants\Transaction\States::VOIDED:
                $status = \XLite\Model\Payment\Transaction::STATUS_VOID;
                break;
        }

        return $status;
    }

    /**
     * Redirect the customer to a selected URL
     *
     * @param string $url URL
     *
     * @return void
     */
    protected function redirectToURL($url)
    {
        static::log('redirectToURL(): ' . $url);

        $page = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body onload="javascript:self.location='$url';">
<div style="margin-top:66px;text-align:center;">
    <p>Please wait, you're being redirected to a Secure Page to complete your payment.</p>
</div>
</body>
</html>
HTML;

        echo $page;
    }

    /**
     * Get all the data required by the Gateway
     *
     * @return array
     */
    protected function collectInitialPaymentData()
    {
        $data = array(
            'transaction_id' => $this->getSetting('prefix') . $this->transaction->getPublicTxnId(),
            'amount' => $this->getFormattedPrice($this->transaction->getValue()),
            'currency' => $this->transaction->getOrder()->getCurrency()->getCode(),
            'usage' => self::TXN_USG,
            'description' => $this->getOrderSummary($this->transaction->getOrder()),
            'customer_email' => $this->getProfile()->getLogin(),
            'customer_phone' => $this->getCustomerPhone(),
            'notification_url' => $this->getGenesisCallbackURL(),
            'return_success_url' => $this->getGenesisSuccessURL(),
            'return_failure_url' => $this->getGenesisFailureURL(),
            'return_cancel_url' => $this->getGenesisCancelURL(),
        );

        if ($this->getProfile()->getBillingAddress()) {
            $billing = array(
                'billing' => array(
                    'first_name' => $this->getProfile()->getBillingAddress()->getFirstname(),
                    'last_name' => $this->getProfile()->getBillingAddress()->getLastname(),
                    'address1' => $this->getProfile()->getBillingAddress()->getStreet(),
                    'address2' => $this->getProfile()->getBillingAddress()->getAddress2(),
                    'zip_code' => $this->getProfile()->getBillingAddress()->getZipcode(),
                    'city' => $this->getProfile()->getBillingAddress()->getCity(),
                    'state' => $this->getProfile()->getBillingAddress()->getState()->getCode(),
                    'country' => $this->getProfile()->getBillingAddress()->getCountry()->getCode(),
                )
            );
        } else {
            $billing = array(
                'billing' => array(
                    'first_name' => '',
                    'last_name' => '',
                    'address1' => '',
                    'address2' => '',
                    'zip_code' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                )
            );
        }

        $data = array_merge($data, $billing);

        if ($this->getProfile()->getShippingAddress()) {
            $shipping = array(
                'shipping' => array(
                    'first_name' => $this->getProfile()->getShippingAddress()->getFirstname(),
                    'last_name' => $this->getProfile()->getShippingAddress()->getLastname(),
                    'address1' => $this->getProfile()->getShippingAddress()->getStreet(),
                    'address2' => $this->getProfile()->getShippingAddress()->getAddress2(),
                    'zip_code' => $this->getProfile()->getShippingAddress()->getZipcode(),
                    'city' => $this->getProfile()->getShippingAddress()->getCity(),
                    'state' => $this->getProfile()->getShippingAddress()->getState()->getCode(),
                    'country' => $this->getProfile()->getShippingAddress()->getCountry()->getCode(),
                )
            );
        } else {
            $shipping = array(
                'billing' => array(
                    'first_name' => '',
                    'last_name' => '',
                    'address1' => '',
                    'address2' => '',
                    'zip_code' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                )
            );
        }

        $data = array_merge($data, $shipping);

        return $data;
    }

    /**
     * Get "callback" return URL
     *
     * @return string
     */
    protected function getGenesisCallbackURL()
    {
        return $this->getCallbackURL(null, true, true);
    }

    /**
     * Get "Success" return URL
     *
     * @return string
     */
    protected function getGenesisSuccessURL()
    {
        return $this->getReturnURL(null, true) . '&action=success';
    }

    /**
     * Get "Failure" return URL
     *
     * @return string
     */
    protected function getGenesisFailureURL()
    {
        return $this->getReturnURL(null, true) . '&action=failure';
    }

    /**
     * Get "Cancel" return URL
     *
     * @return string
     */
    protected function getGenesisCancelURL()
    {
        return $this->getReturnURL(null, true, true);
    }

    /**
     * Get the Customer Phone Number
     *
     * @return string
     */
    protected function getCustomerPhone()
    {
        $address = $this->getProfile()->getBillingAddress() ?: $this->getProfile()->getShippingAddress();

        return $address
            ? trim($address->getPhone())
            : static::t('000000');
    }

    /**
     * Get description for the order
     *
     * @param \XLite\Model\Order $order
     *
     * @return string
     */
    protected function getOrderSummary($order)
    {
        $desc = '';

        foreach ($order->getItems() as $item) {
            $desc .= sprintf(
                '%s x%dpc%s %s',
                $item->getName(),
                $item->getAmount(),
                ($item->getAmount() > 1 ? 's' : ''),
                PHP_EOL
            );
        }

        return $desc;
    }

    /**
     * Format state of billing address for request
     *
     * @return string
     */
    protected function getBillingState()
    {
        return $this->getState($this->getProfile()->getBillingAddress());
    }

    /**
     * Format state of shipping address for request
     *
     * @return string
     */
    protected function getShippingState()
    {
        return $this->getState($this->getProfile()->getShippingAddress());
    }

    /**
     * Format state that is provided from $address model for request.
     *
     * @param \XLite\Model\Address $address Address model (could be shipping or billing address)
     *
     * @return string
     */
    protected function getState($address)
    {
        $state = ('US' === $this->getCountryField($address))
            ? $address->getState()->getCode()
            : $address->getState()->getState();

        if (empty($state)) {
            $state = 'n/a';
        } elseif (!in_array($this->getCountryField($address), array('US', 'CA'))) {
            $state = 'XX';
        }

        return $state;
    }

    /**
     * Return Country field value. if no country defined we should use '' value
     *
     * @param \XLite\Model\Address $address Address model (could be shipping or billing address)
     *
     * @return string
     */
    protected function getCountryField($address)
    {
        return $address->getCountry()
            ? $address->getCountry()->getCode()
            : '';
    }

    /**
     * Return formatted price.
     *
     * @param float $price Price value
     *
     * @return string
     */
    protected function getFormattedPrice($price)
    {
        return sprintf('%.2f', round((double)($price) + 0.00000000001, 2));
    }

    /**
     * Get the Checkout Template Path
     *
     * $param \XLite\Model\Payment\Method $method
     *
     * @return string
     */
    public function getCheckoutTemplate(\XLite\Model\Payment\Method $method)
    {
        return self::CHECKOUT_TEMPLATE_DIR;
    }

    /**
     * Load Genesis library
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function initLibrary()
    {
        include_once LC_DIR_MODULES . '/EComProcessing/Genesis/Library/Genesis/vendor/autoload.php';

        if (!class_exists('\Genesis\Genesis', true)) {
            $error_message = static::t('Module requirements are not being set!');

            static::log('initLibrary()', $error_message);

            throw new \Exception($error_message);
        }

        // Endpoint
        \Genesis\Config::setEndpoint(\Genesis\API\Constants\Endpoints::ECOMPROCESSING);
        // Username
        \Genesis\Config::setUsername($this->getSetting('username'));
        // Password
        \Genesis\Config::setPassword($this->getSetting('secret'));

        $token = $this->getSetting('token');
        //EComProcessingDirect Payment Method requires token
        if (!empty($token)) {
            //Token
            \Genesis\Config::setToken($token);
        }

        // Environment
        \Genesis\Config::setEnvironment(
            $this->isTestMode($this->transaction->getPaymentMethod()) ?
                \Genesis\API\Constants\Environments::STAGING :
                \Genesis\API\Constants\Environments::PRODUCTION
        );
    }

    /**
     * Log the supplied data in the module-specific log file
     *
     * @note Available if developer_mode is on in the config file
     *
     * @param mixed $data
     *
     * @return void
     */
    protected static function log($data)
    {
        if (LC_DEVELOPER_MODE) {
            if (is_array($data)) {
                $data = implode(PHP_EOL, $data);
            }

            \XLite\Logger::logCustom('E-ComProcessing', (string)$data);
        }
    }
}