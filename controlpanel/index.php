<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Catpow SSG</title>
<link rel="stylesheet" href="css/style.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
			mode:'preview',
			comparePreviewScale:50,
			compIsImage:true,
			compUrl:'',
			init(){
				const {pc,sp,comparePreview}=this.$refs;

				//scroll sync
				const coefMap=new Map();
				const updateCoef=(iframe)=>{
					if(!pc.contentWindow.document || !pc.contentWindow.document.documentElement){return;}
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
				const resizeCompareObserver=new ResizeObserver((entries)=>{
					for(const entry of entries){
						updateSizeProperties(entry.target);
					}
				});
				const setSizeProperties=(el,size)=>{
					el.style.setProperty('--w',size[0]);
					el.style.setProperty('--h',size[1]);
				}
				const updateScaleProperties=(el)=>{
					el.style.setProperty('--s',el.clientWidth/el.style.getPropertyValue('--w'));
				}
				const updateSizeProperties=(el)=>{
					const es=el.style.getPropertyValue('--s');
					el.style.setProperty('--w',el.clientWidth/s);
					el.style.setProperty('--h',el.clientHeight/s);
				};
				setSizeProperties(pc.parentElement,[1920,1080]);
				setSizeProperties(sp.parentElement,[420,720]);
				resizeOberver.observe(pc.parentElement);
				resizeOberver.observe(sp.parentElement);

				//auto reload
				const es=new EventSource('<?=SSE_URL?>');
				let lastLoad=0,isLoading=false;
				pc.addEventListener('load',()=>{lastLoad=Math.floor(Date.now() / 1000);isLoading=false;});
				es.addEventListener('update',(e)=>{
					if(isLoading){return;}
					const updatedFiles=JSON.parse(e.data);
					if(Object.values(updatedFiles).some(mtime=>mtime>lastLoad)){
						this.reload();
						isLoading=true;
					}
				});
				
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
				this.$refs.comparePreview.contentWindow.location.reload();
				console.log('reload page');
			},
			toggleMode(){
				this.mode=this.mode==='preview'?'compare':'preview';
			},
			dropComp(e){
				e.preventDefault();
				const file=e.dataTransfer.files[0];
				const reader=new FileReader;
				reader.addEventListener('load',(e)=>{
					this.compIsImage=file.type.slice(0,6)==='image/';
					this.compUrl=e.target.result;
				});
				reader.readAsDataURL(file);
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
				<div class="cp-controls">
					<div class="cp-controls-mode" :class="'is-mode-' + mode" @click="toggleMode">
						<div class="cp-controls-mode__label is-label-preview">Preview</div>
						<div class="cp-controls-mode__handle"></div>
						<div class="cp-controls-mode__label is-label-compare">Compare</div>
					</div>
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
				<div class="cp-previews" x-show="mode=='preview'">
					<?php foreach(['pc','sp'] as $d): ?>
					<div class="cp-preview is-media-<?=$d?>">
						<iframe class="cp-preview__contents" :src="currentPage" frameborder="0" x-ref="<?=$d?>"></iframe>
						<div class="cp-preview-control">
							<div class="cp-preview-control__icon" @click="$refs.<?=$d?>.contentWindow.location.reload()">replay</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="cp-compare" x-show="mode=='compare'">
					<div class="cp-compare-preview" :style="{'--s':comparePreviewScale}">
						<iframe class="cp-compare-preview__contents" :src="currentPage" frameborder="0" x-ref="comparePreview"></iframe>
					</div>
					<div class="cp-compare-comp" @dragover="(e)=>e.preventDefault()" @drop="dropComp">
						<template x-if="compIsImage">
							<img class="cp-compare-comp__contents is-image" :src="compUrl"/>
						</template>
						<template x-if="!compIsImage">
							<iframe class="cp-compare-comp__contents is-iframe" :src="compUrl"/>
						</template>
					</div>
					<div class="cp-compare-controls">
						<div class="cp-compare-controls-scale">
							<input class="cp-compare-controls-scale__range" type="range" x-model="comparePreviewScale" min="20" max="150"/>
							<input class="cp-compare-controls-scale__text" type="number" x-model="comparePreviewScale" min="20" max="150"/>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="cp-footer">
			<p class="cp-footer__text">Catpow-SSG</o>
		</div>
	</div>
</body>
</html>
