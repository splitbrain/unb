/* Put your custom CSS definition changes here. */

/** ANDI **/
body
{
<?php if ($ie && !$ie7) { ?>
        width: 870px;
<?php } else { ?>
        width: 95%;
        min-width: 550px;
        max-width: 920px;
<?php } ?>
}

div.head_logo{
  overflow: auto;
}

div.andi_adsense {
  padding: 0;
  margin: 0;
  float: right;
}
div.andi_adsense iframe,
div.andi_adsense object {
  padding: 0;
  margin: 0;
}

body#subforum17 div.post_container div.post_subject,
body#subforum17 div.post_container div.post_body,
body#subforum17 div.path div.thread,
body#subforum17 div.path div.desc,
body#subforum17 div.editor_container div.editor_head input,
body#subforum17 div.editor_container textarea,
body#subforum17 div.thread_container div.thread_data a,
body#subforum17 div.thread_container td.desc
{
  direction: rtl;
}

