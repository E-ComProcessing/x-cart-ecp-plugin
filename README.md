E-Comprocessing Gateway Module for X-Cart
===========================================

This is a Payment Module for X-Cart, that gives you the ability to process payments through E-Comprocessing's Payment Gateway - Genesis.

Requirements
------------

* X-Cart 5.3.x (you can get this plugin to work on older 5.2 versions simply by changing the __Major Version__ to ```5.2``` in ```Main.php```)
* [GenesisPHP v1.4](https://github.com/GenesisGateway/genesis_php) - (Integrated in Module)
* PCI-certified server in order to use ```E-Comprocessing Direct```

GenesisPHP Requirements
------------

* PHP version 5.3.2 or newer
* PHP Extensions:
    * [BCMath](https://php.net/bcmath)
    * [CURL](https://php.net/curl) (required, only if you use the curl network interface)
    * [Filter](https://php.net/filter)
    * [Hash](https://php.net/hash)
    * [XMLReader](https://php.net/xmlreader)
    * [XMLWriter](https://php.net/xmlwriter)

Installation
------------

* Log into ```X-Cart Administration Area``` with your Administrator account 
* Navigate to ```Modules```
* Install through the Marketplace OR click ```Upload add-on``` and select  ```.zip``` file you downloaded
* Tick ```Enabled``` under the ```E-Comprocessing``` plugin and click ```Save changes```
* Navigate to ```Store setup -> Payment methods```
* Under ```Online methods``` category, click ```Add payment method``` and select ```E-Comprocessing``` from the list
* Enter your credentials and configure the plugin to your liking
* Go back to ```Store setup -> Payment methods``` and toggle the ```E-Comprocessing``` payment method from ```INACTIVE``` to ```ACTIVE```
* If you wish to use ```E-Comprocessing Direct```, the SSL of the Front Store must be enabled. 
If you have already configured a SSL Certificate, go to ```System settings``` -> ```HTTPS settings``` and click ```Enable HTTPS``` in order to be able to process direct payment transactions through our gateway

Installation (Manual)
------------

* Upload the contents of folder (excluding ```README.md```) to the ```<root>``` folder of your X-Cart installation
* Log into ```X-Cart Administration Area``` with your Administrator account
* Go to ```System Settings``` -> ```Cache Management```, click ```Re-deploy the store``` and wait until the ```Deployment Process``` finishes
* Go to ```Modules``` -> Locate ```E-Comprocessing``` Module and tick ```Enabled``` under the ```E-Comprocessing``` plugin and click ```Save changes```
* Navigate to ```Store setup -> Payment methods```
* Under ```Online methods``` category, click ```Add payment method``` and select ```E-Comprocessing``` from the list
* Enter your credentials and configure the plugin to your liking
* Go back to ```Store setup -> Payment methods``` and toggle the ```E-Comprocessing``` payment method from ```INACTIVE``` to ```ACTIVE```
* If you wish to use ```E-Comprocessing Direct```, the SSL of the Front Store must be enabled. 
If you have already configured a SSL Certificate, go to ```System settings``` -> ```HTTPS settings``` and click ```Enable HTTPS``` in order to be able to process direct payment transactions through our gateway

_Note_: If you have trouble with your credentials or terminal configuration, get in touch with our [support] team

You're now ready to process payments through our gateway.

[support]: mailto:tech-support@e-comprocessing.net
