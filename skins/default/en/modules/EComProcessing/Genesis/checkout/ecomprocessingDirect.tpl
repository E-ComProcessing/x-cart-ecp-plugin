{**
 * EComProcessingDirect Template
 *
 * Copyright (C) 2016 E-ComProcessing
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
 * @copyright   2016 E-ComProcessing
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 *}

<span class="payment-title">{getMethodCheckoutLabel(method)}</span>
<img class="ecomprocessing-direct-logo" src="images/../modules/EComProcessing/Genesis/images/ecomprocessing_direct.png" alt="{method.getName()}" title="{method.getName()}" />
<div IF="method.getDescription()" class="payment-description ecomprocessing-payment-description">{method.getDescription()}</div>

<style type="text/css">
    .ecomprocessing-direct-logo {
        max-height: 25pt;
    }

    .ecomprocessing-payment-description {
        padding-top: 5pt;
    }
</style>
