/**
 * This file creates the details window for errorlogs
 * 
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.detail.errorlogs.More
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.detail.errorlogs.More', {
    extend:'Enlight.app.Window',
    alias: 'widget.asignyellowcube-more-window',
    cls: 'createWindow',
    border:false,
    autoShow:true,
    autoScroll: true,
    width: 800,
    height: 450,
    bodyPadding: 10,
    stateful:true,
    stateId:'shopware-asignyellowcube-more-window',
    footerButton:false,

    /**
     * Contains all snippets for this component
     */
    snippets: {
        title:          '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/errorlogs/more}Error Stacktrace for debugging.{/s}',
        textNoDevLogs:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/errorlogs/nologs}No developer logs available for this entry.{/s}'
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;

        //add the order list grid panel and set the store
        me.items = [ me.getMoreInfo() ];
        me.title = me.snippets.title;

        me.callParent(arguments);
    },

    /**
     * Creates the tab panel for the detail page.
     * @return Ext.tab.Panel
     */
    getMoreInfo: function() {
        var me = this;
        
        return Ext.create('Ext.panel.Panel', {
            //title: 'More information',
            bodyPadding: 10,
            flex: 1,
            paddingRight: 5,
            items: [
                {
                    xtype: 'container',
                    renderTpl: me.createResponseTemplate()
                }
            ]
        });
    }, 

    /**
     * Creates the XTemplate for the developer logs
     *
     * @return [Ext.XTemplate] generated Ext.XTemplate
     */
    createResponseTemplate:function () {
        var me = this, devlogtext;
        
        if (me.record.get('devlog') != "") {
            devlogtext = me.record.get('devlog');
        } else {
            devlogtext = me.snippets.textNoDevLogs;
        }
        
        return new Ext.XTemplate(
            '<tpl for=".">',
                devlogtext,                    
            '</tpl>'
        );
        
    },   

    configure: function() {
        return {
            controller: 'AsignYellowcube'
        };
    }
});
