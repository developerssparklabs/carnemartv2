import { getSetting } from '@woocommerce/settings';
import { __ } from '@wordpress/i18n';

export const Block = ( { checkoutExtensionData, extensions } ) => {
	var { points, username, ajax_url, apply_coupon_nonce }  = getSetting( 'share-cart-checkout_data');
	if ( points < 150 ) {
		return;
	}
	const radioButtons = document.querySelectorAll('.lp_points');

	radioButtons.forEach((radio) => {
		if (radio.value === localStorage.getItem('selectedCoupon')) {
			radio.checked = true;
		}
	});
	const rewardOptions = [
		{ percentage: '5%', pointsRequired: 150 },
		{ percentage: '10%', pointsRequired: 500 },
		{ percentage: '15%', pointsRequired: 750 },
		{ percentage: '20%', pointsRequired: 1000 }
	];
	
	const ApplyCoupon = ( obj ) => {
		var selectedPoints = obj.target.value;
		localStorage.setItem('selectedCoupon', selectedPoints);
		var couponcode = '';
		if ( selectedPoints >= 150 && selectedPoints < 499 ) {
			couponcode = 'aprgsqfb';
		} else if ( selectedPoints >= 500 && selectedPoints < 750 ) {
			couponcode = 'mpnbvmkr';
		} else if ( selectedPoints >= 750 && selectedPoints < 1000 ) {
			couponcode = 'VZKRTWHQ'
		}else {
			couponcode = 'nceybpp2';
		}
		  const formData = new FormData();
		  formData.append('coupon_code', couponcode);
		  formData.append('security', apply_coupon_nonce); // Nonce for security
		  formData.append('action', 'lp_apply_coupon'); // WooCommerce action
		  fetch(woocommerce_params.ajax_url, {
			method: 'POST',
			body: formData,
		  })
			.then((response) => response.json())
			.then((data) => {
			  if (data.success) {
				wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
				// setMessage('Coupon applied successfully!');
			  } else {
				// setMessage(data.data || 'Coupon application failed.');
			  }
			})
			.catch((error) => {
			//   setMessage('An error occurred.');
			  console.error('Error:', error);
			});
	};
	return (
		<div className="sb_checkout_offer">
			<p>{username} has {points} points</p>
			<p>Subheading here</p>
			<ul style={{ listStyleType: "none" }}>
      {rewardOptions.map((option, index) => {
        if (option.pointsRequired <= points) {
          return (
            <li key={index}>
              <label>
                <input type="radio" name="lp_get_discount" onClick={ApplyCoupon} className="lp_points" value={option.pointsRequired} />
                {`${option.percentage} for ${option.pointsRequired} points`}
              </label>
            </li>
          );
        }
        return null;
      })}
    </ul>
		</div>
	);
};