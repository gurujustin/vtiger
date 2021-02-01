<?php /* Smarty version Smarty-3.1.7, created on 2021-01-10 12:06:51
         compiled from "/mnt/data/home/517557.cloudwaysapps.com/vxwnhjupfc/public_html/includes/runtime/../../layouts/v7/modules/Settings/ExtensionStore/ExtensionCompatibleError.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2219523325ffaeddbc84ff6-91489361%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ff57899102f146f253ae68058ea6b687f2cec1f1' => 
    array (
      0 => '/mnt/data/home/517557.cloudwaysapps.com/vxwnhjupfc/public_html/includes/runtime/../../layouts/v7/modules/Settings/ExtensionStore/ExtensionCompatibleError.tpl',
      1 => 1610209105,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2219523325ffaeddbc84ff6-91489361',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'EXTENSION_LABEL' => 0,
    'QUALIFIED_MODULE' => 0,
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5ffaeddbc8a48',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ffaeddbc8a48')) {function content_5ffaeddbc8a48($_smarty_tpl) {?>

<div style="min-height: 600px;"><table border="0" cellpadding="5" cellspacing="0" width="100%" style="margin-top: 100px;"><tr><td align="center"><div style="border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 40%; position: relative;padding-right: 3px;"><table border="0" cellpadding="5" cellspacing="0" width="98%"><tbody><tr><td rowspan="2"><img src="<?php echo vimage_path("denied.gif");?>
" style="margin: 10px;"></td><td width="80%" style="border-bottom: 1px solid rgb(204, 204, 204);"><span class="genHeaderSmall"><b><?php echo vtranslate($_smarty_tpl->tpl_vars['EXTENSION_LABEL']->value,$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</b> <?php echo ucfirst(strtolower(vtranslate('LBL_EXTENSION_NOT_COMPATABLE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value)));?>
</span></td></tr><tr><td class="small" align="right" nowrap="nowrap"><a href="index.php?module=ExtensionStore&parent=Settings&view=ExtensionStore"><?php echo vtranslate('LBL_GO_BACK',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a><br></td></tr></tbody></table></div></td></tr></table><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("CardSetupModals.tpl",$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('QUALIFIED_MODULE'=>$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value), 0);?>
</div><?php }} ?>