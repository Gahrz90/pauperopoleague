<?php
get_header();
?>

<div class="tappa-archive">
    <h1>Archivio Tappe</h1>
    <?php if (have_posts()): while(have_posts()): the_post(); ?>
        <div class="tappa-item">
            <a href="<?php the_permalink();?>"><?php the_title();?></a>
            <span>Data: <?php echo get_the_date();?></span>
        </div>
    <?php endwhile; else: ?>
        <p>Nessuna tappa trovata.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>