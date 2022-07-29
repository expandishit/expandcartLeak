/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

    config.removeButtons = '';

    config.removePlugins = 'pastefromword,image,pastetext,undo,newpage,preview,save,language,find,scayt,selectall,wsc,about,forms,div,iframe,pagebreak,specialchar,smiley,flash,stylescombo';
    config.extraPlugins = 'justify,widget,oembed,lineutils,image2,autogrow';
    config.toolbar = [
        { name: 'basicstyles', items : ['Bold','Italic','Underline'] },
        { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','JustifyGroup','Outdent','Indent','-','Blockquote','JustifyBlock' ] },
        { name: 'links',       items : [ 'Link','Unlink' ] },
        { name: 'insert',      items : [ 'Image','Table','oembed' ] },
        { name: 'format',      items : [ 'Format' ] },
        { name: 'colors',      items : [ 'TextColor','BGColor' ] },
        { name: 'document',    items : [ 'Source' ] },
    ];
    config.language = langCode;
    config.baseHref = '';

    config.autoGrow_minHeight = 200;
    config.autoGrow_maxHeight = 500;

    config.filebrowserWindowWidth = '50%';

    config.filebrowserWindowHeight = '50%';

    config.allowedContent = true;

    CKEDITOR.dtd.$removeEmpty['ins'] = false;
};