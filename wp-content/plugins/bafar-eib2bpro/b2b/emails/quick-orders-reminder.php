<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_Email')) {
    return;
}

class EIB2BPRO_Quick_Orders_Reminder extends \WC_Email
{

    /**
     * Create an instance of the class.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->id = 'eib2bpro_quick_orders_reminder';
        $this->title = esc_html__('Quick Orders Reminder', 'eib2bpro');
        $this->description = esc_html__('Email to be sent to customer for quick orders reminder', 'eib2bpro');

        $this->heading = esc_html__('Reminder', 'eib2bpro');
        $this->subject = esc_html__('Reminder', 'eib2bpro');

        // Template paths.
        $this->template_base = EIB2BPRO_DIR . 'b2b/emails/templates/';
        $this->template_plain = 'plain-quick-orders-reminder.php';
        $this->template_html = 'quick-orders-reminder.php';

        $this->recipient = $this->get_option('recipient');

        if (!$this->recipient) {
            $this->recipient = get_option('admin_email');
        }

        parent::__construct();

        add_action('eib2bpro_quick_orders_reminder', array($this, 'trigger'), 10, 1);
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

            'subject' => array(
                'title' => 'Subject',
                'type' => 'text',
                'description' => '',
                'placeholder' => $this->subject,
                'default' => $this->subject
            ),
            'heading' => array(
                'title' => esc_html__('Email heading', 'eib2bpro'),
                'type' => 'text',
                'description' => '',
                'placeholder' => $this->heading,
                'default' => $this->heading
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

    public function trigger($post_id)
    {
        $post = get_post($post_id);

        if (!$post) {
            return;
        }

        $user_id = get_post_field('post_author', $post_id);
        $user_data = get_userdata($user_id);
        $this->recipient =  $user_data->user_email;
        $this->post_id = $post_id;

        if (!$this->is_enabled() || !$this->get_recipient()) {
            return;
        }

        do_action('wpml_switch_language_for_email', $this->recipient);

        $this->subject = esc_html__('Reminder', 'eib2bpro');
        $this->heading = sprintf(esc_html__('Reminder for %s', 'eib2bpro'), get_the_title($post_id));

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

        do_action('wpml_restore_language_from_email');
    }

    public function get_content_html()
    {
        ob_start();
        wc_get_template($this->template_html, array(
            'email_heading' => $this->get_heading(),
            'post_id' => $this->post_id,
            'email' => $this,
        ), $this->template_base, $this->template_base);
        return ob_get_clean();
    }

    public function get_content_plain()
    {
        ob_start();
        wc_get_template($this->template_plain, array(
            'email_heading' => $this->get_heading(),
            'post_id' => $this->post_id,
            'email' => $this,
        ), $this->template_base, $this->template_base);
        return ob_get_clean();
    }
}


return new \EIB2BPRO_Quick_Orders_Reminder();
