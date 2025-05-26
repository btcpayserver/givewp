=== BTCPay for GiveWP ===
Contributors: ndeet
Tags: Bitcoin, BTCPay Server, cryptocurrency, GiveWP, donations
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires Give: 2.24.0
Requires PHP: 8.1
License: MIT
License URI: https://opensource.org/licenses/MIT

A BTCPay Server Bitcoin / Lightning Network (and other cryptocurrencies) payment gateway for GiveWP.

== Description ==

= Accept Bitcoin donations in your GiveWP powered WordPress site with BTCPay Server =

BTCPay Server for GiveWP is a revolutionary, self-hosted, open-source payment gateway to accept Bitcoin payments. Our **seamless integration** with GiveWP allows you to connect your self-hosted [BTCPay Server](https://btcpayserver.org) and start accepting Bitcoin payments in **[just a few simple steps](https://docs.btcpayserver.org/GiveWP)**.

= Features: =

* **Zero fees**: Enjoy a payment gateway with no fees. Yes, really!
* **Fully automated system**: BTCPay takes care of payments, invoice management and refunds automatically.
* **Display Bitcoin QR code at checkout**: Enhance customer experience with an easy and secure payment option.
* **No middlemen or KYC**:
    * Direct, P2P payments (going directly to your wallet)
    * Say goodbye to intermediaries and tedious paperwork
    * Transaction information is only shared between you and your customer
* **Self-hosted infrastructure**: Maintain full control over your payment gateway.
* **Direct wallet payments**: Be your own bank with a self-custodial service.
* **Lightning Network** integrated out of the box - instant, fast and low cost payments and payouts
* **Reporting and accounting** - CSV exports
* **Advanced invoice management**  
* **Real-time exchange price tracking** for correct payment amounts
* **Versatile plugin system**:
    * Extend functionality according to your needs
    * Accept payments in altcoins through various plugins
* **Elegant checkout design**: Compatible with all Bitcoin wallets and enhanced with your store's logo and branding for a unique UX.
* **Point-of-sale** integration - Accept payments in your physical shops
* **Multilingual ready**: Serve a global audience right out of the box.
* **Top-notch privacy and security**: Protect your and your customers' data.
* **Community-driven support**: Get responsive assistance from our dedicated community ([Mattermost](http://chat.btcpayserver.org) or [Telegram](https://t.me/btcpayserver)).
* Extensive [documentation](https://docs.btcpayserver.org/GiveWP)

The non-profit [BTCPay Server Foundation ](https://foundation.btcpayserver.org) is committed to keeping this powerful payment gateway free forever. Our mission is to enable anyone to accept bitcoin regardless of financial, technical, social or political barriers.


== Installation ==

This plugin requires GiveWP. Please make sure you have GiveWP installed.

To add BTCPay Server as payment gateway to GiveWP, follow the steps below or check our official [installation instructions](https://docs.btcpayserver.org/GiveWP/).

### 1. Deploy BTCPay Server (optional) ###

This step is optional, if you already have a BTCPay Server instance setup you can skip to section 2. below. To launch your BTCPay server, you can self-host it, or use a third party host.

#### 1.1 Self-hosted BTCPay ####

There are various ways to [launch a self-hosted BTCPay](https://github.com/btcpayserver/btcpayserver-doc#deployment). If you do not have technical knowledge, use the [web-wizard method](https://launchbtcpay.lunanode.com) and follow the video below.

https://www.youtube.com/watch?v=NjslXYvp8bk

For the self-hosted solutions, you will have to wait for your node to sync fully before proceeding to step 3.

#### 1.2 Third-party host ####

Those who just want to test BTCPay out, or are okay with the limitations of a third-party hosting (dependency and privacy, as well as lack of some features) can use a one of many [third-party hosts](ThirdPartyHosting.md).

The video below shows you how to connect your store to such a host.

https://www.youtube.com/watch?v=IT2K8It3S3o

### 2. Install BTCPay for GiveWP Plugin ###

BTCPay for GiveWP plugin is a bridge between your BTCPay Server (payment processor) and your donation forms. No matter if you are using a self-hosted or third-party solution from step 1., the connection process is identical.

You can find detailed installation instructions on our [GiveWP documentation](https://docs.btcpayserver.org/GiveWP/).

###  3. Connecting your wallet ###

No matter if you're using self-hosted or server hosted by a third-party, the process of configuring your wallet is the same.

https://www.youtube.com/watch?v=xX6LyQej0NQ

### 4. Testing the checkout ###

Making a small test-donation, will give you a piece of mind. Always make sure that everything is set up correctly before going live. The final video, guides you through the steps of setting a gap limit in your Electrum wallet and testing the checkout process.

== Frequently Asked Questions ==

You'll find extensive documentation and answers to many of your questions on [BTCPay for GiveWP docs](https://docs.btcpayserver.org/GiveWP).

== Screenshots ==

1. Provides a Bitcoin / Lightning Network (and other) payment gateway on checkout.
2. Your customers can pay by scanning the QR-Code with their wallet or copy and paste the receiving address.
3. After successful payment the customers will get redirected to the order page. The order will be marked as paid automatically.
4. On BTCPay Server you have extensive reporting and accounting features.

== Upgrade Notice ==

= 1.0.0 =
* Initial release


== Changelog ==
= 1.0.0 :: 2025-04-02 =
* Initial release
