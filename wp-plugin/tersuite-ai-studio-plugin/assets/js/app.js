jQuery(function($){'use strict';
 window.TSA={
  ajax:function(action,data){data=data||{};data.action='tersuite_'+action;data.nonce=window.TersuiteAI?TersuiteAI.nonce:'';return $.ajax({url:window.TersuiteAI?TersuiteAI.ajaxUrl:ajaxurl,type:'POST',data:data,dataType:'json'});},
  ajaxFile:function(action,data){var fd=new FormData();Object.keys(data||{}).forEach(function(k){fd.append(k,data[k]);});fd.append('action','tersuite_'+action);fd.append('nonce',window.TersuiteAI?TersuiteAI.nonce:'');return $.ajax({url:window.TersuiteAI?TersuiteAI.ajaxUrl:ajaxurl,type:'POST',data:fd,dataType:'json',processData:false,contentType:false});},
  toast:function(msg,type,persistent){var c=$('#tsa-toast');if(!c.length){$('body').append('<div id="tsa-toast" role="status" aria-live="polite"></div>');c=$('#tsa-toast');}var el=$('<div/>',{class:'tsa-toast-item '+(type||'info'),text:msg});c.append(el);if(!persistent){setTimeout(function(){el.fadeOut(250,function(){$(this).remove();});},4500);}return el;},
  showNotice:function(selector,msg,type,persistent){var $el=$(selector);if(!$el.length)return;$el.removeClass('is-loading is-success is-warning is-error').addClass('is-'+(type||'info')).prop('hidden',false).html('<span class="tsa-notice-icon" aria-hidden="true">'+({success:'✓',error:'!',warning:'!',loading:'…',info:'i'}[type]||'i')+'</span><span class="tsa-notice-message">'+this.esc(msg)+'</span>');if(!persistent){setTimeout(function(){$el.prop('hidden',true);},4500);}return $el;},
  esc:function(v){return $('<div/>').text(v==null?'':String(v)).html();},
  unwrap:function(res){return res&&res.success?res.data:null;},
  error:function(xhr,fallback){var r=xhr&&xhr.responseJSON;return r&&r.data&&r.data.message?r.data.message:fallback||'Request failed.';},
  loading:function($el,text){if($el&&$el.length)$el.html('<div class="tsa-loading-state"><span class="tsa-spinner"></span>'+this.esc(text||'Loading…')+'</div>');},
  empty:function($el,text){if($el&&$el.length)$el.html('<div class="tsa-empty-state">'+this.esc(text||'Nothing to show yet.')+'</div>');},
  collection:function(payload){
    if(Array.isArray(payload)) return payload;
    if(!payload||typeof payload!=='object') return [];
    var c=[payload.results,payload.projects,payload.items,payload.data];
    for(var i=0;i<c.length;i++){
      if(Array.isArray(c[i])) return c[i];
      if(c[i]&&typeof c[i]==='object'){
        if(Array.isArray(c[i].results)) return c[i].results;
        if(Array.isArray(c[i].projects)) return c[i].projects;
        if(Array.isArray(c[i].items)) return c[i].items;
      }
    }
    return [];
  }
 };
 $('.tsa-mobile-menu').off('click').on('click',function(){$('.tsa-sidebar').toggleClass('open');$('body').toggleClass('tsa-sidebar-open');});
 $(document).on('click','.tsa-sidebar a',function(){if(window.matchMedia('(max-width: 900px)').matches){$('.tsa-sidebar').removeClass('open');$('body').removeClass('tsa-sidebar-open');}});
 $(document).on('keydown',function(e){if(e.key==='Escape'){ $('.tsa-project-dropdown').prop('hidden',true); $('.tsa-modal-overlay').removeClass('is-open').prop('hidden',true).hide(); $('.tsa-sidebar').removeClass('open'); $('body').removeClass('tsa-sidebar-open'); }});
 $(document).on('click',function(e){if(!$(e.target).closest('#tsa-project-dropdown,#tsa-project-select').length)$('#tsa-project-dropdown').prop('hidden',true);});
 $('#tsa-project-select').on('click',function(e){e.preventDefault();var $d=$('#tsa-project-dropdown');$d.prop('hidden',!$d.prop('hidden'));if(!$d.prop('hidden'))loadProjectPicker();});
 $('#tsa-close-project-dropdown').on('click',function(){$('#tsa-project-dropdown').prop('hidden',true);});
 $('#tsa-header-notifications').on('click',function(){location.href=TersuiteAI.notificationsUrl;});
 $('#tsa-user-menu').on('click',function(){location.href=TersuiteAI.settingsUrl;});
 $('#tsa-global-generate').on('click',function(){if(TersuiteAI.projectId)location.href=TersuiteAI.studioUrl+'&project_id='+encodeURIComponent(TersuiteAI.projectId);else location.href=TersuiteAI.projectsUrl;});
 function loadProjectPicker(){var $l=$('#tsa-project-dropdown-list');TSA.loading($l,'Loading projects…');TSAAPI.projects().done(function(r){var d=TSA.unwrap(r),items=TSA.collection(d);if(!items.length){TSA.empty($l,'No projects yet.');return;}var html='';items.forEach(function(p){var id=p.id||p.project_id;var name=TSA.esc(p.name||'Untitled project');var active=String(id)===String(TersuiteAI.projectId);html+='<button type="button" class="tsa-project-option '+(active?'active':'')+'" data-id="'+TSA.esc(id)+'"><span>'+name+'</span><small>'+TSA.esc(p.status||'Ready')+'</small></button>';});$l.html(html);});}
 $(document).on('click','.tsa-project-option',function(){var id=$(this).data('id');if(!id)return;var url=TersuiteAI.studioUrl+'&project_id='+encodeURIComponent(id);location.href=url;});
 function loadShell(){
  TSAAPI.account().done(function(r){var d=TSA.unwrap(r)||{};var user=d.user||d.account||d;var name=user.name||user.username||user.email||'Account';$('#tsa-user-name').text(name);$('#tsa-user-avatar').text(String(name).charAt(0).toUpperCase());$('#tsa-user-plan').text((user.plan||d.plan||'Connected')+'');}).fail(function(){ $('#tsa-user-plan').text('Backend not connected'); });
  TSAAPI.subscription().done(function(r){var d=TSA.unwrap(r)||{};var s=d.status||{},plans=d.plans||[];var plan=s.plan||s.subscription||{};var planName=plan.name||s.plan_name||'Plan';$('#tsa-sidebar-plan-name').text(String(planName).toUpperCase());var credits=s.credits_remaining!=null?s.credits_remaining:(d.credits&&d.credits.remaining);var limit=s.credits_limit||(d.credits&&d.credits.limit);if(credits!=null)$('#tsa-sidebar-credits').text(credits);if(limit!=null)$('#tsa-sidebar-credits-limit').text('/ '+limit);if(credits!=null&&limit)$('#tsa-sidebar-credit-progress').css('width',Math.min(100,(credits/limit)*100)+'%');});
  TSAAPI.notifications().done(function(r){var d=TSA.unwrap(r)||{},items=Array.isArray(d)?d:(d.results||[]),unread=d.unread_count!=null?Number(d.unread_count):items.filter(function(n){return !(n.read||n.is_read);}).length;$('#tsa-header-notification-count,#tsa-sidebar-notification-count').text(unread);});
 }
 function applyTheme(theme){theme=theme==='light'?'light':'dark';document.documentElement.setAttribute('data-tsa-theme',theme);document.body.classList.toggle('tsa-theme-light',theme==='light');$('#tsa-theme-toggle').attr('aria-pressed',theme==='light').attr('title',theme==='light'?'Switch to dark mode':'Switch to light mode').text(theme==='light'?'☾':'☀');try{localStorage.setItem('tersuite_ai_theme',theme);}catch(e){}}
 var savedTheme='dark';try{savedTheme=localStorage.getItem('tersuite_ai_theme')||'dark';}catch(e){} applyTheme(savedTheme);
 $(document).on('click','#tsa-theme-toggle',function(){applyTheme(document.documentElement.getAttribute('data-tsa-theme')==='light'?'dark':'light');});
 loadShell();
});
