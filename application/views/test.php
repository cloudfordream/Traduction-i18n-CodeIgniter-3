<p>
    <?php 
        if($this->lang->lang() == 'en'){
            echo anchor($this->lang->switch_lang('fr'),'Français');
        }elseif($this->lang->lang() == 'fr'){
            echo anchor($this->lang->switch_lang('en'),'English');
        }
	?>


    <?= $this->lang->t('welcome')." <b>John Doe</b>"; ?>
</p>
