<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_Email')) {
    return;
}

class EIB2BPRO_New_Customer extends \WC_Email
{

    /**
     * Create an instance of the class.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->id = 'eib2bpro_new_customer';
        $this->title = esc_html__('New customer ', 'eib2bpro');
        $this->description = esc_html__('Email to be sent to admin when a new customer registers', 'eib2bpro');

        $this->heading = esc_html__('New customer', 'eib2bpro');
        $this->subject = esc_html__('New customer', 'eib2bpro');

        // Template paths.
        $this->template_base = EIB2BPRO_DIR . 'b2b/emails/templates/';
        $this->template_plain = 'plain-new-customer.php';
        $this->template_html = 'new-customer.php';

        $this->recipient = $this->get_option('recipient');

        if (!$this->recipient) {
            $this->recipient = get_option('admin_email');
        }
        add_action('woocommerce_created_customer_notification', array($this, 'trigger'), 10, 3);

        parent::__construct();
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => esc_html__('Enable/Disable', 'eib2bpro'),
                'type' => 'checkbox',
                'label' => esc_html__('Enable this email notification', 'eib2bpro'),
                'default' => 'yes',
            ),
            'recipient' => array(
                'title' => esc_html__('Recipient(s)', 'eib2bpro'),
                'type' => 'text',
                'description' => esc_html__('If you want to send it to more than one recipient, you can add a comma between them', 'eib2bpro'),
                'placeholder' => esc_attr(get_option('admin_email')),
                'default' => esc_attr(get_option('admin_email'))
            ),
            'subject' => array(
                'title' => 'Subject',
                'type' => 'text',
                'description' => '',
                'placeholder' => $this->subject,
                'default' => ''
            ),
            'heading' => array(
                'title' => esc_html__('Email heading', 'eib2bpro'),
                'type' => 'text',
                'description' => '',
                'placeholder' => $this->heading,
                'default' => ''
            ),
            'email_type' => array(
                'title' => esc_html__('Email type', 'eib2bpro'),
                'type' => 'select',
                'description' => esc_html__('Choose which format of email to send.', 'eib2bpro'),
                'default' => 'html',
                'class' => 'email_type',
                'options' => array(
                    'plain' => 'Plain text',
                    'html' => 'HTML', 'woocommerce',
                    'multipart' => 'Multipart', 'woocommerce',
                )
            )
        );
    }

    public function trigger($customer_id, $data, $password)
    {
        if (!$this->is_enabled() || !$this->get_recipient()) {
            return;
        }

        $this->object = new \WP_User($customer_id);
        $this->user_login = stripslashes($this->object->user_login);

        if ('yes' !== get_user_meta($customer_id, 'eib2bpro_user_approved', true)) {
            $this->subject = esc_html__('New customer is waiting for your approval', 'eib2bpro');
            $this->heading = esc_html__('New customer is waiting for your approval', 'eib2bpro');
        } else {
            $this->subject = $this->get_subject();
            $this->heading = $this->get_heading();
        }
        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    public function get_content_html()
    {
        ob_start();
        wc_get_template($this->template_html, array(
            'email_heading' => $this->get_heading(),
            'user_login' => $this->user_login,
            'email' => $this,
        ), $this->template_base, $this->template_base);
        return ob_get_clean();
    }

    public function get_content_plain()
    {
        ob_start();
        wc_get_template($this->template_plain, array(
            'email_heading' => $this->get_heading(),
            'user_login' => $this->user_login,
            'email' => $this,
        ), $this->template_base, $this->template_base);
        return ob_get_clean();
    }
}


return new \EIB2BPRO_New_Customer();
