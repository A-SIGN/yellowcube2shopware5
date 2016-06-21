{extends file='frontend/checkout/finish.tpl}

{block name='frontend_index_footer' append}
    {action module=widgets controller=AsignWidgetCube action=triggerYellowcube}
{/block}
