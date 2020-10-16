{**
 * EComProcessingDirect Payment Template
 *
 * Copyright (C) 2016 E-Comprocessing
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
 * @copyright   2016 E-Comprocessing
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 *}

<div class="transparent-redirect-box">
  <div id="payment-method-ecomprocessing-direct">
    <div class="row">
      <div class="col-xs-12">
        <div class="payment-method-container">
          <div class="payment-method-header">
            <div class="row no-gutter">
              <div class="col-xs-12">
                <h2>{t(#Pay with Credit / Debit Card#)}</h2>
              </div>
            </div>
          </div>

          <div class="payment-method-content">
            <div class="row no-gutter">
              <div class="card-wrapper-container no-gutter">
                <div class="card-wrapper"></div>
              </div>

              <div class="card-controls-container no-gutter">
                <div class="form-wrapper">
                  <div class="form-group active">
                      <input autocomplete="off"
                             placeholder="Card number"
                             title="Card number"
                             class="form-control field-required" type="text"
                             name="ecomprocessing-direct-card-number">
                      <input autocomplete="off"
                             placeholder="Card holder"
                             title="Card holder"
                             class="form-control field-required" type="text"
                             name="ecomprocessing-direct-card-holder">
                      <input autocomplete="off"
                             placeholder="CVV / CVV2 / CSC"
                             title="CVV / CVV2 / CSC"
                             class="form-control card-cvv field-required" type="text"
                             name="ecomprocessing-direct-card-cvc">
                      <input autocomplete="off"
                             placeholder="Expiration date (month / year)"
                             title="Expiration date (month / year)"
                             class="form-control card-expiry field-required" type="text"
                             name="ecomprocessing-direct-card-expiry">
                  </div>
                </div>
              </div>
              <div class="col-md-1 col-lg-2"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
