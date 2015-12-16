
    </div>
    <div class="container">
        <div ui-view="footer"></div>
    </div>
</div> <!-- container principale -->


<script type="text/ng-template" id="modalDialogId">
    <div class="ngdialog-message bg-danger">
        <h3>Errore</h3>
        <p>{{currentError}}</p>
    </div>
    <div class="ngdialog-buttons">
        <button type="button" class="ngdialog-button ngdialog-button-secondary" ng-click="closeThisDialog('button')">Ok</button>
    </div>
</script>

<footer class="footer">
      <div class="container">
        <p class="text-muted">
	
<?php 
    if (isset($_SESSION['wordpress'])) {
        echo '<a href="'.urldecode($_SESSION['wordpress']['logout_url']).'">Logout</a>';
    } else {
        echo '<a href="'.$wordpress['url'].'wp-login.php'.'">Login</a>';
    }
?>
        | <?=$footerText?>
		</p>
      </div>
    </footer>

    <script data-main="<?=$baseUrl?>app/app.js" src="<?=$baseUrl?>assets/js/dist/require.js"></script>

</body>
</html>