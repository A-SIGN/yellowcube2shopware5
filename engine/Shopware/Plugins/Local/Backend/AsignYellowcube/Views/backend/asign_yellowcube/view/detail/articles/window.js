/**
 * This file creates the details window for article
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.detail.articles.Window
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.detail.articles.Window', {
    extend:'Enlight.app.Window',
    alias: 'widget.asignyellowcube-articles-detail-window',
    cls: 'createWindow',
    border: false,
    autoScroll: true,
    autoShow: true,
    layout: 'fit',
    width: 900,
    height: 460,
    stateful: true,
    stateId: 'shopware-asignyellowcube-articles-detail-window',
    footerButton: false,

    /**
     * Contains all snippets for this component
     */
    snippets: {
        title:          '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/title}Yellowcube: YC Params for article - {/s}',
        textSave:       '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/saveall}Save Settings{/s}',
        textCancel:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/cancel}Cancel{/s}',
        textSettings:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/settings}Main Configurations{/s}',
        textMore:       '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/more}Additional Configurations{/s}',

        labelExpiryOptions: {
            labelExpiry: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/expiry}Period Expiry Date Type{/s}',
            labelIgnore: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/expiry/ignore}Ignore{/s}',
            labelWocht:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/expiry/wocht}Wocht{/s}',
            labelMonat:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/expiry/monat}Monat{/s}',
            labelJahr:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/expiry/jahr}Jahr{/s}'
        },

        labelAltOptions: {
            labelAltUnit: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/arltunit}Basismengeneinheit{/s}',
            labelPCE:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/altunit/pce}Stück (Piece){/s}',
            labelPK:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/altunit/pk}Paket (Parcel) evtl. Multipack{/s}',
            labelBG:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/altunit/bg}Bag (Beutel/Tüte){/s}',
            labelCA:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/altunit/ca}Kanister/Dose{/s}',
            labelCT:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/altunit/ct}KAR-Karton 2. Stufe (EH2){/s}',
            labelPF:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/altunit/pf}AL-Palette 3. Stufe (EH3){/s}'
        },

        labelEanOptions: {
            labelEanType: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean}EANType{/s}',
            labelHE:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean/he}Hersteller-EAN{/s}',
            labelHK:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean/hk}Hersteller-Kurz-EAN{/s}',
            labelI6:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean/i6}ITF-Code - 16stellig{/s}',
            labelUC:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean/uc}UPC-Code{/s}',
            labelIC:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean/ic}ITF-Code{/s}',
            labelIE:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean/ie}Instore-EAN (int. Vergabe mögl.){/s}',
            labelIK:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean/ik}Instore-Kurz-EAN (int. Vergabe mögl.){/s}',
            labelVC:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/ean/vc}Velocity-Code (int. Vergabe mögl.){/s}'
        },

        labelBatch: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/batch}Batch Request{/s}',
        labelFlag:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/noflag}Seriennummer-Erfassung{/s}',
        labelIncEsd: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/esd}Include with ESD{/s}',
        descIncEsd: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/esd_desc}Available if this article is ESD and should be allowed to send Yellowcube.{/s}',
        labelLength:'{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/length}Länge{/s}',
        labelHeight:'{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/height}Höhe{/s}',
        labelWidth: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/width}Breite{/s}',
        labelVolume:'{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/volume}Volumen{/s}',
        labelNetto: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/netto}Default Nettogewicht{/s}',
        labelBrutto:'{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/brutto}Default Bruttogewicht{/s}',
        labelUnits: {
            labelCMT: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/unit/cmt}Centimeter{/s}',
            labelMTR: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/unit/mtr}Meter{/s}',
            labelMMT: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/unit/mmt}Millimeter{/s}',
            labelCMQ: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/unit/cmq}Kubik-Centimeter{/s}',
            labelMTQ: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/unit/mtq}Kubik-Meter{/s}',
            labelMMQ: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/unit/mmq}Kubik-Millimeter{/s}',
            labelGRM: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/unit/grm}Gramm{/s}',
            labelKGM: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/unit/kgm}Kilogramm{/s}'
        },

        // international field labels
        labelCustomstariff: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/tariff}Commodity Code{/s}',
        labelTara: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/tara}Tara (KG){/s}',
        labelOrigin: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/articles/form/origin}Country of Origin{/s}',
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
        me.myForm = me.createFormPanel();

        /*{if {acl_is_allowed privilege=update}}*/
        me.dockedItems = [{
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: me.createButtons()
        }];
        /*{/if}*/

        me.items = [me.myForm];
        me.title = me.snippets.title + ' ' + me.record.get('ordernumber');

        me.callParent(arguments);
    },

    createButtons: function(){
        var me = this;
        var buttons = ['->',
            {
                text: me.snippets.textSave,
                action:'saveAdditional',
                cls:'primary'
            },
            {
                text: me.snippets.textCancel,
                action: 'cancel',
                cls: 'secondary',
                scope: me,
                handler: me.destroy
            }
        ];

        return buttons;
    },

    createFormPanel: function () {
        var me = this;
        var artForm = Ext.create('Ext.form.Panel', {
            collapsible: false,
            split: false,
            region: 'center',
            autoScroll: true,
            defaults: {
                labelStyle: 'font-weight: 700; text-align: right;',
                labelWidth: 200,
                anchor: '100%'
            },

            border: 0,
            bodyPadding: 10,
            items: [
                {
                    xtype: 'fieldset',
                    collapsible: true,
                    title: me.snippets.textSettings,
                    items: [
                        {
                            xtype: 'container',
                            layout: 'column',
                            artStore: me.artStore,
                            items: [
                                me.createLeftContainer(), // left side
                                me.createRightContainer() // right side in fieldset
                            ]
                        }
                    ]
                },
                {
                    xtype: 'fieldset',
                    collapsible: true,
                    title: me.snippets.textMore,
                    name: 'additionalInfos',
                    items: [
                        {
                            xtype: 'container',
                            layout: 'column',
                            artStore: me.artStore,
                            items: [
                                me.createLeftInfoContainer(), // left info container
                                me.createRightInfoContainer() // right info container
                            ]
                        }
                    ]
                }
            ]
        });

        if(me.record){
            artForm.loadRecord(me.record);
        }

        return artForm;
    },

    createLeftContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            columnWidth: .5,
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '95%',
                labelWidth: 150,
                minWidth: 250,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                allowBlank: true // for setting the fields madatory
            },
            items: [
                {
                    xtype:'combo',
                    fieldLabel: me.snippets.labelExpiryOptions.labelExpiry,
                    name:'expdatetype',
                    editable: false,
                    value: me.record.get('expdatetype'),
                    queryMode:'local',
                    store:[
                        ['0',me.snippets.labelExpiryOptions.labelIgnore], ['1',me.snippets.labelExpiryOptions.labelWocht],
                        ['2',me.snippets.labelExpiryOptions.labelMonat], ['3', me.snippets.labelExpiryOptions.labelJahr]
                    ]
                },
                {
                    xtype:'combo',
                    fieldLabel: me.snippets.labelAltOptions.labelAltUnit,
                    name:'altunitiso',
                    editable: false,
                    queryMode:'local',
                    value: me.record.get('altunitiso'),
                    valueField: 'altunitiso',
                    store:[
                        ['PCE',me.snippets.labelAltOptions.labelPCE], ['PK',me.snippets.labelAltOptions.labelPK],['BG',me.snippets.labelAltOptions.labelBG],
                        ['CA',me.snippets.labelAltOptions.labelCA], ['CT',me.snippets.labelAltOptions.labelCT], ['PF',me.snippets.labelAltOptions.labelPF]
                    ]
                },
                {
                    xtype:'combo',
                    fieldLabel: me.snippets.labelEanOptions.labelEanType,
                    valueField: 'EANType',
                    name:'eantype',
                    editable: false,
                    store: [
                        ['HE',me.snippets.labelEanOptions.labelHE], ['HK',me.snippets.labelEanOptions.labelHK], ['I6',me.snippets.labelEanOptions.labelI6],
                        ['UC',me.snippets.labelEanOptions.labelUC], ['IC',me.snippets.labelEanOptions.labelIC], ['IE',me.snippets.labelEanOptions.labelIE],
                        ['IK',me.snippets.labelEanOptions.labelIK], ['VC',me.snippets.labelEanOptions.labelVC]
                    ],
                    queryMode: 'local',
                    value: me.record.get('eantype')
                }
            ]
        });
    },

    // first fieldset left + right containers
    createRightContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            columnWidth: .5,
            border: false,
            layout: 'anchor',
            alias: 'widget.containersaveaddon',
            defaults: {
                anchor: '95%',
                labelWidth: 150,
                minWidth: 250,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                allowBlank: false
            },
            items: [
                {
                    xtype: 'checkbox',
                    fieldLabel: me.snippets.labelBatch,
                    name: 'batchreq',
                    checked: me.record.get('batchreq'),
                    inputValue: 1,
                    uncheckedValue: 0
                },
                {
                    xtype: 'checkbox',
                    fieldLabel: me.snippets.labelFlag,
                    name: 'noflag',
                    checked: me.record.get('noflag'),
                    inputValue: 1,
                    uncheckedValue: 0
                },
                {
                    xtype: 'checkbox',
                    fieldLabel: me.snippets.labelIncEsd,
                    supportText : me.snippets.descIncEsd,
                    name: 'incesd',
                    checked: me.record.get('incesd'),
                    inputValue: 1,
                    uncheckedValue: 0,
                    disabled: (me.record.get('esdid') > 0) ? false : true
                },
                {
                    xtype: 'hidden',
                    name: 'id',
                    value: me.record.get('id')
                },
                {
                    xtype: 'hidden',
                    name: 'artid',
                    value: me.record.get('artid')
                }
            ]
        });
    },

    createLeftInfoContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            columnWidth: .5,
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '95%',
                labelWidth: 100,
                minWidth: 250,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                allowBlank: false
            },
            items: [
                {
                    xtype:'combo',
                    fieldLabel: me.snippets.labelLength,
                    name:'length',
                    queryMode:'local',
                    value: me.record.get('length'),
                    store:[
                        ['CMT',me.snippets.labelUnits.labelCMT], ['MTR',me.snippets.labelUnits.labelMTR], ['MMT',me.snippets.labelUnits.labelMMT]
                    ],

                    autoSelect:false,
                    editable: false,
                    forceSelection:false
                },
                {
                    xtype:'combo',
                    fieldLabel: me.snippets.labelWidth,
                    name:'width',
                    queryMode:'local',
                    value: me.record.get('width'),
                    store:[
                        ['CMT',me.snippets.labelUnits.labelCMT], ['MTR',me.snippets.labelUnits.labelMTR], ['MMT',me.snippets.labelUnits.labelMMT]
                    ],

                    autoSelect:false,
                    editable: false,
                    forceSelection:false
                },
                {
                    xtype:'combo',
                    fieldLabel: me.snippets.labelHeight,
                    name:'height',
                    queryMode:'local',
                    value: me.record.get('height'),
                    store:[
                        ['CMT',me.snippets.labelUnits.labelCMT], ['MTR',me.snippets.labelUnits.labelMTR], ['MMT',me.snippets.labelUnits.labelMMT]
                    ],

                    autoSelect:false,
                    editable: false,
                    forceSelection:false
                },
                {
                    xtype:'combo',
                    fieldLabel: me.snippets.labelVolume,
                    name:'volume',
                    value: me.record.get('volume'),
                    queryMode:'local',
                    store:[
                        ['CMQ',me.snippets.labelUnits.labelCMQ], ['MTQ',me.snippets.labelUnits.labelMTQ],
                        ['MMQ',me.snippets.labelUnits.labelMMQ]
                    ],

                    autoSelect:false,
                    editable: false,
                    forceSelection:false
                },
                {
                    xtype:'combo',
                    fieldLabel: me.snippets.labelNetto,
                    name:'netto',
                    queryMode:'local',
                    value: me.record.get('netto'),
                    valueField: 'netto',
                    store:[['GRM',me.snippets.labelUnits.labelGRM],['KGM',me.snippets.labelUnits.labelKGM]],

                    autoSelect:false,
                    editable: false,
                    forceSelection:false
                },
                {
                    xtype: 'combo',
                    fieldLabel: me.snippets.labelBrutto,
                    valueField: 'brutto',
                    name: 'brutto',
                    queryMode: 'local',
                    value: me.record.get('brutto'),
                    store: [['GRM',me.snippets.labelUnits.labelGRM],['KGM',me.snippets.labelUnits.labelKGM]],

                    autoSelect:false,
                    editable: false,
                    forceSelection:false
                }
            ]
        })
    },

    createRightInfoContainer: function () {
        var me = this;
        return Ext.create('Ext.container.Container', {
            columnWidth: .5,
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '95%',
                labelWidth: 100,
                minWidth: 250,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                allowBlank: true // for setting the fields madatory
            },
            items: [
                {
                    xtype:'textfield',
                    fieldLabel: me.snippets.labelCustomstariff,
                    name:'tariff',
                    value: me.record.get('tariff'),
                    valueField: 'tariff'
                },
                {
                    xtype:'textfield',
                    fieldLabel: me.snippets.labelTara,
                    name:'tara',
                    value: me.record.get('tara'),
                    valueField: 'tara'
                },
                {
                    xtype: 'combo',
                    emptyText: me.snippets.textEmpty,
                    fieldLabel: me.snippets.labelOrigin,
                    displayField:'name',
                    valueField:'id',
                    name:'origin',
                    queryMode: 'local',
                    value: me.record.get('origin'),
                    triggerAction: 'all',
                    editable:false,
                    store: Ext.StoreManager.lookup('Country')
                }
            ]
        })
    },

    configure: function() {
        return {
            controller: 'AsignYellowcube'
        };
    }
});
