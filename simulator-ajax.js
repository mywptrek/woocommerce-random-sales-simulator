jQuery(document).ready(function($) {
    function installDemoCustomers() {
        $.post(simulator_ajax_obj.ajax_url, {
            action: 'install_demo_customers',
            nonce: simulator_ajax_obj.nonce
        }, function(response) {
            alert(response.data);
        });
    }

    function installSampleProducts() {
        $.post(simulator_ajax_obj.ajax_url, {
            action: 'install_sample_products',
            nonce: simulator_ajax_obj.nonce
        }, function(response) {
            alert(response.data);
        });
    }

    window.installDemoCustomers = installDemoCustomers;
    window.installSampleProducts = installSampleProducts;
});
