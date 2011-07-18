Retrieving all variables as JSON. Helpful for passing language variables to JavaScript.
<script type="text/javascript">
    lang = <?php echo $this->lang->toJson(); ?>
</script>