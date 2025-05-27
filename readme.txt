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

### Requirements ###

- PHP version 8.1 or newer
- The cURL, gd, intl, json, and mbstring PHP extensions are available
- A WordPress site with GiveWP installed ([Installation instructions](https://wordpress.org/plugins/give/)
- You have a BTCPay Server version 2.0.0 or later, either [self-hosted](https://docs.btcpayserver.org/Deployment) or [hosted by a third-party](https://docs.btcpayserver.org/Deployment/ThirdPartyHosting)
- [You've a registered account on the instance](https://docs.btcpayserver.org/RegisterAccount)
- [You've a BTCPay store on the instance](https://docs.btcpayserver.org/CreateStore)
- [You've a wallet connected to your store](https://docs.btcpayserver.org/WalletSetup)

### 2. Install BTCPay for GiveWP Plugin ###

BTCPay for GiveWP plugin is a bridge between your BTCPay Server (payment processor) and your donation forms. No matter if you are using a self-hosted or third-party solution, the connection process is identical.

You can find detailed installation instructions on our [GiveWP documentation](https://docs.btcpayserver.org/GiveWP/).

### 2.1 Install via WordPress Admin ###

In your WordPress admin, go to **Plugins > Add New** and search for **BTCPay for GiveWP**. Click on **Install Now** and then **Activate**.

### 3. Configure BTCPay for GiveWP ###

In your WordPress admin, go to **GiveWP > Settings > Payment Gateways** and click on **BTCPay for GiveWP**. You will need to enter the following information:
- **BTCPay Server URL**: The URL of your BTCPay Server instance (e.g., `https://btcpay.example.com`)
- **Store ID**: The ID of your BTCPay store (you can find it in the store settings on your BTCPay Server instance)
- **API Key**: The API key for your BTCPay store (you can create it in the store settings on your BTCPay Server instance). See [here](https://docs.btcpayserver.org/GiveWP#generate-api-key) for instructions on how to generate an API key.

After you save the settings, the plugin will automatically connect to your BTCPay Server instance and create a webhook to receive payment notifications.
You should see the following notifications:
- BTCPay for GiveWP: BTCPay Server API credentials verified successfully.
- BTCPay for GiveWP: Webhook successfully created.

### 4. Enable BTCPay for GiveWP ###

Now on the top of the BTCPay for GiveWP settings page, click on **Gateways** and make sure there is a checkmark set to enable the "BTCPay Server Gateway". You can also change the text of the payment gateway, it defaults to "Bitcoin".

### 5. Test the Payment Gateway ###

You are good to go! You can now test the payment gateway by creating a new donation form in GiveWP and selecting the BTCPay Server payment gateway at checkout.

== Frequently Asked Questions ==

You'll find extensive documentation and answers to many of your questions on [BTCPay for GiveWP docs](https://docs.btcpayserver.org/GiveWP#troubleshooting).

== Screenshots ==

1. Provides a Bitcoin / Lightning Network (and other) payment gateway on donation forms.
2. Your customers can pay by scanning the QR-Code with their wallet or copy and paste the receiving address.
3. After successful payment the customers will get redirected to the order page. The order will be marked as paid automatically.
4. On backend, in GiveWP donations overview, you can see the payment status as paid.
5. The BTCPay for GiveWP settings page.
6. On BTCPay you will have extensive reporting and accounting features, including CSV exports.

== Upgrade Notice ==

= 1.0.0 =
* Initial release


== Changelog ==
= 1.0.0 :: 2025-05-27 =
* Initial release
