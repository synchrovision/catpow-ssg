<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Catpow SSG</title>
<link rel="stylesheet" href="css/style.css">
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.3.0/alpine.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
<script>
	function app(){
		var con=axios.create({
			baseURL:'<?=API_URL?>'
		});
		return {
			baseURL:'<?=BASE_URL?>',
			currentPage:'',
			keyword:'',
			pages:[],
			init(){
				const {pc,sp}=this.$refs;

				//scroll sync
				const coefMap=new Map();
				const updateCoef=(iframe)=>{
					if(!pc.contentWindow.document){return;}
					const pcd=pc.contentWindow.document.documentElement;
					const pcsy=Math.max(1,pcd.scrollHeight-pcd.clientHeight);
					[sp].forEach((f)=>{
						if(!f.contentWindow.document){coefMap.set(f,1);return;}
						const d=f.contentWindow.document.documentElement;
						const sy=Math.max(1,d.scrollHeight-d.clientHeight);
						coefMap.set(f,sy/pcsy);
					});
				}
				const syncScroll=()=>{
					[sp].forEach((f)=>{
						f.contentWindow.scroll({
							top:coefMap.get(f)*pc.contentWindow.scrollY,
							behavior:'instant'
						});
					});
				}
				const timer=setInterval(updateCoef,100);

				//resize
				const resizeOberver=new ResizeObserver((entries)=>{
					for(const entry of entries){
						updateScaleProperties(entry.target);
					}
				});
				const setSizeProperties=(el,size)=>{
					el.style.setProperty('--w',size[0]);
					el.style.setProperty('--h',size[1]);
				}
				const updateScaleProperties=(el)=>{
					el.style.setProperty('--s',el.clientWidth/el.style.getPropertyValue('--w'));
				}
				setSizeProperties(pc.parentElement,[1920,1080]);
				setSizeProperties(sp.parentElement,[420,720]);
				resizeOberver.observe(pc.parentElement);
				resizeOberver.observe(sp.parentElement);

				//auto reload
				const currentPageDeps={html:false,js:[],css:[]};
				const observeUpdate=async()=>{
					if(currentPageDeps.html){
						const options={
							method:'POST',
							headers:{"Content-Type":'application/json'},
							body:JSON.stringify(currentPageDeps)
						};
						console.log('start to observe : '+currentPageDeps.html);
						const result=await fetch('http://localhost:8001/',options).then(res=>res.json());
						if(result.updated){
							console.log('change detected');
							this.reload();
						}
						else{
							console.log('no change detected');
						}
					}
					else{
						await new Promise((resolve)=>setTimeout(resolve,5000));
					}
					observeUpdate();
				}
				pc.addEventListener('load',()=>{
					updateCoef();
					pc.contentWindow.addEventListener('scroll',syncScroll);
					currentPageDeps.js=[];
					currentPageDeps.css=[];
					if(pc.src.startsWith('<?=BASE_URL?>')){
						currentPageDeps.html=(new URL(pc.src)).pathname;
						for(const script of pc.contentDocument.scripts){
							if(script.src && script.src.startsWith('<?=BASE_URL?>')){
								currentPageDeps.js.push((new URL(script.src)).pathname);
							}
						}
						for(const styleSheet of pc.contentDocument.styleSheets){
							if(styleSheet.href && styleSheet.href.startsWith('<?=BASE_URL?>')){
								currentPageDeps.css.push((new URL(styleSheet.href)).pathname);
							}
						}
					}
					else{
						currentPageDeps.html=false;
					}
				});
				observeUpdate();
				
				this.updateIndex();
			},
			updateIndex(){
				con.get('index').then((res)=>{
					this.currentPage=res.data[0];
					this.pages=res.data;
				});
			},
			reload(){
				this.$refs.pc.contentWindow.location.reload();
				this.$refs.sp.contentWindow.location.reload();
				console.log('reload page');
			}
		};
	}
</script>
</head>

<body>
	<div class="cp-layout">
		<div class="cp-header">
			<h2 class="cp-header__title">Catpow SSG</h2>
		</div>
		<div class="cp-main" x-data="app()" x-init="init()">
			<div class="cp-main__sidebar">
				<div class="cp-search">
					<input type="text" class="cp-search__input" x-model="keyword"/>
				</div>
				<ul class="cp-index">
					<template x-for="page in pages">
						<li class="cp-index-item" :class="{'is-active':page==currentPage,'is-visible':!keyword || page.includes(keyword)}">
							<span class="cp-index-item__label" x-text="page" @click="currentPage=page"></span>
							<a class="cp-index-item__icon" :href="page" target="_blank">open_in_new</a>
						</li>
					</template>
				</ul>
			</div>
			<div class="cp-main__contents">
				<div class="cp-previews">
					<?php foreach(['pc','sp'] as $d): ?>
					<div class="cp-preview is-media-<?=$d?>">
						<iframe class="cp-preview__contents" :src="currentPage" frameborder="0" x-ref="<?=$d?>"></iframe>
						<div class="cp-preview-control">
							<div class="cp-preview-control__icon" @click="$refs.<?=$d?>.contentWindow.location.reload()">replay</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="cp-footer">
			<p class="cp-footer__text">Catpow-SSG</o>
		</div>
	</div>
</body>
</html>
