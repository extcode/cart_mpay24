.. include:: ../../../Includes.txt

Payment Method Configuration
============================

The payment method for mPAY24 is configured like any other payment method. There are all configuration options
from Cart available.

::

   plugin.tx_cart {
       payments {
           options {
               2 {
                   provider = MPAY24-CC-VISA
                   title = mPay24 - VISA
                   extra = 0.00
                   taxClassId = 1
                   status = open
               }
           }
       }
   }

|

.. container:: table-row

   Property
      plugin.tx_cart.payments.options.n.provider
   Data type
      string
   Description
      Defines that the payment provider for mPAY24 should be used.
      This information is mandatory and ensures that the extension Cart mPAY24 takes control and for the authorization of payment the user forwards to the mPAY24 site.
      The provider has the following syntax: **MPAY24**-*TYPE*-*BRAND*.
      The **MPAY24** is fix. The *TYPE* specifies the payment type (e.g. CC for Credit Card). The *BRAND* specifies the used brand of the payment system (e.g. VISA).
      All possible types and brands are listed in your mPAY24 account.
