<?php

    function setFooter($args, ...$scripts){
        $ua = as_object( $args->ua );
?>
    <script src="<?=JS?>jquery.js"></script>
    <script src="<?=JS?>bootstrap.js"></script>
    <script src="<?=JS?>sweetalert2.js"></script>
    <script src="<?=JS?>app.js"></script>
    <?php foreach($scripts as $script) : ?>
        <script src="<?=JS?><?= $script ?>.js"></script>
    <?php endforeach; ?>

    <script>
        $( function(){
            app.user.sv       =  <?= $ua->sv?'true':'false'?>;
            app.user.id       = "<?=$ua->id??''?>"
            app.user.username = "<?=$ua->username??''?>"
            app.user.tipo     = "<?=$ua->tipo??''?>"

        })
    </script>
<?php
    }
    function closeFooter(){?>
        </body>
        </html>
    <?php }