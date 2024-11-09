jQuery(document).ready(function($) {

    // Install Demo Customers
    function installDemoCustomers() {
        alert('Demo customers installation triggered!');
        $.ajax({
            url: simulator_ajax_obj.ajax_url,
            method: 'POST',
            data: {
                action: 'simulator_install_demo_customers',
                nonce: simulator_ajax_obj.nonce
            },
            success: function(response) {
                alert(response.data);
            },
            error: function(error) {
                alert('An error occurred: ' + error.responseText);
            }
        });
    }
    $('body').on('click', '#installDemoCustomers', installDemoCustomers);

    // Install Sample Products
    function installSampleProducts() {
        alert('Sample products installation triggered!');
        $.ajax({
            url: simulator_ajax_obj.ajax_url,
            method: 'POST',
            data: {
                action: 'simulator_install_sample_products',
                nonce: simulator_ajax_obj.nonce
            },
            success: function(response) {
                alert(response.data);
            },
            error: function(error) {
                alert('An error occurred: ' + error.responseText);
            }
        });
    }
    $('body').on('click', '#installSampleProducts', installSampleProducts);

    // Install Demo Rankings
    function installDemoRankings() {
        alert('Demo rankings installation triggered!');
        $.ajax({
            url: simulator_ajax_obj.ajax_url,
            method: 'POST',
            data: {
                action: 'simulator_assign_random_rankings',
                nonce: simulator_ajax_obj.nonce
            },
            success: function(response) {
                alert(response.data);
            },
            error: function(error) {
                alert('An error occurred: ' + error.responseText);
            }
        });
    }
    $('body').on('click', '#installDemoRankings', installDemoRankings);

});

