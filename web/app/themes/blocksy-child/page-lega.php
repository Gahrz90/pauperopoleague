<?php
get_header();

// Titolo pagina
?>
<h1>Lega</h1>

<?php
// Recupera tutte le tappe dal CPT
$tappe = get_posts([
    'post_type' => 'tappa',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'ASC'
]);

if ($tappe): ?>
    <ul>
    <?php foreach($tappe as $tappa):
        $tappa_id = $tappa->ID;
        $titolo = get_the_title($tappa_id);
        $slug = $tappa->post_name;
        $data = get_post_meta($tappa_id, 'tappa_data', true);
        $codice_wizard = get_post_meta($tappa_id, 'tappa_codice_wizard', true);
        
        // Controllo se la tappa è “aperta” per il form
        $now = new DateTime('now', new DateTimeZone('Europe/Rome'));
        $tappa_data = $data ? new DateTime($data . ' 21:15', new DateTimeZone('Europe/Rome')) : null;
        $aperta = $tappa_data && $now <= $tappa_data;
        ?>
        <li>
            <strong><?php echo $titolo; ?></strong>
            <?php if($data): ?>
                - <?php echo date('d/m/Y', strtotime($data)); ?>
            <?php endif; ?>

            <?php if($aperta): ?>
                <form method="GET" action="<?php echo site_url('/inserisci-deck/'); ?>" style="margin-top:5px;">
                    <label>Inserisci codice Wizard:</label>
                    <input type="text" name="wizard_code" placeholder="Codice Wizard" required>
                    <input type="hidden" name="tappa_slug" value="<?php echo $slug; ?>">
                    <button type="submit">Accedi</button>
                </form>
            <?php else: ?>
                <p style="color:gray;">Tappa chiusa o in corso</p>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Nessuna tappa disponibile.</p>
<?php endif; ?>

<?php
get_footer();