import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import BaseComponent from 'oroui/js/app/components/base/component';

const OrderReviewComponent = BaseComponent.extend({
    /**
     * @property {Object}
     */
    options: {
        paymentMethod: null
    },

    /**
     * @inheritdoc
     */
    constructor: function OrderReviewComponent(options) {
        OrderReviewComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.options = _.extend({}, this.options, options);
        mediator.on('checkout:place-order:response', this.placeOrderResponse, this);
    },

    /**
     * @inheritdoc
     */
    dispose: function() {
        mediator.off('checkout:place-order:response', this.placeOrderResponse, this);
        OrderReviewComponent.__super__.dispose.call(this);
    },

    placeOrderResponse: function(eventData) {
        if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
            if (false === eventData.responseData.purchaseSuccessful) {
                eventData.stopped = true;
                mediator.execute('redirectTo', {url: eventData.responseData.errorUrl}, {redirect: true});
            }
        }
    }
});

export default OrderReviewComponent;
