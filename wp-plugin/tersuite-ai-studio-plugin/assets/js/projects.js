jQuery(function($){'use strict';
 var items=[],view='grid';
 function render(){var $list=$('#tsa-projects-list');var q=($('#tsa-project-search').val()||'').toLowerCase();var status=$('#tsa-project-status').val();var filtered=items.filter(function(p){return (!q||((p.name||'')+' '+(p.description||'')).toLowerCase().indexOf(q)>=0)&&(!status||String(p.status||'').toLowerCase()===status);});if(!filtered.length){TSA.empty($list,q||status?'No projects match your filters.':'No projects yet. Create your first project to get started.');return;}var html='';filtered.forEach(function(p){var id=p.id||p.project_id;var pct=p.progress!=null?Math.max(0,Math.min(100,Number(p.progress))):0;var state=String(p.status||'ready').toLowerCase();var chip=state==='completed'?'success':(state==='failed'?'danger':(state==='archived'?'neutral':'live'));html+='<article class="tsa-project-big tsa-project-'+view+'" data-project-id="'+TSA.esc(id)+'"><div class="tsa-big-top"><span class="tsa-folder-icon large">◆</span><span class="tsa-status-chip '+chip+'">'+TSA.esc(p.status||'Ready')+'</span></div><h3>'+TSA.esc(p.name||'Untitled project')+'</h3><p>'+TSA.esc(p.description||'No description provided.')+'</p><div class="tsa-project-meta"><span>'+TSA.esc(p.version||'—')+'</span><span>'+TSA.esc(p.file_count!=null?p.file_count:'—')+' files</span><span>'+TSA.esc(pct)+'%</span></div><div class="tsa-progress"><span style="width:'+pct+'%"></span></div><div class="tsa-card-actions"><a class="tsa-secondary" href="'+TSA.esc(TersuiteAI.studioUrl+'&project_id='+encodeURIComponent(id))+'">Open Studio</a><div class="tsa-project-menu-wrap"><button class="tsa-ghost tsa-project-menu" data-id="'+TSA.esc(id)+'" type="button" aria-expanded="false" aria-haspopup="menu">•••</button><div class="tsa-project-menu-panel" data-menu-for="'+TSA.esc(id)+'" hidden><button type="button" class="tsa-project-open" data-id="'+TSA.esc(id)+'">Open Studio</button><button type="button" class="tsa-project-delete danger" data-id="'+TSA.esc(id)+'">Delete Project</button></div></div></div></article>';});$list.toggleClass('tsa-list-view',view==='list').html(html);}
 function normalizeProjects(payload){
   if(Array.isArray(payload)) return payload;
   if(!payload || typeof payload!=='object') return [];
   var candidates=[payload.results,payload.projects,payload.items,payload.data];
   for(var i=0;i<candidates.length;i++){
     if(Array.isArray(candidates[i])) return candidates[i];
     if(candidates[i] && typeof candidates[i]==='object'){
       if(Array.isArray(candidates[i].results)) return candidates[i].results;
       if(Array.isArray(candidates[i].projects)) return candidates[i].projects;
       if(Array.isArray(candidates[i].items)) return candidates[i].items;
     }
   }
   return [];
 }
 function load(){
   var $l=$('#tsa-projects-list');
   TSA.loading($l,'Loading projects…');
   return TSAAPI.projects().done(function(r){
     var d=TSA.unwrap(r);
     items=(TSA.collection?TSA.collection(d):normalizeProjects(d));
     render();
   }).fail(function(x){
     $l.html('<div class="tsa-error-state"><strong>Unable to load projects</strong><p>'+TSA.esc(TSA.error(x,'Unable to load projects.'))+'</p><button class="tsa-secondary tsa-retry-projects" type="button">Retry</button></div>');
   });
 }
 $('#tsa-create-project').on('click',function(){$('#tsa-project-modal').prop('hidden',false).addClass('is-open').show();setTimeout(function(){$('#tsa-proj-name').trigger('focus');},50);});
 $('#tsa-project-modal-close,#tsa-project-cancel').on('click',function(){$('#tsa-project-modal').prop('hidden',true).removeClass('is-open').hide();});
 $('#tsa-create-project-form').on('submit',function(e){
   e.preventDefault();
   var n=$('#tsa-proj-name').val().trim(),d=$('#tsa-proj-desc').val().trim();
   if(!n){TSA.showNotice('#tsa-project-form-status','Project name is required.','error',true);return;}
   var $form=$(this),$b=$form.find('[type=submit]').prop('disabled',true).text('Creating…');
   TSA.showNotice('#tsa-project-form-status','Creating your project…','loading',false);
   TSAAPI.createProject(n,d).done(function(r){
     var payload=TSA.unwrap(r)||{};
     var id=payload.id||payload.project_id||(payload.project&&payload.project.id)||(payload.data&&payload.data.id);
     if(id){
       TSA.showNotice('#tsa-project-form-status','✓ Project created successfully. Opening Studio…','success',true);
       setTimeout(function(){location.href=TersuiteAI.studioUrl+'&project_id='+encodeURIComponent(id);},350);
       return;
     }

     // Backend acknowledged creation but did not return an ID. Refresh the
     // authoritative collection automatically and resolve the project by name.
     TSA.showNotice('#tsa-project-form-status','Project created. Syncing it with your workspace…','warning',true);
     load().done(function(){
       var matches=items.filter(function(p){return String(p.name||'').trim().toLowerCase()===n.toLowerCase();});
       if(matches.length){
         var match=matches[matches.length-1],matchId=match.id||match.project_id;
         if(matchId){
           TSA.showNotice('#tsa-project-form-status','✓ Project found. Opening Studio…','success',true);
           setTimeout(function(){location.href=TersuiteAI.studioUrl+'&project_id='+encodeURIComponent(matchId);},350);
           return;
         }
       }
       TSA.showNotice('#tsa-project-form-status','Project was created, but its ID is not available yet. Projects have been refreshed. Click “Refresh Projects” below to try again.','error',true);
       if(!$('#tsa-project-refresh-recovery').length){
         $('<button type="button" id="tsa-project-refresh-recovery" class="tsa-secondary tsa-project-refresh-recovery">Refresh Projects</button>').insertAfter('#tsa-project-form-status');
       }
     }).fail(function(){
       TSA.showNotice('#tsa-project-form-status','Project was created, but we could not refresh the project list. Please refresh Projects and try again.','error',true);
     });
   }).fail(function(x){
     TSA.showNotice('#tsa-project-form-status',TSA.error(x,'Project creation failed. Please try again.'),'error',true);
   }).always(function(){$b.prop('disabled',false).text('Create & Open Studio');});
 });
 $(document).on('click','.tsa-project-refresh-recovery',function(){
   var $b=$(this).prop('disabled',true).text('Refreshing…');
   load().done(function(){
     var n=$('#tsa-proj-name').val().trim();
     var matches=items.filter(function(p){return String(p.name||'').trim().toLowerCase()===n.toLowerCase();});
     if(matches.length){
       var id=matches[matches.length-1].id||matches[matches.length-1].project_id;
       if(id) location.href=TersuiteAI.studioUrl+'&project_id='+encodeURIComponent(id);
       else TSA.showNotice('#tsa-project-form-status','The project is visible, but its ID is still unavailable.','error',true);
     }else{
       TSA.showNotice('#tsa-project-form-status','The project list refreshed, but the new project is not visible yet. Try Refresh Projects again in a moment.','error',true);
     }
   }).always(function(){$b.prop('disabled',false).text('Refresh Projects');});
 });
 $('#tsa-project-search,#tsa-project-status').on('input change',render);
 $('#tsa-grid-view').on('click',function(){view='grid';$('#tsa-grid-view,#tsa-list-view').removeClass('is-active');$(this).addClass('is-active');render();});
 $('#tsa-list-view').on('click',function(){view='list';$('#tsa-grid-view,#tsa-list-view').removeClass('is-active');$(this).addClass('is-active');render();});
 $(document).on('click','.tsa-project-menu',function(e){e.stopPropagation();var id=String($(this).data('id'));$('.tsa-project-menu-panel').not('[data-menu-for="'+CSS.escape(id)+'"]').prop('hidden',true);var $p=$(this).siblings('.tsa-project-menu-panel');$p.prop('hidden',!$p.prop('hidden'));$(this).attr('aria-expanded',String(!$p.prop('hidden')));});
 $(document).on('click','.tsa-project-open',function(){var id=$(this).data('id');location.href=TersuiteAI.studioUrl+'&project_id='+encodeURIComponent(id);});
 $(document).on('click','.tsa-project-delete',function(){var id=$(this).data('id');var p=items.find(function(x){return String(x.id||x.project_id)===String(id);});var name=p&&p.name?p.name:'this project';if(!confirm('Delete “'+name+'”? This permanently removes the project from Tersuite.'))return;var $b=$(this).prop('disabled',true).text('Deleting…');TSAAPI.deleteProject(id).done(function(){TSA.toast('Project deleted.','success');load();}).fail(function(x){TSA.toast(TSA.error(x,'Unable to delete project.'),'error');}).always(function(){$b.prop('disabled',false).text('Delete Project');});});
 $(document).on('click',function(e){if(!$(e.target).closest('.tsa-project-menu-wrap').length){$('.tsa-project-menu-panel').prop('hidden',true);$('.tsa-project-menu').attr('aria-expanded','false');}});
 $(document).on('click','.tsa-retry-projects',load);load();
});