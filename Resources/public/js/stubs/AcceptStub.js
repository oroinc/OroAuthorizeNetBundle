(function() {
    'use strict';
    window.Accept = {
        dispatchData: function(request, callback) {
            let result;
            if (request.bankData) {
                result = this.processBankRequest();
            } else {
                result = this.processCardRequest(request.cardData);
            }
            callback(result);
        },

        processCardRequest: function(cardData) {
            if (cardData.cardNumber === '5555555555554444') {
                return {
                    messages: {
                        message: [
                            {
                                code: 'E_WC_17',
                                text: 'User authentication failed due to invalid authentication values.'
                            }
                        ],
                        resultCode: 'Error'
                    }
                };
            } else if (cardData.cardNumber === '5105105105105100') {
                return {
                    messages: {
                        message: [
                            {
                                code: 'I_WC_01',
                                text: 'Successful.'
                            }
                        ],
                        resultCode: 'Ok'
                    },
                    opaqueData: {
                        dataDescriptor: 'COMMON.ACCEPT.INAPP.PAYMENT',
                        dataValue: 'special_data_value_for_api_error_emulation'
                    }
                };
            }

            return {
                messages: {
                    message: [
                        {
                            code: 'I_WC_01',
                            text: 'Successful.'
                        }
                    ],
                    resultCode: 'Ok'
                },
                opaqueData: {
                    dataDescriptor: 'COMMON.ACCEPT.INAPP.PAYMENT',
                    dataValue: 'eyJ0b2tlbiI6Ijk0OTIxNzMxMTc4ODIwODQ2MDQ2MDMiLCJ2IjoiMS4xIn0='
                }
            };
        },

        processBankRequest: function() {
            return {
                messages: {
                    message: [
                        {
                            code: 'I_WC_01',
                            text: 'Successful.'
                        }
                    ],
                    resultCode: 'Ok'
                },
                opaqueData: {
                    dataDescriptor: 'COMMON.ACCEPT.INAPP.PAYMENT',
                    dataValue: 'echeck_data_value'
                }
            };
        }
    };
})();
