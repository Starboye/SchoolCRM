<?php
/** Student page shell — set $pageTitle before including. */
if (!isset($pageTitle)) {
    $pageTitle = 'Student Portal';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?= page_title($pageTitle) ?></title>
  <?php brand_head_tags(); ?>
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,600,700" rel="stylesheet">
  <?php portal_vendor_styles(); ?>
</head>
<body>
<?php portal_boot_screen(); ?>
