<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <p>Moneo key used: <strong><?php echo MONEO_KEY; ?></strong></p>
    <p>Moneo company ID used: <strong><?php echo MONEO_COMPANY_ID; ?></strong></p>

    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
        <input type="hidden" name="action" value="moneo_price_update">
        <?php wp_nonce_field( 'update-prices_'. uniqid());?>
        <input type="submit" value="Update Moneo product prices">
    </form>
</div>