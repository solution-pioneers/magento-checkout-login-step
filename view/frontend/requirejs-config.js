/**
 * Solution Pioneers SRL.
 *
 * @category   SolutionPioneers
 * @package    SolutionPioneers_CheckoutLoginStep
 * @author     Solution Pioneers <digital@solution-pioneers.com>
 * @copyright   Copyright (c) Solution Pioneers (https://www.solution-pioneers.com/)
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/progress-bar' : {
                'SolutionPioneers_CheckoutLoginStep/js/view/progress-bar-mixin':true
            }
        }
    }
};