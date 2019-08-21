humhub.module('onlydocuments', function (module, require, $) {

    var client = require('client');
    var modal = require('ui.modal');
    var object = require('util').object;
    var Widget = require('ui.widget').Widget;
    var event = require('event');
    var loader = require('ui.loader');
    var ooJSLoadRetries = 0;

    var Editor = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Editor, Widget);

    Editor.prototype.getDefaultOptions = function () {
        return {
            'fileName': 'unnamedFile.docx',
        };
    };

    Editor.prototype.init = function () {
        
        if (this.options.moduleConfigured != 1) {
            module.log.error('No OnlyOffice server configured! - Check OnlyDocuments module configuration!', true);
            return
        }
        
        this.initEditor();
        
        this.modal = modal.get('#onlydocuments-modal');
        
        var that = this;
        this.modal.$.on('hidden.bs.modal', function(evt) {
            that.modal.clear();
        });

    };


    Editor.prototype.share = function (evt) {
        m = modal.get('#onlydocuments-share-modal');
        m.load(evt.url);
        m.show();
    }


    Editor.prototype.close = function (evt) {

        if (this.options.editMode == 'edit') {
            refreshFileInfo(this, evt);
        } else {
            this.modal.clear();
            this.modal.close();
            evt.finish();
        }
        
        
    }

    Editor.prototype.initEditor = function () {
        if(!window.DocsAPI) {
            ooJSLoadRetries++;
            if(ooJSLoadRetries < 100) {
                setTimeout($.proxy(this.initEditor, this), 100);
                return;
            } else {
                module.log.error('Could not onlyoffice document editor.', true);
                return;
            }
        }
        
        var config = this.options.config;
        config.width = "100%";
        config.height = "100%";
        config.events = {
            //'onReady': onReady,
            //'onDocumentStateChange': onDocumentStateChange,
            //'onRequestEditRights': onRequestEditRights,
            //'onError': onError,
        };

        this.docEditor = new DocsAPI.DocEditor('iframeContainer', config);
    }

    var Convert = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Convert, Widget);

    Convert.prototype.init = function () {

        var that = this;
        var msg = that.$.find('#oConvertMessage');

        function _onError(error) {
            msg.text(that.options.errorMessage + ' ' + error);
            loader.reset(that.$.find('.modal-footer'));
        }

        function _callAjax() {
            jQuery.ajax({
                type : "POST",
                url : that.options.convertPost,
                cache: false,
                success: function(data) {
                    if (data.error) {
                        _onError(data.error);
                        return;
                    }

                    if (data.percent != null) {
                        msg.text(data.percent + "%");
                    }

                    if (!data.endConvert) {
                        setTimeout(_callAjax, 1000);
                    } else {
                        msg.text(that.options.doneMessage);
                        loader.reset(that.$.find('.modal-footer'));
                    }
                },
                error: _onError
            });
        }

        loader.set(that.$.find('.modal-footer'));
		_callAjax();
    };

    Convert.prototype.getDefaultOptions = function () {
        return {};
    };

    Convert.prototype.close = function (evt) {
        refreshFileInfo(this, evt);
    };

    function refreshFileInfo(that, evt) {
        client.post({url: that.options.fileInfoUrl}).then(function (response) {
            event.trigger('humhub:file:modified', [response.file]);
            that.modal.clear();
            that.modal.close();
            evt.finish();
        }).catch(function (e) {
            module.log.error(e);
            that.modal.clear();
            that.modal.close();
            evt.finish();
        });
    }


    var Share = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Share, Widget);

    Share.prototype.init = function () {

        var that = this;

        if ($('.editLinkInput').find('input').val() != '') {
            $('.editLinkCheckbox').prop('checked', true);
            $(".editLinkCheckbox").attr('checked', true);
        } else {
            $('.editLinkInput').hide();
        }

        if ($('.viewLinkInput').find('input').val() != '') {
            $('.viewLinkCheckbox').attr('checked', true);
        } else {
            $('.viewLinkCheckbox').attr('checked', false);
            $('.viewLinkInput').hide();
        }

        $('.viewLinkCheckbox').change(function () {
            if ($('.viewLinkCheckbox:checked').length) {
                loader.set(that.$.find('.modal-footer'));
                $.ajax({
                    url: that.options.shareGetLink,
                    cache: false,
                    type: 'POST',
                    data: {'shareMode': 'view'},
                    dataType: 'json',
                    success: function (json) {
                        $('.viewLinkInput').show();
                        $('.viewLinkInput').find('input').val(json.url)
                        loader.reset(that.$.find('.modal-footer'));
                    }
                });
            } else {
                loader.set(that.$.find('.modal-footer'));
                $.ajax({
                    url: that.options.shareRemoveLink,
                    cache: false,
                    type: 'POST',
                    data: {'shareMode': 'view'},
                    dataType: 'json',
                    success: function (jsoin) {
                        $('.viewLinkInput').hide();
                        loader.reset(that.$.find('.modal-footer'));
                    }
                });
            }
        });

        $('.editLinkCheckbox').change(function () {
            if ($('.editLinkCheckbox:checked').length) {
                loader.set(that.$.find('.modal-footer'));
                $.ajax({
                    url: that.options.shareGetLink,
                    cache: false,
                    type: 'POST',
                    data: {'shareMode': 'edit'},
                    dataType: 'json',
                    success: function (json) {
                        $('.editLinkInput').show();
                        $('.editLinkInput').find('input').val(json.url)
                        loader.reset(that.$.find('.modal-footer'));
                    }
                });
            } else {
                loader.set(that.$.find('.modal-footer'));
                $.ajax({
                    url: that.options.shareRemoveLink,
                    cache: false,
                    type: 'POST',
                    data: {'shareMode': 'edit'},
                    dataType: 'json',
                    success: function (jsoin) {
                        $('.editLinkInput').hide();
                        loader.reset(that.$.find('.modal-footer'));
                    }
                });
            }
        });

    };

    Share.prototype.getDefaultOptions = function () {
        return {};
    };

    Share.prototype.clickv = function (evt) {
        var that = this;
        //evt.$trigger
    }



    var init = function (pjax) {};

    var createSubmit = function (evt) {
        client.submit(evt).then(function (response) {
            event.trigger('humhub:file:created', [response.file]);

            m = modal.get('#onlydocuments-modal');
            if (response.openFlag) {
                m.load(response.openUrl);
                m.show();
            } else {
                m.close();
            }

        }).catch(function (e) {
            module.log.error(e, true);
        });
    };

    module.export({
        init: init,
        createSubmit: createSubmit,
        Editor: Editor,
        Convert: Convert,
        Share: Share,
    });

});