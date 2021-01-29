<footer>
  <span><a href="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__) . "/imprint.php" ?>"><?php echo $lang->imprint ?></a></span>
  <span><?php echo $lang->title ?> v1.4.0 <?php echo "$lang->on" ?> <a href="https://github.com/m4lt3/theatre_planner" target="_blank"><i class="github icon"></i></a></span>
  <span><a href="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__) . "/privacy.php" ?>"><?php echo $lang->privacy ?></a></span>
</footer>

<script src="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__)?>/js/jquery-3.5.1.min.js" charset="utf-8"></script>
<script src="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__)?>/js/semantic.min.js" charset="utf-8"></script>
<script src="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__)?>/js/updateNav.js" charset="utf-8"></script>
<script type="text/javascript">
  updateNav("<?php echo str_replace(".php","", str_replace(dirname($_SERVER["SCRIPT_FILENAME"])."/","",$_SERVER["SCRIPT_FILENAME"]))?>");
</script>
