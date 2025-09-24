import { getSetting } from '@woocommerce/settings';
import { __ } from '@wordpress/i18n';
export const Block = ({ checkoutExtensionData, extensions }) => {
	const { setExtensionData } = checkoutExtensionData;
	var {time_interval}  = getSetting( 'share-cart-checkout_data');
    const handlePickupDateChange = (event) => {
		setExtensionData( 'wc-local-pickup', 'lp_pickup_date', event.target.value );
    };

    const handlePickupTimeChange = (event) => {
		setExtensionData( 'wc-local-pickup', 'lp_pickup_time', event.target.value )
    };
	const handlePickupComments = (event) => {
		setExtensionData( 'wc-local-pickup', 'lp_pickup_comments', event.target.value )
    };
	var timeIntervalHTML = time_interval.map(function(data) {
		return <option value={data}>{data}</option>
	})
    return (
        <div className="custom-pickup-fields">
            <h3>{__('Pickup Information', 'loyalty-program')}</h3>
			<div className="pickup_date">
				<label>{__('Pickup Date', 'loyalty-program')} <br />
            		<input style={{padding:'10px', width:'97%', marginBottom:'10px'}} label="Pickup Date" type="date" onChange={handlePickupDateChange} />
				</label>
			</div>
			<div className="pickup_time">
				<label>{__('Pickup Time', 'loyalty-program')} <br />
					<select style={{padding:'10px', width:'100%'}} onChange={handlePickupTimeChange}>
						<option>{__('Choose Time Interval', 'loyalty-program')}</option>
						{timeIntervalHTML}
					</select>
				</label>
			</div>
			<div className="pickup_comments">
				<label>{__('Comments', 'loyalty-program')} <br />
					<textarea onChange={handlePickupComments}></textarea>
				</label>
			</div>
        </div>
	);
};
