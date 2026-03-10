<?php
/**
 * Pagina per inserimento decklist giocatore
 */

get_header();

// Recupero dati GET
$wizard_code = sanitize_text_field($_GET['wizard_code'] ?? '');

// Trovo la tappa tramite codice wizard
$args = [
    'post_type' => 'tappa',
    'meta_key' => 'wizard_code',
    'meta_value' => $wizard_code,
    'numberposts' => 1
];

$tappa = get_posts($args);

if (!$tappa) {
    echo '<p>Tappa non valida o codice wizard errato.</p>';
    get_footer();
    exit;
}

$tappa_id = $tappa[0]->ID;

// Nome giocatore
$player_name = sanitize_text_field($_POST['player_name'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $player_name) {

    $decklist = [];
    // Main deck 60
    for ($i = 1; $i <= 60; $i++) {
        $name = sanitize_text_field($_POST["card_name_$i"] ?? '');
        $count = intval($_POST["card_count_$i"] ?? 0);
        if ($name) {
            $decklist['main'][] = ['name'=>$name, 'count'=>$count];
        }
    }

    // Sideboard 15
    for ($i = 1; $i <= 15; $i++) {
        $name = sanitize_text_field($_POST["side_name_$i"] ?? '');
        $count = intval($_POST["side_count_$i"] ?? 0);
        if ($name) {
            $decklist['side'][] = ['name'=>$name, 'count'=>$count];
        }
    }

    // Validazioni
    $total_main = array_sum(array_map(fn($c)=>$c['count'], $decklist['main'] ?? []));
    $total_side = array_sum(array_map(fn($c)=>$c['count'], $decklist['side'] ?? []));

    if ($total_main != 60 || $total_side > 15) {
        echo '<p>Deck non valido: main 60 carte, sideboard max 15.</p>';
    } else {
        // Salvo meta
        update_post_meta($tappa_id, 'decklist_'.$player_name, $decklist);
        echo '<p>Decklist salvata con successo!</p>';
    }
}

// Form inserimento decklist
?>

<div class="decklist-form">
    <h2>Inserisci la tua decklist</h2>
    <form method="POST">
        <label>Nome giocatore:</label>
        <input type="text" name="player_name" required>

        <h3>Main Deck (60 carte)</h3>
        <?php for($i=1;$i<=60;$i++): ?>
            <div>
                <input type="text" name="card_name_<?php echo $i;?>" placeholder="Nome carta <?php echo $i;?>" class="autocomplete-card">
                <input type="number" name="card_count_<?php echo $i;?>" placeholder="N copie" max="4">
            </div>
        <?php endfor; ?>

        <h3>Sideboard (15 carte)</h3>
        <?php for($i=1;$i<=15;$i++): ?>
            <div>
                <input type="text" name="side_name_<?php echo $i;?>" placeholder="Nome carta <?php echo $i;?>" class="autocomplete-card">
                <input type="number" name="side_count_<?php echo $i;?>" placeholder="N copie" max="4">
            </div>
        <?php endfor; ?>

        <button type="submit">Salva Decklist</button>
    </form>
</div>

<?php get_footer(); ?>