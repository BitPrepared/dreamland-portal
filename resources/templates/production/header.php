<!doctype html>
<html lang="en" ng-app="dreamApp">
<head>
  <meta charset="utf-8">
  <title><?=$title?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="<?=$baseUrl?>assets/css/bootstrap_base.css" rel="stylesheet"/>
    <!-- mystyle -->
    <link href="<?=$baseUrl?>assets/css/styles.css" rel="stylesheet"/>
    <link href="<?=$baseUrl?>assets/css/dist/ngDialog.min.css" rel="stylesheet"/>
    <link href="<?=$baseUrl?>assets/css/dist/ngDialog-theme-plain.min.css" rel="stylesheet"/>
    <link href="<?=$baseUrl?>assets/css/dist/ngDialog-theme-default.min.css" rel="stylesheet"/>
    <link href="<?=$baseUrl?>assets/css/dist/angular-busy.min.css" rel="stylesheet"/>

    <link href="<?=$baseUrl?>assets/js/dist/ui-bootstrap.min.js" rel="stylesheet"/>
    <link href="<?=$baseUrl?>assets/js/dist/ui-bootstrap-tpls.min.js" rel="stylesheet"/>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

   <!--

      Hey, Stai cercando di fare Reverse Engineering?
      Perche' non ti unisci a noi ?

      Cercarci su https://github.com/BitPrepared :)

      o vai sul progetto: https://github.com/BitPrepared/dreamland-portal

    -->

</head>
<body ng-controller="ApplicationController">

<div class="container">

    <div ui-view="navbar"></div>

    <!-- Use the simple syntax https://github.com/cgross/angular-busy -->
    <div cg-busy="{promise:remoteLoad}">&nbsp;</div>
    <!-- Use the advanced syntax -->
    <!-- <div cg-busy="{promise:myPromise,message:'Loading Your Data',templateUrl:'mycustomtemplate.html'}"></div> -->

    <div ng-if="currentUser">Welcome, {{ currentUser.name }}</div>
