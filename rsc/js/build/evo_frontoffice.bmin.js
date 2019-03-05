/* This includes 9 files: src/evo_modal_window.js, src/evo_images.js, src/evo_user_crop.js, src/evo_user_report.js, src/evo_user_contact_groups.js, src/evo_rest_api.js, src/evo_item_flag.js, src/evo_links.js, ajax.js */
function openModalWindow(a,b,c,d,e,f){var g="overlay_page_active";void 0!==d&&1==d&&(g="overlay_page_active_transparent"),void 0===b&&(b="560px");var h="";if(void 0!==c&&(c>0||""!=c)&&(h=' style="height:'+c+'"'),jQuery("#overlay_page").length>0)return void jQuery("#overlay_page").html(a);jQuery("body").append('<div id="screen_mask"></div><div id="overlay_wrap" style="width:'+b+'"><div id="overlay_layout"><div id="overlay_page"'+h+"></div></div></div>"),jQuery("#screen_mask").fadeTo(1,.5).fadeIn(200),jQuery("#overlay_page").html(a).addClass(g),jQuery(document).on("click","#close_button, #screen_mask, #overlay_page",function(a){if("overlay_page"==jQuery(this).attr("id")){var b=jQuery("#overlay_page form");if(b.length){var c=b.position().top+jQuery("#overlay_wrap").position().top,d=c+b.height();a.clientY>c&&a.clientY<d||closeModalWindow()}return!0}return closeModalWindow(),!1})}function closeModalWindow(a){return void 0===a&&(a=window.document),jQuery("#overlay_page",a).hide(),jQuery(".action_messages",a).remove(),jQuery("#server_messages",a).insertBefore(".first_payload_block"),jQuery("#overlay_wrap",a).remove(),jQuery("#screen_mask",a).remove(),!1}function user_crop_avatar(a,b,c){void 0===c&&(c="avatar");var d=750,e=320,f=jQuery(window).width(),g=jQuery(window).height(),h=f,i=g,j=i/h;i=i>d?d:i<e?e:i,h=h>d?d:h<e?e:h;var k=10,l=10;k=h-2*k>e?10:0,l=i-2*l>e?10:0;var m=h>d?d:h,n=i>d?d:i;openModalWindow('<span id="spinner" class="loader_img loader_user_report absolute_center" title="'+evo_js_lang_loading+'"></span>',m+"px",n+"px",!0,evo_js_lang_crop_profile_pic,[evo_js_lang_crop,"btn-primary"],!0);var o=jQuery("div.modal-dialog div.modal-body").length?jQuery("div.modal-dialog div.modal-body"):jQuery("#overlay_page"),p={top:parseInt(o.css("paddingTop")),right:parseInt(o.css("paddingRight")),bottom:parseInt(o.css("paddingBottom")),left:parseInt(o.css("paddingLeft"))},q=(jQuery("div.modal-dialog div.modal-body").length?parseInt(o.css("min-height")):n-100)-(p.top+p.bottom),r=m-(p.left+p.right),s={user_ID:a,file_ID:b,aspect_ratio:j,content_width:r,content_height:q,display_mode:"js",crumb_user:evo_js_crumb_user};return evo_js_is_backoffice?(s.ctrl="user",s.user_tab="crop",s.user_tab_from=c):(s.blog=evo_js_blog,s.disp="avatar",s.action="crop"),jQuery.ajax({type:"POST",url:evo_js_user_crop_ajax_url,data:s,success:function(a){openModalWindow(a,m+"px",n+"px",!0,evo_js_lang_crop_profile_pic,[evo_js_lang_crop,"btn-primary"])}}),!1}function user_report(a,b){openModalWindow('<span class="loader_img loader_user_report absolute_center" title="'+evo_js_lang_loading+'"></span>',"auto","",!0,evo_js_lang_report_user,[evo_js_lang_report_this_user_now,"btn-danger"],!0);var c={action:"get_user_report_form",user_ID:a,crumb_user:evo_js_crumb_user};return evo_js_is_backoffice?(c.is_backoffice=1,c.user_tab=b):c.blog=evo_js_blog,jQuery.ajax({type:"POST",url:evo_js_user_report_ajax_url,data:c,success:function(a){openModalWindow(a,"auto","",!0,evo_js_lang_report_user,[evo_js_lang_report_this_user_now,"btn-danger"])}}),!1}function user_contact_groups(a){return openModalWindow('<span class="loader_img loader_user_report absolute_center" title="'+evo_js_lang_loading+'"></span>',"auto","",!0,evo_js_lang_contact_groups,evo_js_lang_save,!0),jQuery.ajax({type:"POST",url:evo_js_user_contact_groups_ajax_url,data:{action:"get_user_contact_form",blog:evo_js_blog,user_ID:a,crumb_user:evo_js_crumb_user},success:function(a){openModalWindow(a,"auto","",!0,evo_js_lang_contact_groups,evo_js_lang_save)}}),!1}function evo_rest_api_request(url,params_func,func_method,method){var params=params_func,func=func_method;"function"==typeof params_func&&(func=params_func,params={},method=func_method),void 0===method&&(method="GET"),jQuery.ajax({contentType:"application/json; charset=utf-8",type:method,url:restapi_url+url,data:params}).then(function(data,textStatus,jqXHR){"object"==typeof jqXHR.responseJSON&&eval(func)(data,textStatus,jqXHR)})}function evo_rest_api_print_error(a,b,c){if("string"!=typeof b&&void 0===b.code&&(b=void 0===b.responseJSON?b.statusText:b.responseJSON),void 0===b.code)var d='<h4 class="text-danger">Unknown error: '+b+"</h4>";else{var d='<h4 class="text-danger">'+b.message+"</h4>";c&&(d+="<div><b>Code:</b> "+b.code+"</div><div><b>Status:</b> "+b.data.status+"</div>")}evo_rest_api_end_loading(a,d)}function evo_rest_api_start_loading(a){jQuery(a).addClass("evo_rest_api_loading").append('<div class="evo_rest_api_loader">loading...</div>')}function evo_rest_api_end_loading(a,b){jQuery(a).removeClass("evo_rest_api_loading").html(b).find(".evo_rest_api_loader").remove()}function evo_link_initialize_fieldset(a){if(jQuery("#"+a+"attachments_fieldset_table").length>0){var b=jQuery("#"+a+"attachments_fieldset_table").height();b=b>320?320:b<97?97:b,jQuery("#"+a+"attachments_fieldset_wrapper").height(b),jQuery("#"+a+"attachments_fieldset_wrapper").resizable({minHeight:80,handles:"s",resize:function(b,c){jQuery("#"+a+"attachments_fieldset_wrapper").resizable("option","maxHeight",jQuery("#"+a+"attachments_fieldset_table").height())}}),jQuery(document).on("click","#"+a+"attachments_fieldset_wrapper .ui-resizable-handle",function(){var b=jQuery("#"+a+"attachments_fieldset_table").height(),c=jQuery("#"+a+"attachments_fieldset_wrapper").height()+80;jQuery("#"+a+"attachments_fieldset_wrapper").css("height",c>b?b:c)})}}function evo_link_fix_wrapper_height(a){var b=void 0===a?"":a,c=jQuery("#"+b+"attachments_fieldset_table").height();jQuery("#"+b+"attachments_fieldset_wrapper").height()!=c&&jQuery("#"+b+"attachments_fieldset_wrapper").height(jQuery("#"+b+"attachments_fieldset_table").height())}function evo_link_change_position(a,b,c){var d=a,e=a.value,f=a.id.substr(17);return jQuery.get(b+"anon_async.php?action=set_object_link_position&link_ID="+f+"&link_position="+e+"&crumb_link="+c,{},function(b,c){b=ajax_debug_clear(b),"OK"==b?(evoFadeSuccess(jQuery(d).closest("tr")),jQuery(d).closest("td").removeClass("error"),"cover"==e&&jQuery("select[name=link_position][id!="+a.id+"] option[value=cover]:selected").each(function(){jQuery(this).parent().val("aftermore"),evoFadeSuccess(jQuery(this).closest("tr"))})):(jQuery(d).val(b),evoFadeFailure(jQuery(d).closest("tr")),jQuery(d.form).closest("td").addClass("error"))}),!1}function evo_link_insert_inline(a,b,c,d,e){if(void 0==d&&(d=0),"undefined"!=typeof b2evoCanvas){var f="["+a+":"+b;c.length&&(f+=":"+c),f+="]",void 0!==e&&!1!==e&&(f+=e+"[/"+a+"]");var g=jQuery("#display_position_"+b);0!=g.length&&"inline"!=g.val()?(deferInlineReminder=!0,evo_rest_api_request("links/"+b+"/position/inline",function(a){g.val("inline"),evoFadeSuccess(g.closest("tr")),g.closest("td").removeClass("error"),textarea_wrap_selection(b2evoCanvas,f,"",d,window.document)},"POST"),deferInlineReminder=!1):textarea_wrap_selection(b2evoCanvas,f,"",d,window.document)}}function evo_link_delete(a,b,c,d){return evo_rest_api_request("links/"+c,{action:d},function(d){if("item"==b||"comment"==b||"emailcampaign"==b||"message"==b){var e=window.b2evoCanvas;if(null!=e){var f=new RegExp("\\[(image|file|inline|video|audio|thumbnail):"+c+":?[^\\]]*\\]","ig");textarea_str_replace(e,f,"",window.document)}}jQuery(a).closest("tr").remove(),evo_link_fix_wrapper_height()},"DELETE"),!1}function evo_link_change_order(a,b,c){return evo_rest_api_request("links/"+b+"/"+c,function(b){var d=jQuery(a).closest("tr"),e=d.find("span[data-order]");if("move_up"==c){var f=e.attr("data-order"),g=jQuery(d.prev()),h=g.find("span[data-order]"),i=h.attr("data-order");d.prev().before(d),e.attr("data-order",i),h.attr("data-order",f)}else{var f=e.attr("data-order"),j=jQuery(d.next()),k=j.find("span[data-order]"),l=k.attr("data-order");d.next().after(d),e.attr("data-order",l),k.attr("data-order",f)}evoFadeSuccess(d)},"POST"),!1}function evo_link_attach(a,b,c,d){return evo_rest_api_request("links",{action:"attach",type:a,object_ID:b,root:c,path:d},function(a){var b=jQuery("#attachments_fieldset_table .results table",window.parent.document),c=(b.parent,jQuery(a.list_content));b.replaceWith(jQuery("table",c)).promise().done(function(a){setTimeout(function(){window.parent.evo_link_fix_wrapper_height()},10)})}),!1}function evo_link_ajax_loading_overlay(){var a=jQuery("#attachments_fieldset_table"),b=!1;return 0==a.find(".results_ajax_loading").length&&(b=jQuery('<div class="results_ajax_loading"><div>&nbsp;</div></div>'),a.css("position","relative"),b.css({width:a.width(),height:a.height()}),a.append(b)),b}function evo_link_refresh_list(a,b,c){var d=evo_link_ajax_loading_overlay();return d&&evo_rest_api_request("links",{action:void 0===c?"refresh":"sort",type:a.toLowerCase(),object_ID:b},function(a){jQuery("#attachments_fieldset_table").html(a.html),d.remove(),evo_link_fix_wrapper_height()}),!1}function evo_link_sort_list(a){var b=jQuery("#"+a+"attachments_fieldset_table tbody.filelist_tbody tr");b.sort(function(a,c){var d=parseInt(jQuery("span[data-order]",a).attr("data-order")),e=parseInt(jQuery("span[data-order]",c).attr("data-order"));return d||(d=b.length),e||(e=b.length),d<e?-1:e<d?1:0});var c;$.each(b,function(b,d){0===b?(jQuery(d).prependTo("#"+a+"attachments_fieldset_table tbody.filelist_tbody"),c=d):(jQuery(d).insertAfter(c),c=d)})}function ajax_debug_clear(a){var b=/<!-- Ajax response end -->/;return a=a.replace(b,""),a=a.replace(/(<div class="jslog">[\s\S]*)/i,""),jQuery.trim(a)}function ajax_response_is_correct(a){var b=/<!-- Ajax response end -->/;return!!a.match(b)&&""!=(a=ajax_debug_clear(a))}jQuery(document).keyup(function(a){27==a.keyCode&&closeModalWindow()}),jQuery(document).ready(function(){jQuery("img.loadimg").each(function(){jQuery(this).prop("complete")?(jQuery(this).removeClass("loadimg"),""==jQuery(this).attr("class")&&jQuery(this).removeAttr("class")):jQuery(this).on("load",function(){jQuery(this).removeClass("loadimg"),""==jQuery(this).attr("class")&&jQuery(this).removeAttr("class")})})}),jQuery(document).on("click","a.evo_post_flag_btn",function(){var a=jQuery(this),b=parseInt(a.data("id"));return b>0&&(a.data("status","inprogress"),jQuery("span",jQuery(this)).addClass("fa-x--hover"),evo_rest_api_request("collections/"+a.data("coll")+"/items/"+b+"/flag",function(b){b.flag?(a.find("span:first").show(),a.find("span:last").hide()):(a.find("span:last").show(),a.find("span:first").hide()),jQuery("span",a).removeClass("fa-x--hover"),setTimeout(function(){a.removeData("status")},500)},"PUT")),!1}),jQuery(document).on("mouseover","a.evo_post_flag_btn",function(){"inprogress"!=jQuery(this).data("status")&&jQuery("span",jQuery(this)).addClass("fa-x--hover")});