
<div ui-view="footer"></div>

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
	if ( isset($_SESSION['wordpress']) ) {
		echo '<a href="'.urldecode($_SESSION['wordpress']['logout_url']).'">Logout</a>';
	} else {
		echo '<a href="'.$wordpress['url'].'wp-login.php'.'">Login</a>';
	}
?>
        | <?=$footerText?>
		</p>
      </div>
    </footer>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?=$baseUrl?>assets/js/ie10-viewport-bug-workaround.js"></script>

    <!-- external lib -->
    <script src="<?=$baseUrl?>assets/js/dist/underscore-min.js"  type="text/javascript"></script>

    <!-- angularjs -->
    <script src="<?=$baseUrl?>assets/js/dist/angular.min.js"  type="text/javascript"></script>
    <script src="<?=$baseUrl?>assets/js/dist/angular-ui-router.min.js"  type="text/javascript"></script>
    <script src="<?=$baseUrl?>assets/js/dist/angular-locale_it-it.js"></script>
    <!-- <script src="<?=$baseUrl?>assets/js/dist/angular-route.min.js"  type="text/javascript"></script> -->
    <script src="<?=$baseUrl?>assets/js/dist/angular-sanitize.min.js"  type="text/javascript"></script>
    <script src="<?=$baseUrl?>assets/js/dist/angular-animate.min.js"  type="text/javascript"></script>
    <script src="<?=$baseUrl?>assets/js/dist/angular-busy.min.js"  type="text/javascript"></script>
    <script src="<?=$baseUrl?>assets/js/dist/ngDialog.min.js"  type="text/javascript"></script>

    <!-- angularj<?=$baseUrl?>s bootstrap -->
    <script src="<?=$baseUrl?>assets/js/dist/ui-bootstrap.min.js"  type="text/javascript"></script>
    <script src="<?=$baseUrl?>assets/js/dist/ui-bootstrap-tpls.min.js"  type="text/javascript"></script>

    <script src="<?=$baseUrl?>assets/js/dist/moment-with-locales.min.js"  type="text/javascript"></script>

    <!-- my app -->
    <script src="<?=$baseUrl?>app/app.js" type="text/javascript"></script>

    <script src="<?=$baseUrl?>app/ApplicationController.js" type="text/javascript"></script>

    <script src="<?=$baseUrl?>app/Authentication/AuthService.js" type="text/javascript"></script>
    <script src="<?=$baseUrl?>app/Authentication/SessionService.js" type="text/javascript"></script>
    <script src="<?=$baseUrl?>app/Portal/PortalService.js" type="text/javascript"></script>
    <script src="<?=$baseUrl?>app/Authentication/AuthInterceptor.js" type="text/javascript"></script>
    <script src="<?=$baseUrl?>app/Authentication/LoginController.js" type="text/javascript"></script>

    <script src="<?=$baseUrl?>app/Header/HeaderController.js" type="text/javascript"></script>
    <script src="<?=$baseUrl?>app/Registration/RegistrationController.js" type="text/javascript"></script>
    <script src="<?=$baseUrl?>app/Sfide/SfideController.js" type="text/javascript"></script>
    <script src="<?=$baseUrl?>app/Flash/FlashController.js" type="text/javascript"></script>
    <script src="<?=$baseUrl?>app/Registration/CalculatorController.js" type="text/javascript"></script>

</body>
</html>