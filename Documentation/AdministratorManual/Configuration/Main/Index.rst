.. include:: ../../../Includes.txt

Main Configuration
==================

The plugin needs to know the merchant e-mail address.

::

   plugin.tx_cartmpay24 {
       test = 1

       merchantId =
       soapPassword =
   }

|

.. container:: table-row

   Property
         plugin.tx_cartmpay24.test
   Data type
         boolean
   Description
         This configuration determines whether the extension is in live or in test mode.
   Default
         The default value is chosen so that the plugin is always in test mode after installation, so that payment can be tested with mPAY24.

.. container:: table-row

   Property
         plugin.tx_cartmpay24.merchantId
   Data type
         string
   Description
         The Merchant-ID for your account. You can find it in your account settings.

.. container:: table-row

   Property
         plugin.tx_cartmpay24.soapPassword
   Data type
         string
   Description
         The password for the SOAP API will send to the email address or mobile phone number in your account settings.
