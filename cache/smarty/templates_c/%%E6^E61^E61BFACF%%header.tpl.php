<?php /* Smarty version 2.6.11, created on 2017-10-10 18:09:41
         compiled from modules/Calendar/tpls/header.tpl */ ?>

<?php if ($this->_tpl_vars['controls']): ?>

<div class="clear"></div>

<div style='float:left; width: 50%;'>
<?php $_from = $this->_tpl_vars['tabs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['tabs'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['tabs']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['tab']):
        $this->_foreach['tabs']['iteration']++;
?>
	<input type="button" class="button" <?php if ($this->_tpl_vars['view'] == $this->_tpl_vars['tab']): ?> selected <?php endif; ?> id="<?php echo $this->_tpl_vars['tabs_params'][$this->_tpl_vars['tab']]['id']; ?>
" title="<?php echo $this->_tpl_vars['tabs_params'][$this->_tpl_vars['tab']]['title']; ?>
" value="<?php echo $this->_tpl_vars['tabs_params'][$this->_tpl_vars['tab']]['title']; ?>
" onclick="<?php echo $this->_tpl_vars['tabs_params'][$this->_tpl_vars['tab']]['link']; ?>
">
<?php endforeach; endif; unset($_from); ?>
</div>

<div style="float:left; text-align: right; width: 50%; font-size: 12px;">
	<?php if ($this->_tpl_vars['view'] == 'shared'): ?>
		<button id="userListButtonId" type="button" class="button" onclick="javascript: CAL.toggle_shared_edit('shared_cal_edit');"><?php echo $this->_tpl_vars['MOD']['LBL_EDIT_USERLIST']; ?>
</button>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['view'] != 'year' && ! $this->_tpl_vars['print']): ?>
	<span class="dateTime">
					<img border="0" src="<?php echo $this->_tpl_vars['cal_img']; ?>
" alt="<?php echo $this->_tpl_vars['APP']['LBL_ENTER_DATE']; ?>
" id="goto_date_trigger" align="absmiddle">
					<input type="hidden" id="goto_date" name="goto_date" value="<?php echo $this->_tpl_vars['current_date']; ?>
">
					<script type="text/javascript">
					Calendar.setup (<?php echo '{'; ?>

						inputField : "goto_date",
						ifFormat : "%m/%d/%Y",
						daFormat : "%m/%d/%Y",
						button : "goto_date_trigger",
						singleClick : true,
						dateStr : "<?php echo $this->_tpl_vars['current_date']; ?>
",
						step : 1,
						onUpdate: goto_date_call,
						startWeekday: <?php echo $this->_tpl_vars['start_weekday']; ?>
,
						weekNumbers:false
					<?php echo '}'; ?>
);
					<?php echo '
					YAHOO.util.Event.onDOMReady(function(){
						YAHOO.util.Event.addListener("goto_date","change",goto_date_call);
					});
					function goto_date_call(){
						CAL.goto_date_call();
					}
					'; ?>

					</script>
	</span>
	<?php endif; ?>
	<input type="button" id="cal_settings" class="button" onclick="CAL.toggle_settings()" value="<?php echo $this->_tpl_vars['MOD']['LBL_SETTINGS']; ?>
">
</div>

<div style='clear: both;'></div>

<?php endif; ?>


<div class="<?php if ($this->_tpl_vars['controls']): ?>monthHeader<?php endif; ?>">
	<div style='float: left; width: 20%;'><?php echo $this->_tpl_vars['previous']; ?>
</div>
	<div style='float: left; width: 60%; text-align: center;'><h3><?php echo $this->_tpl_vars['date_info']; ?>
</h3></div>
	<div style='float: right;'><?php echo $this->_tpl_vars['next']; ?>
</div>
	<br style='clear:both;'>
</div>