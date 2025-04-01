
(() => {
    let settings = {};
    /**
     * Example of rendering gateway fields (without jsx).
     *
     * This renders a simple div with a label and input.
     *
     * @see https://react.dev/reference/react/createElement
     */
    function BtcpayGatewayFields() {
        return window.wp.element.createElement(
            "div",
            {
                className: 'btpcay-gateway-help-text'
            },
            window.wp.element.createElement(
                "p",
                {
                    style: {marginBottom: 0}
                },
                settings.message,
            )
        );
    }

    /**
     * Example of a front-end gateway object.
     */
    const BtcpayGateway = {
        id: "btcpay-gateway",
        initialize() {
            settings = this.settings
        },
        Fields() {
            return window.wp.element.createElement(BtcpayGatewayFields);
        },
    };

    /**
     * The final step is to register the front-end gateway with GiveWP.
     */
    window.givewp.gateways.register(BtcpayGateway);
})();