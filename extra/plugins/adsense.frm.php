<?php header("Content-Type: text/html; charset=utf-8"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
  <head>
    <title>AdSense</title>
    <style type="text/css">
      body { margin: 0; padding: 0; }
    </style>
  </head>
  <body>
    <script type="text/javascript">
		google_ad_client = "<?php echo htmlspecialchars($_GET['client'], ENT_QUOTES); ?>";
		google_ad_width = <?php echo intval($_GET['width']); ?>;
		google_ad_height = <?php echo intval($_GET['height']); ?>;
		google_ad_format = "<?php echo htmlspecialchars($_GET['format'], ENT_QUOTES); ?>";
		google_ad_type = "<?php echo htmlspecialchars($_GET['type'], ENT_QUOTES); ?>";
		google_ad_channel = "<?php echo htmlspecialchars($_GET['channel'], ENT_QUOTES); ?>";
		//google_page_url = document.location;
		//google_page_url = window.parent.location;
		google_page_url = "<?php echo htmlspecialchars($_GET['url'], ENT_QUOTES); ?>";
		google_color_border = "C8D4E0";
		//google_color_bg = "FFFFFF";
		google_color_bg = "FDFDFD";
		google_color_link = "0050E0";
		google_color_url = "008000";
		google_color_text = "404040";
    </script>
    <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
  </body>
</html>