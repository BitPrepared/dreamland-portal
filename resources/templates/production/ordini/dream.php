<?php

 function appenStelle($num){
     $res = '';
     for($i=0;$i<$num;$i++){
         $res .= ' <span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
     }
     return $res;
 }

?>


<h1>Ordini di Dreamland</h1>

<?php
//$livelloAssocC['1']
function livello($livelloArrayA,$livelloArrayB,$livelloArrayC,$stelleAssoc) {
?>
<div class="container">

    <div class="col-md-4">
        <ul>
            <?php
            foreach($livelloArrayA as $codCens => $l) {
                $num = 0;
                if ( isset ($stelleAssoc[$codCens])){
                    $num = $stelleAssoc[$codCens];
                }
                echo '<li>'.$l. appenStelle($num). '</li>';
            }
            ?>
        </ul>

    </div>


    <div class="col-md-4">
        <ul>
            <?php
            foreach($livelloArrayB as $codCens => $l) {
                $num = 0;
                if ( isset ($stelleAssoc[$codCens])){
                    $num = $stelleAssoc[$codCens];
                }
                echo '<li>'.$l. appenStelle($num). '</li>';
            }
            ?>
        </ul>

    </div>

    <div class="col-md-4">
        <ul>
            <?php
            foreach($livelloArrayC as $codCens => $l) {
                $num = 0;
                if ( isset ($stelleAssoc[$codCens])){
                    $num = $stelleAssoc[$codCens];
                }
                echo '<li>'.$l. appenStelle($num). '</li>';
            }
            ?>
        </ul>

</div>

</div><!-- /.container -->

<?php
}
?>

<h2>Master</h2>

<?php if ( isset($livelloAssocA['3']) )  livello($livelloAssocA['3'],$livelloAssocB['3'],$livelloAssocC['3'],$stelleAssoc) ; ?>

<h2>Apprendice</h2>

<?php if ( isset($livelloAssocA['2']) )  livello($livelloAssocA['2'],$livelloAssocB['2'],$livelloAssocC['2'],$stelleAssoc) ; ?>

<h2>Junior</h2>

<?php if ( isset($livelloAssocA['1']) )   livello($livelloAssocA['1'],$livelloAssocB['1'],$livelloAssocC['1'],$stelleAssoc) ; ?>



<?php

//array(1) { [1]=> array(1) { [83784]=> string(30) "Sq. Rattispaziali - BOLOGNA 13" } }
//var_dump($livelloAssoc);
//var_dump($stelleAssoc);


?>

