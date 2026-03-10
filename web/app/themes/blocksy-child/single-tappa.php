<?php
/**
 * Template per singola tappa
 */

get_header();

global $post;

// Recupero meta
$wizard_code = get_post_meta($post->ID, 'wizard_code', true);
$tappa_data = get_the_date('', $post);
?>

<div class="tappa-content">
    <h1><?php the_title(); ?></h1>
    <p>Data: <?php echo $tappa_data; ?></p>

    <?php
    // Se la tappa è attiva
    $ora = current_time('timestamp');
    $tappa_start = strtotime($tappa_data . ' 00:00:00');
    if ($ora < $tappa_start) {
        echo "<p>La tappa inizierà il ".date('d/m/Y', $tappa_start)."</p>";
        ?>
        <form method="GET" action="<?php echo site_url('/inserisci-deck/'); ?>">
            <label>Inserisci il codice Wizard:</label>
            <input type="text" name="wizard_code" required>
            <button type="submit">Accedi</button>
        </form>
        <?php
    } else {
        echo '<p>Tappa lega in corso</p>';
    }
    ?>
</div>

<?php get_footer(); ?>