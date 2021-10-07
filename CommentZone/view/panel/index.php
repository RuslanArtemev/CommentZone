<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo $csrf['token']; ?>">
  <title>Admin Panel</title>
</head>

<body>
  <div id="commentPanel" data-api-path="<?php echo $apiPath; ?>"></div>
  <script src="<?php echo $resource; ?>/panel/js/bootstrap.js"></script>
  <script src="<?php echo $resource; ?>/panel/js/app.js"></script>
</body>

</html>