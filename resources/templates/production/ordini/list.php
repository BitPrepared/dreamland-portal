<?php

 function appenStelle($numSfideConquistate)
 {
     $res = '';
     if ($numSfideConquistate > 0) {
         $res .= ' <span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
     }
     if ($numSfideConquistate > 2) {
         $res .= ' <span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
     }
     if ($numSfideConquistate > 4) {
         $res .= ' <span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
     }

     return $res;
 }

?>

<h1>Ordini di Dreamland</h1>

<?php
//$livelloAssocC['1']
function livello($livelloArrayA, $livelloArrayB, $livelloArrayC, $stelleAssoc)
{
    ?>
<div class="container">

    <div class="col-md-4">
        <ul>
            <?php
            foreach ($livelloArrayA as $codCens => $l) {
                $num = 0;
                if (isset($stelleAssoc[$codCens])) {
                    $num = $stelleAssoc[$codCens];
                }
                echo '<li>'.$l.appenStelle($num).'</li>';
            }
    ?>
        </ul>

    </div>


    <div class="col-md-4">
        <ul>
            <?php
            foreach ($livelloArrayB as $codCens => $l) {
                $num = 0;
                if (isset($stelleAssoc[$codCens])) {
                    $num = $stelleAssoc[$codCens];
                }
                echo '<li>'.$l.appenStelle($num).'</li>';
            }
    ?>
        </ul>

    </div>

    <div class="col-md-4">
        <ul>
            <?php
            foreach ($livelloArrayC as $codCens => $l) {
                $num = 0;
                if (isset($stelleAssoc[$codCens])) {
                    $num = $stelleAssoc[$codCens];
                }
                echo '<li>'.$l.appenStelle($num).'</li>';
            }
    ?>
        </ul>

</div>

</div><!-- /.container -->

<?php

}
?>

<h2><?=$role?> Dreamer</h2>

<?php if (isset($livelloAssocA)) {
    livello($livelloAssocA, $livelloAssocB, $livelloAssocC, $stelleAssoc);
} ?>

