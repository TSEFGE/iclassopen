<?php /* Smarty version 2.6.11, created on 2017-10-10 18:09:41
         compiled from modules/Calendar/tpls/main.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'sugar_getjspath', 'modules/Calendar/tpls/main.tpl', 38, false),array('function', 'sugar_getscript', 'modules/Calendar/tpls/main.tpl', 39, false),array('function', 'counter', 'modules/Calendar/tpls/main.tpl', 98, false),)), $this); ?>
<link type="text/css" href="<?php echo smarty_function_sugar_getjspath(array('file' => "modules/Calendar/Cal.css"), $this);?>
" rel="stylesheet" />
<?php echo smarty_function_sugar_getscript(array('file' => "modules/Calendar/Cal.js"), $this);?>

<script type="text/javascript">

	<?php echo '
	YAHOO.util.Event.onDOMReady(function(){
		dom_loaded = true;
	});
	
	function check_cal_loaded(){
		return (typeof cal_loaded != \'undefined\' && cal_loaded == true && typeof dom_loaded != \'undefined\' && dom_loaded == true);
	}
	'; ?>

	
	SUGAR.util.doWhen(check_cal_loaded, function()<?php echo '{'; ?>

	
		CAL.view = "<?php echo $this->_tpl_vars['view']; ?>
";
		CAL.style = "<?php echo $this->_tpl_vars['style']; ?>
";
		CAL.t_step = <?php echo $this->_tpl_vars['t_step']; ?>
;
		CAL.current_user_id = "<?php echo $this->_tpl_vars['current_user_id']; ?>
";	
		CAL.current_user_name = "<?php echo $this->_tpl_vars['current_user_name']; ?>
";
		CAL.time_format = "<?php echo $this->_tpl_vars['time_format']; ?>
";
		CAL.enable_repeat = "<?php echo $this->_tpl_vars['enable_repeat']; ?>
";
		CAL.items_draggable = "<?php echo $this->_tpl_vars['items_draggable']; ?>
";
		CAL.items_resizable = "<?php echo $this->_tpl_vars['items_resizable']; ?>
";
		CAL.cells_per_day = <?php echo $this->_tpl_vars['cells_per_day']; ?>
;	
		CAL.current_params = <?php echo '{}'; ?>
;
		CAL.dashlet = "<?php echo $this->_tpl_vars['dashlet']; ?>
";		
		CAL.grid_start_ts = <?php echo $this->_tpl_vars['grid_start_ts']; ?>
;
		CAL.scroll_slot = <?php echo $this->_tpl_vars['scroll_slot']; ?>
;
		CAL.basic.min_height = <?php echo $this->_tpl_vars['basic_min_height']; ?>
;

		CAL.lbl_create_new = "<?php echo $this->_tpl_vars['MOD']['LBL_CREATE_NEW_RECORD']; ?>
";
		CAL.lbl_edit = "<?php echo $this->_tpl_vars['MOD']['LBL_EDIT_RECORD']; ?>
";
		CAL.lbl_saving = "<?php echo $this->_tpl_vars['MOD']['LBL_SAVING']; ?>
";
		CAL.lbl_loading = "<?php echo $this->_tpl_vars['MOD']['LBL_LOADING']; ?>
";
		CAL.lbl_sending = "<?php echo $this->_tpl_vars['MOD']['LBL_SENDING_INVITES']; ?>
";
		CAL.lbl_confirm_remove = "<?php echo $this->_tpl_vars['MOD']['LBL_CONFIRM_REMOVE']; ?>
";
		CAL.lbl_confirm_remove_all_recurring = "<?php echo $this->_tpl_vars['MOD']['LBL_CONFIRM_REMOVE_ALL_RECURRING']; ?>
";
		
		CAL.lbl_error_saving = "<?php echo $this->_tpl_vars['MOD']['LBL_ERROR_SAVING']; ?>
";
		CAL.lbl_error_loading = "<?php echo $this->_tpl_vars['MOD']['LBL_ERROR_LOADING']; ?>
";
		CAL.lbl_repeat_limit_error = "<?php echo $this->_tpl_vars['MOD']['LBL_REPEAT_LIMIT_ERROR']; ?>
";
		
		CAL.year = <?php echo $this->_tpl_vars['year']; ?>
;
		CAL.month = <?php echo $this->_tpl_vars['month']; ?>
;
		CAL.day = <?php echo $this->_tpl_vars['day']; ?>
;

		CAL.print = <?php echo $this->_tpl_vars['isPrint']; ?>
;
		
		<?php echo '
		var scrollable = CAL.get("cal-scrollable");
		if(scrollable){
			scrollable.scrollTop = (CAL.slot_height + 1) * CAL.scroll_slot - 1;
			if(CAL.view == "day")
				scrollable.scrollTop++;
		}
		'; ?>
			

		<?php if ($this->_tpl_vars['view'] == 'shared'): ?>
			<?php echo smarty_function_counter(array('name' => 'un','start' => 0,'print' => false,'assign' => 'un'), $this);?>

			<?php $_from = $this->_tpl_vars['shared_ids']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['shared'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['shared']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['member_id']):
        $this->_foreach['shared']['iteration']++;
?>				
				CAL.shared_users['<?php echo $this->_tpl_vars['member_id']; ?>
'] = '<?php echo $this->_tpl_vars['un']; ?>
';
				<?php echo smarty_function_counter(array('name' => 'un','print' => false), $this);?>

			<?php endforeach; endif; unset($_from); ?>
			CAL.shared_users_count = "<?php echo $this->_tpl_vars['shared_users_count']; ?>
";
		<?php endif; ?>
	
		CAL.field_list = new Array();
		CAL.field_disabled_list = new Array();			

		CAL.activity_colors = [];				
		<?php $_from = $this->_tpl_vars['activity_colors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['colors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['colors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['module'] => $this->_tpl_vars['v']):
        $this->_foreach['colors']['iteration']++;
?>
			CAL.activity_colors['<?php echo $this->_tpl_vars['module']; ?>
'] = [];
			CAL.activity_colors['<?php echo $this->_tpl_vars['module']; ?>
']['border'] = '<?php echo $this->_tpl_vars['v']['border']; ?>
';
			CAL.activity_colors['<?php echo $this->_tpl_vars['module']; ?>
']['body'] = '<?php echo $this->_tpl_vars['v']['body']; ?>
'
		<?php endforeach; endif; unset($_from); ?>

		CAL.act_types = [];
		CAL.act_types['Meetings'] = 'meeting';
		CAL.act_types['Calls'] = 'call';
		CAL.act_types['Tasks'] = 'task';

		<?php echo '

		if(CAL.items_draggable){			
			var target_slots = [];			
			var slots = CAL.query(\'#cal-grid div.slot\');
			var cnt = 0;
			CAL.each(
				slots,
				function(i,v){					
					target_slots[i] = new YAHOO.util.DDTarget(slots[i].id,"cal");
					cnt++;
				}
			);
			slots = CAL.query(\'#cal-grid div.basic_slot\');
			CAL.each(
				slots,
				function(i,v){
					target_slots[cnt + i] = new YAHOO.util.DDTarget(slots[i].id,"basic_cal");
				}
			);				
		}	
		
		var nodes = CAL.query("#cal-grid div.slot, #cal-grid div.basic_slot");
		CAL.each(nodes, function(i,v){
			YAHOO.util.Event.on(nodes[i],"mouseover",function(){
				if(CAL.records_openable && !CAL.disable_creating)
					this.style.backgroundColor = "#D1DCFF";							
				if(!this.childNodes.length)	
					this.setAttribute("title",this.getAttribute("time"));
			});
			YAHOO.util.Event.on(nodes[i],"mouseout",function(){
				this.style.backgroundColor = "";
				this.removeAttribute("title");
			});
			YAHOO.util.Event.on(nodes[i],"click",function(){
				if(!CAL.disable_creating){							
					CAL.dialog_create(this);
				}
			});
		});				
		
		CAL.init_edit_dialog({
			width: "';  echo $this->_tpl_vars['editview_width'];  echo '",
			height: "';  echo $this->_tpl_vars['editview_height'];  echo '"
		});
		
		YAHOO.util.Event.on(window, \'resize\', function(){
			CAL.fit_grid();
			CAL.update_dd.fire();
		});		
				
		YAHOO.util.Event.on("btn-save","click",function(){																	
			if(!CAL.check_forms())
				return false;											
			CAL.dialog_save();	
		});
		
		YAHOO.util.Event.on("btn-send-invites","click",function(){																				
			if(!CAL.check_forms())
				return false;	
			CAL.get("send_invites").value = "1";							
			CAL.dialog_save();	
		});		
				
		YAHOO.util.Event.on("btn-delete","click",function(){
			if(CAL.get("record").value != "")
				if(confirm(CAL.lbl_confirm_remove))
					CAL.dialog_remove();
						
		});	
	
		YAHOO.util.Event.on("btn-cancel","click",function(){			
			document.schedulerwidget.reset();
            if(document.getElementById(\'empty-search-message\')) {
                document.getElementById(\'empty-search-message\').style.display = \'none\';
            }
            CAL.editDialog.cancel();						
		}); 
		
		YAHOO.util.Event.on("btn-full-form","click",function(){			
			CAL.full_form();						
		}); 

		CAL.select_tab("cal-tab-1");

		YAHOO.util.Event.on(CAL.get("btn-cancel-settings"), \'click\', function(){
			CAL.settingsDialog.cancel();	
		});
		
		YAHOO.util.Event.on(CAL.get("btn-save-settings"), \'click\', function(){			
			CAL.get("form_settings").submit();
		});
		
		'; ?>

				
		var calendar_items = <?php echo $this->_tpl_vars['a_str']; ?>
;
					
		<?php echo '
		CAL.each(calendar_items, function(i,v){
			CAL.add_item_to_grid(calendar_items[i]);
		});
		'; ?>

		
		<?php if ($this->_tpl_vars['view'] != 'year'): ?>
		CAL.arrange_advanced();
		CAL.basic.populate_grid();		
		CAL.fit_grid();
		CAL.update_dd.fire();
		<?php endif; ?>
		
		cal_loaded = null;	
	});
</script>
			
<div id="cal-edit" style="display: none;">
	
	<div class="hd"><span id="title-cal-edit"></span></div>
	<div class="bd" id="edit-dialog-content">
		<div id="cal-tabs" class="yui-navset yui-navset-top yui-content" style="height: auto; padding: 0 2px;">
			<ul class="yui-nav">
				<li id="tab_general"><a tabname="cal-tab-1" id="cal-tab-1-link"><em><?php echo $this->_tpl_vars['MOD']['LBL_GENERAL_TAB']; ?>
</em></a></li>
				<li id="tab_invitees"><a tabname="cal-tab-2" id="cal-tab-2-link"><em><?php echo $this->_tpl_vars['MOD']['LBL_PARTICIPANTS_TAB']; ?>
</em></a></li>
				<?php if ($this->_tpl_vars['enable_repeat']): ?>
				<li id="tab_repeat"><a tabname="cal-tab-3" id="cal-tab-3-link"><em><?php echo $this->_tpl_vars['MOD']['LBL_REPEAT_TAB']; ?>
</em></a></li>
				<?php endif; ?>
			</ul>
			<div id="cal-tab-1" class="yui-content">
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['form'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</div>				
			<div id="cal-tab-2" class="yui-content">
				<div class="h3Row" id="scheduler"></div>
			</div>
			<?php if ($this->_tpl_vars['enable_repeat']): ?>
			<div id="cal-tab-3" class="yui-content">
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['repeat'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</div>
			<?php endif; ?>
		</div>
	</div>	
	<div id="cal-edit-buttons" class="ft">
		<button id="btn-save" class="button" type="button"><?php echo $this->_tpl_vars['MOD']['LBL_SAVE_BUTTON']; ?>
</button>
		<button id="btn-cancel" class="button" type="button"><?php echo $this->_tpl_vars['MOD']['LBL_CANCEL_BUTTON']; ?>
</button>
		<button id="btn-delete" class="button" type="button"><?php echo $this->_tpl_vars['MOD']['LBL_DELETE_BUTTON']; ?>
</button>
		<button id="btn-send-invites" class="button" type="button"><?php echo $this->_tpl_vars['MOD']['LBL_SEND_INVITES']; ?>
</button>
		<button id="btn-full-form" class="button" type="button"><?php echo $this->_tpl_vars['APP']['LBL_FULL_FORM_BUTTON_LABEL']; ?>
</button>
	</div>
</div>

<?php if ($this->_tpl_vars['settings']):  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['settings'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>
	
<script type="text/javascript">	
<?php echo $this->_tpl_vars['GRjavascript']; ?>

</script>
	
<script type="text/javascript">	
<?php echo '
YAHOO.util.Event.onDOMReady(function(){	
	var schedulerLoader = new YAHOO.util.YUILoader({
		require : ["jsclass_scheduler"],
		onSuccess: function(){
			var root_div = document.getElementById(\'scheduler\');
			var sugarContainer_instance = new SugarContainer(document.getElementById(\'scheduler\'));
			sugarContainer_instance.start(SugarWidgetScheduler);
		}
	});
	schedulerLoader.addModule({
		name :"jsclass_scheduler",
		type : "js",
		fullpath: "modules/Meetings/jsclass_scheduler.js",
		varName: "global_rpcClient",
		requires: []
	});
	schedulerLoader.insert();
});	
'; ?>
	
</script>
	
<script type="text/javascript" src="include/javascript/jsclass_base.js"></script>
<script type="text/javascript" src="include/javascript/jsclass_async.js"></script>	
	
<style type="text/css">
<?php echo '
	.schedulerDiv h3{
		display: none;
	}
	.schedulerDiv{
		width: auto !important;
	}
'; ?>

</style>	
<?php if ($this->_tpl_vars['view'] == 'day'): ?>
<style type="text/css">
<?php echo '
	#cal-grid div.col, #cal-grid div.left_col{
		border-top: 1px solid silver;	
	}
'; ?>

</style>
<?php endif; ?>

<div id="cal-width-helper" style="width: auto;"></div>
