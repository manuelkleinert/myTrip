pimcore.registerNS("pimcore.plugin.MyTripBundle");

pimcore.plugin.MyTripBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.MyTripBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("MyTripBundle ready!");
    }
});

var MyTripBundlePlugin = new pimcore.plugin.MyTripBundle();
